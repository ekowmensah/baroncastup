<?php

namespace SmartCast\Controllers;

use SmartCast\Models\Event;
use SmartCast\Models\Contestant;
use SmartCast\Models\Category;
use SmartCast\Models\Vote;
use SmartCast\Models\Transaction;
use SmartCast\Models\TenantBalance;
use SmartCast\Models\User;
use SmartCast\Models\Tenant;
use SmartCast\Models\VoteBundle;
use SmartCast\Models\LeaderboardCache;
use SmartCast\Core\Database;

/**
 * Organizer Dashboard Controller
 */
class OrganizerController extends BaseController
{
    private $eventModel;
    private $contestantModel;
    private $categoryModel;
    private $voteModel;
    private $transactionModel;
    private $balanceModel;
    private $userModel;
    private $tenantModel;
    private $bundleModel;
    private $leaderboardModel;
    private $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->requireRole(['owner', 'manager']);
        
        $this->db = Database::getInstance();
        $this->eventModel = new Event();
        $this->contestantModel = new Contestant();
        $this->categoryModel = new Category();
        $this->voteModel = new Vote();
        $this->transactionModel = new Transaction();
        $this->balanceModel = new TenantBalance();
        $this->userModel = new User();
        $this->tenantModel = new Tenant();
        $this->bundleModel = new VoteBundle();
        $this->leaderboardModel = new LeaderboardCache();
    }
    
    public function dashboard()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get dashboard statistics
        $stats = $this->getDashboardStats($tenantId);
        
        // Get recent events
        $recentEvents = $this->eventModel->getEventsByTenant($tenantId);
        $recentEvents = array_slice($recentEvents, 0, 5);
        
        // Get recent votes
        $recentVotes = $this->getRecentVotes($tenantId, 10);
        
        // Recalculate balance from revenue_transactions to ensure accuracy
        $this->balanceModel->recalculateBalance($tenantId);
        
        // Get financial overview
        $balance = $this->balanceModel->getBalance($tenantId);
        
        // Get subscription plan information
        $subscriptionModel = new \SmartCast\Models\TenantSubscription();
        $currentSubscription = $subscriptionModel->getActiveSubscription($tenantId);
        
        // Get available plans if no subscription
        $availablePlans = null;
        if (!$currentSubscription) {
            $planModel = new \SmartCast\Models\SubscriptionPlan();
            $availablePlans = $planModel->getPlansForPricing();
        }
        
        // Get tenant USSD information
        $tenantModel = new \SmartCast\Models\Tenant();
        $tenant = $tenantModel->find($tenantId);
        
        $content = $this->renderView('organizer/dashboard', [
            'stats' => $stats,
            'recentEvents' => $recentEvents,
            'recentVotes' => $recentVotes,
            'balance' => $balance,
            'currentSubscription' => $currentSubscription,
            'availablePlans' => $availablePlans,
            'tenant' => $tenant,
            'title' => 'Organizer Dashboard'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Dashboard'
        ]);
    }
    
    /**
     * Subscribe to a plan (for existing tenants without subscription)
     */
    public function subscribeToPlan()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $tenantId = $this->session->getTenantId();
                $data = $this->sanitizeInput($_POST);
                
                // Validate plan selection
                if (empty($data['plan_id'])) {
                    $this->redirect(ORGANIZER_URL, 'Please select a plan', 'error');
                    return;
                }
                
                // Check if tenant already has an active subscription
                $subscriptionModel = new \SmartCast\Models\TenantSubscription();
                $existingSubscription = $subscriptionModel->getActiveSubscription($tenantId);
                
                if ($existingSubscription) {
                    $this->redirect(ORGANIZER_URL, 'You already have an active subscription', 'info');
                    return;
                }
                
                // Validate selected plan
                $planModel = new \SmartCast\Models\SubscriptionPlan();
                $selectedPlan = $planModel->find($data['plan_id']);
                
                if (!$selectedPlan || !$selectedPlan['is_active']) {
                    $this->redirect(ORGANIZER_URL, 'Invalid plan selected', 'error');
                    return;
                }
                
                // Create subscription
                $subscriptionModel->createSubscription($tenantId, $selectedPlan['id'], $selectedPlan['billing_cycle']);
                
                // Update tenant record
                $this->tenantModel->update($tenantId, [
                    'current_plan_id' => $selectedPlan['id'],
                    'subscription_status' => 'trial',
                    'trial_ends_at' => $selectedPlan['trial_days'] > 0 ? 
                        date('Y-m-d H:i:s', strtotime('+' . $selectedPlan['trial_days'] . ' days')) : null
                ]);
                
                $message = 'Successfully subscribed to ' . $selectedPlan['name'] . ' plan!';
                if ($selectedPlan['trial_days'] > 0) {
                    $message .= ' You have ' . $selectedPlan['trial_days'] . ' days free trial.';
                }
                
                $this->redirect(ORGANIZER_URL, $message, 'success');
                
            } catch (\Exception $e) {
                $this->redirect(ORGANIZER_URL, 'Failed to subscribe to plan: ' . $e->getMessage(), 'error');
            }
        } else {
            // Show plan selection page
            $tenantId = $this->session->getTenantId();
            
            // Check if tenant already has subscription
            $subscriptionModel = new \SmartCast\Models\TenantSubscription();
            $existingSubscription = $subscriptionModel->getActiveSubscription($tenantId);
            
            if ($existingSubscription) {
                $this->redirect(ORGANIZER_URL, 'You already have an active subscription', 'info');
                return;
            }
            
            // Get available plans
            $planModel = new \SmartCast\Models\SubscriptionPlan();
            $plans = $planModel->getPlansForPricing();
            
            $content = $this->renderView('organizer/subscription/select-plan', [
                'plans' => $plans,
                'title' => 'Choose Your Plan'
            ]);
            
            echo $this->renderLayout('organizer_layout', $content, [
                'title' => 'Choose Your Plan',
                'breadcrumbs' => [
                    ['title' => 'Subscription', 'url' => '#'],
                    ['title' => 'Choose Plan']
                ]
            ]);
        }
    }
    
    /**
     * Show plan switching page
     */
    public function switchPlan()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $tenantId = $this->session->getTenantId();
                $data = $this->sanitizeInput($_POST);
                
                // Validate plan selection
                if (empty($data['plan_id'])) {
                    $this->redirect(ORGANIZER_URL . '/switch-plan', 'Please select a plan', 'error');
                    return;
                }
                
                // Get current subscription
                $subscriptionModel = new \SmartCast\Models\TenantSubscription();
                $currentSubscription = $subscriptionModel->getActiveSubscription($tenantId);
                
                // Validate selected plan
                $planModel = new \SmartCast\Models\SubscriptionPlan();
                $selectedPlan = $planModel->find($data['plan_id']);
                
                if (!$selectedPlan || !$selectedPlan['is_active']) {
                    $this->redirect(ORGANIZER_URL . '/switch-plan', 'Invalid plan selected', 'error');
                    return;
                }
                
                // Check if it's the same plan
                if ($currentSubscription && $currentSubscription['plan_id'] == $selectedPlan['id']) {
                    $this->redirect(ORGANIZER_URL, 'You are already subscribed to this plan', 'info');
                    return;
                }
                
                // Switch to new plan
                $subscriptionModel->changeSubscription($tenantId, $selectedPlan['id'], 'Plan switch by tenant');
                
                $message = 'Successfully switched to ' . $selectedPlan['name'] . ' plan!';
                if ($selectedPlan['trial_days'] > 0 && !$currentSubscription) {
                    $message .= ' You have ' . $selectedPlan['trial_days'] . ' days free trial.';
                }
                
                $this->redirect(ORGANIZER_URL, $message, 'success');
                
            } catch (\Exception $e) {
                $this->redirect(ORGANIZER_URL . '/switch-plan', 'Failed to switch plan: ' . $e->getMessage(), 'error');
            }
        } else {
            // Show plan switching page
            $tenantId = $this->session->getTenantId();
            
            // Get current subscription
            $subscriptionModel = new \SmartCast\Models\TenantSubscription();
            $currentSubscription = $subscriptionModel->getActiveSubscription($tenantId);
            
            // Get available plans
            $planModel = new \SmartCast\Models\SubscriptionPlan();
            $plans = $planModel->getPlansForPricing();
            
            $content = $this->renderView('organizer/subscription/switch-plan', [
                'plans' => $plans,
                'currentSubscription' => $currentSubscription,
                'title' => 'Switch Plan'
            ]);
            
            echo $this->renderLayout('organizer_layout', $content, [
                'title' => 'Switch Plan',
                'breadcrumbs' => [
                    ['title' => 'Subscription', 'url' => '#'],
                    ['title' => 'Switch Plan']
                ]
            ]);
        }
    }
    
    public function events()
    {
        $tenantId = $this->session->getTenantId();
        $events = $this->eventModel->getEventsByTenant($tenantId);
        
        // Get subscription limits
        $subscriptionModel = new \SmartCast\Models\TenantSubscription();
        $tenantLimits = $subscriptionModel->getTenantLimits($tenantId) ?? $this->getDefaultTenantLimits();
        
        $content = $this->renderView('organizer/events/index', [
            'events' => $events,
            'tenantLimits' => $tenantLimits,
            'title' => 'My Events'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Events',
            'breadcrumbs' => [
                ['title' => 'Events']
            ]
        ]);
    }
    
    public function createEvent()
    {
        // Redirect to the wizard since it's now the primary creation method
        $this->redirect(ORGANIZER_URL . '/events/wizard', '', '');
    }

    
    /**
     * Primary event creation method using the wizard interface
     * Supports categories, nominees, and global shortcode generation
     */
    public function createEventWizard()
    {
        $editEventId = $_GET['edit'] ?? null;
        $eventData = null;
        $categories = [];
        $nominees = [];
        $isEditing = false;
        
        $tenantId = $this->session->getTenantId();
        
        // Get subscription limits for display
        $subscriptionModel = new \SmartCast\Models\TenantSubscription();
        $tenantLimits = $subscriptionModel->getTenantLimits($tenantId) ?? $this->getDefaultTenantLimits();
        
        // If editing an existing event, load its data
        if ($editEventId) {
            $eventData = $this->eventModel->find($editEventId);
            
            if (!$eventData || $eventData['tenant_id'] != $this->session->getTenantId()) {
                $this->redirect(ORGANIZER_URL . '/events', 'Event not found', 'error');
                return;
            }
            
            $isEditing = true;
            
            // Load categories for this event
            $categoryModel = new \SmartCast\Models\Category();
            $categories = $categoryModel->findAll(['event_id' => $editEventId]);
            
            // Load nominees (contestants) for this event
            $contestantModel = new \SmartCast\Models\Contestant();
            $contestants = $contestantModel->findAll(['event_id' => $editEventId]);
            
            // Load nominee-category assignments with shortcodes (per category)
            $contestantCategoryModel = new \SmartCast\Models\ContestantCategory();
            foreach ($contestants as &$contestant) {
                $assignments = $contestantCategoryModel->findAll(['contestant_id' => $contestant['id']]);
                $contestant['categories'] = array_column($assignments, 'category_id');
                
                // Build shortcodes object with category_id as key
                $contestant['shortcodes'] = [];
                // Build category photos object with category_id as key
                $contestant['category_photos'] = [];
                
                foreach ($assignments as $assignment) {
                    if (!empty($assignment['short_code'])) {
                        $contestant['shortcodes'][$assignment['category_id']] = $assignment['short_code'];
                    }
                    // Add category-specific photo if exists
                    if (!empty($assignment['category_image_url']) && $assignment['use_category_photo']) {
                        $contestant['category_photos'][$assignment['category_id']] = $assignment['category_image_url'];
                    }
                }
            }
            $nominees = $contestants;
            
            // Debug: Log loaded data
            error_log("Loaded categories: " . count($categories));
            error_log("Loaded nominees: " . count($nominees));
            if (!empty($nominees)) {
                error_log("First nominee: " . json_encode($nominees[0]));
            }
        }
        
        $title = $isEditing ? 'Edit Event - ' . ($eventData['name'] ?? 'Unknown') : 'Create New Event';
        
        $content = $this->renderView('organizer/events/create-wizard', [
            'title' => $title,
            'eventData' => $eventData,
            'categories' => $categories,
            'nominees' => $nominees,
            'isEditing' => $isEditing,
            'editEventId' => $editEventId,
            'tenantLimits' => $tenantLimits,
            'currentEventStatus' => $eventData['status'] ?? null
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => $isEditing ? 'Edit Event' : 'Create Event',
            'breadcrumbs' => [
                ['title' => 'Events', 'url' => ORGANIZER_URL . '/events'],
                ['title' => $isEditing ? 'Edit Event' : 'Create Event']
            ]
        ]);
    }
    
    
    public function editEvent($id)
    {
// ... }}
        $tenantId = $this->session->getTenantId();
        
        // Get the event and verify ownership
        $event = $this->eventModel->find($id);
        if (!$event || $event['tenant_id'] != $tenantId) {
            $this->redirect(ORGANIZER_URL . '/events', 'Event not found', 'error');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->sanitizeInput($_POST);
            
            try {
                $this->eventModel->update($id, $data);
                $this->redirect(ORGANIZER_URL . '/events/' . $id, 'Event updated successfully', 'success');
            } catch (\Exception $e) {
                $content = $this->renderView('organizer/events/edit', [
                    'event' => $event,
                    'error' => 'Failed to update event: ' . $e->getMessage(),
                    'title' => 'Edit Event'
                ]);
                
                echo $this->renderLayout('organizer_layout', $content, [
                    'title' => 'Edit Event'
                ]);
                return;
            }
        }
        
        $content = $this->renderView('organizer/events/edit', [
            'event' => $event,
            'title' => 'Edit Event'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Edit Event',
            'breadcrumbs' => [
                ['title' => 'Events', 'url' => ORGANIZER_URL . '/events'],
                ['title' => 'Edit Event']
            ]
        ]);
    }
    
    public function contestants()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get events with their contestants grouped by categories
        $sql = "
            SELECT 
                e.id as event_id,
                e.name as event_name,
                e.code as event_code,
                e.status as event_status,
                COUNT(DISTINCT c.id) as contestant_count,
                COALESCE(SUM(v.quantity), 0) as total_votes,
                COALESCE(SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END), 0) as revenue
            FROM events e
            LEFT JOIN contestants c ON e.id = c.event_id AND c.active = 1
            LEFT JOIN votes v ON c.id = v.contestant_id
            LEFT JOIN transactions t ON v.transaction_id = t.id
            WHERE e.tenant_id = :tenant_id
            GROUP BY e.id, e.name, e.code, e.status
            ORDER BY e.created_at DESC
        ";
        
        try {
            $events = $this->eventModel->getDatabase()->select($sql, ['tenant_id' => $tenantId]);
        } catch (\Exception $e) {
            $events = [];
        }
                // Get categories and contestants for each event
        $eventData = [];
        foreach ($events as $event) {
            // Get categories for this event
            $categorySql = "
                SELECT 
                    cat.id as category_id,
                    cat.name as category_name,
                    cat.description as category_description,
                    COUNT(DISTINCT cc.contestant_id) as contestant_count
                FROM categories cat
                LEFT JOIN contestant_categories cc ON cat.id = cc.category_id
                LEFT JOIN contestants c ON cc.contestant_id = c.id AND c.active = 1
                WHERE cat.event_id = :event_id
                GROUP BY cat.id, cat.name, cat.description
                ORDER BY cat.display_order ASC, cat.name ASC
            ";
            
            try {
                $categories = $this->eventModel->getDatabase()->select($categorySql, ['event_id' => $event['event_id']]);
            } catch (\Exception $e) {
                $categories = [];
            }
            
            // Get contestants for each category
            foreach ($categories as &$category) {
                $contestantSql = "
                    SELECT 
                        c.id,
                        c.name,
                        c.contestant_code,
                        c.image_url,
                        c.bio,
                        cc.short_code as voting_shortcode,
                        COALESCE(vote_stats.total_votes, 0) as total_votes,
                        COALESCE(vote_stats.revenue, 0) as revenue
                    FROM contestants c
                    INNER JOIN contestant_categories cc ON c.id = cc.contestant_id
                    LEFT JOIN (
                        SELECT 
                            v.contestant_id,
                            SUM(v.quantity) as total_votes,
                            SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END) as revenue
                        FROM votes v
                        LEFT JOIN transactions t ON v.transaction_id = t.id
                        WHERE (v.category_id = :category_id_for_votes OR v.category_id IS NULL)
                        GROUP BY v.contestant_id
                    ) vote_stats ON c.id = vote_stats.contestant_id
                    WHERE cc.category_id = :category_id
                    AND c.active = 1
                    ORDER BY cc.display_order ASC, c.name ASC
                ";
                
                try {
                    $category['contestants'] = $this->eventModel->getDatabase()->select($contestantSql, [
                        'category_id' => $category['category_id'],
                        'category_id_for_votes' => $category['category_id']
                    ]);
                    
                } catch (\Exception $e) {
                    $category['contestants'] = [];
                }
            }
            
            $eventData[] = [
                'event' => $event,
                'categories' => $categories
            ];
        }
        
        $this->view('organizer/contestants/index', [
            'eventData' => $eventData,
            'title' => 'Contestants',
            'breadcrumbs' => [
                ['title' => 'Contestants']
            ]
        ]);
    }
    
    public function revenueDashboard()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get balance information
        $balance = $this->balanceModel->getBalance($tenantId);
        
        // Get today's earnings
        $todayEarnings = $this->getTodayEarnings($tenantId);
        
        // Get top earning events
        $topEvents = $this->getTopEarningEvents($tenantId);
        
        // Get recent revenue transactions
        $recentTransactions = $this->getRecentRevenueTransactions($tenantId);
        
        // Get revenue chart data (last 30 days)
        $chartData = $this->getRevenueChartData($tenantId);
        
        // Get payout method (if configured)
        $payoutMethod = $this->getPayoutMethod($tenantId);
        
        $content = $this->renderView('organizer/financial/revenue-dashboard', [
            'balance' => $balance,
            'todayEarnings' => $todayEarnings,
            'topEvents' => $topEvents,
            'recentTransactions' => $recentTransactions,
            'chartLabels' => $chartData['labels'],
            'chartData' => $chartData['data'],
            'payoutMethod' => $payoutMethod,
            'nextAutoPayout' => 'Monthly on 1st',
            'title' => 'Revenue Dashboard'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Revenue Dashboard',
            'breadcrumbs' => [
                ['title' => 'Financial', 'url' => ORGANIZER_URL . '/financial'],
                ['title' => 'Revenue Dashboard']
            ]
        ]);
    }

    public function financialOverview()
    {
        $tenantId = $this->session->getTenantId();
        
        // Recalculate balance from revenue_transactions to ensure accuracy
        $this->balanceModel->recalculateBalance($tenantId);
        
        // Get balance information
        $balance = $this->balanceModel->getBalance($tenantId);
        
        // Get recent transactions with event and contestant details
        $recentTransactions = $this->getRecentTransactionsWithDetails($tenantId, 10);
        
        // Calculate revenue stats
        $revenueStats = $this->getRevenueStats($tenantId);
        
        // Get revenue trend data for chart (last 6 months)
        $revenueTrends = $this->getRevenueTrends($tenantId, 6);
        
        $content = $this->renderView('organizer/financial/overview', [
            'balance' => $balance,
            'recentTransactions' => $recentTransactions,
            'revenueStats' => $revenueStats,
            'revenueTrends' => $revenueTrends,
            'title' => 'Financial Overview'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Financial Overview',
            'breadcrumbs' => [
                ['title' => 'Financial', 'url' => ORGANIZER_URL . '/financial'],
                ['title' => 'Overview']
            ]
        ]);
    }
    
    public function liveResults()
    {
        $tenantId = $this->session->getTenantId();
        $eventId = $_GET['event'] ?? null;
        $categoryId = $_GET['category'] ?? null;
        
        // Get all events for dropdown
        $events = $this->eventModel->getEventsByTenant($tenantId);
        $activeEvents = array_filter($events, function($event) {
            return $event['status'] === 'active';
        });
        
        // Get categories for the selected event
        $categories = [];
        if ($eventId) {
            $categories = $this->getCategoriesByEvent($eventId);
        }
        
        // If no event specified, use the first active event
        if (!$eventId && !empty($activeEvents)) {
            $eventId = reset($activeEvents)['id'];
            // Reload categories for the selected event
            if ($eventId) {
                $categories = $this->getCategoriesByEvent($eventId);
            }
        }
        
        $selectedEvent = null;
        if ($eventId) {
            $selectedEvent = $this->eventModel->find($eventId);
            if (!$selectedEvent || $selectedEvent['tenant_id'] != $tenantId) {
                $selectedEvent = null;
                $eventId = null;
                $categoryId = null;
                $categories = [];
            }
        }
        
        // Get event-specific data
        $liveStats = $this->getLiveVotingStats($tenantId, $eventId, $categoryId);
        $topContestants = $this->getTopContestants($tenantId, 15, $eventId, $categoryId);
        $recentVotes = $this->getRecentVotes($tenantId, $eventId, $categoryId, 10);
        $votingTrends = $this->getVotingTrends($tenantId, $eventId, $categoryId, 24); // Last 24 hours
        
        // Handle AJAX requests
        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'liveStats' => $liveStats,
                'topContestants' => $topContestants,
                'recentVotes' => $recentVotes,
                'votingTrends' => $votingTrends,
                'lastUpdated' => date('H:i:s')
            ]);
            return;
        }
        
        $content = $this->renderView('organizer/voting/live', [
            'events' => $activeEvents,
            'categories' => $categories,
            'selectedEvent' => $selectedEvent,
            'selectedEventId' => $eventId,
            'selectedCategoryId' => $categoryId,
            'liveStats' => $liveStats,
            'topContestants' => $topContestants,
            'recentVotes' => $recentVotes,
            'votingTrends' => $votingTrends,
            'title' => 'Live Results'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Live Results',
            'breadcrumbs' => [
                ['title' => 'Voting', 'url' => ORGANIZER_URL . '/voting'],
                ['title' => 'Live Results']
            ]
        ]);
    }
    
    private function getDashboardStats($tenantId)
    {
        $stats = [];
        
        // Total events
        $stats['total_events'] = $this->eventModel->count(['tenant_id' => $tenantId]);
        
        // Active events
        $stats['active_events'] = $this->eventModel->count([
            'tenant_id' => $tenantId,
            'status' => 'active'
        ]);
        
        // Total contestants
        $stats['total_contestants'] = $this->contestantModel->count([
            'tenant_id' => $tenantId,
            'active' => 1
        ]);
        
        // Total votes
        $sql = "
            SELECT COALESCE(SUM(v.quantity), 0) as total
            FROM votes v
            INNER JOIN events e ON v.event_id = e.id
            WHERE e.tenant_id = :tenant_id
        ";
        $result = $this->eventModel->getDatabase()->selectOne($sql, ['tenant_id' => $tenantId]);
        $stats['total_votes'] = $result['total'] ?? 0;
        
        // Revenue this month
        $startOfMonth = date('Y-m-01 00:00:00');
        $endOfMonth = date('Y-m-t 23:59:59');
        
        $sql = "
            SELECT COALESCE(SUM(t.amount), 0) as revenue
            FROM transactions t
            INNER JOIN events e ON t.event_id = e.id
            WHERE e.tenant_id = :tenant_id
            AND t.status = 'success'
            AND t.created_at BETWEEN :start_date AND :end_date
        ";
        
        $result = $this->eventModel->getDatabase()->selectOne($sql, [
            'tenant_id' => $tenantId,
            'start_date' => $startOfMonth,
            'end_date' => $endOfMonth
        ]);
        $stats['monthly_revenue'] = $result['revenue'] ?? 0;
        
        return $stats;
    }
    
    private function getCategoriesByEvent($eventId)
    {
        try {
            $sql = "
                SELECT 
                    cat.id,
                    cat.name,
                    cat.description,
                    COUNT(DISTINCT cc.contestant_id) as contestant_count
                FROM categories cat
                LEFT JOIN contestant_categories cc ON cat.id = cc.category_id
                LEFT JOIN contestants c ON cc.contestant_id = c.id AND c.active = 1
                WHERE cat.event_id = :event_id
                GROUP BY cat.id, cat.name, cat.description
                ORDER BY cat.name ASC
            ";
            
            return $this->eventModel->getDatabase()->select($sql, ['event_id' => $eventId]);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    private function getRecentVotes($tenantId, $eventId = null, $categoryId = null, $limit = 10)
    {
        $eventFilter = $eventId ? "AND e.id = :event_id" : "";
        $categoryFilter = $categoryId ? "AND v.category_id = :category_id" : "";
        $params = ['tenant_id' => $tenantId, 'limit' => $limit];
        if ($eventId) {
            $params['event_id'] = $eventId;
        }
        if ($categoryId) {
            $params['category_id'] = $categoryId;
        }
        
        $sql = "
            SELECT 
                c.name as contestant_name,
                e.name as event_name,
                v.quantity,
                t.amount,
                v.created_at,
                TIMESTAMPDIFF(SECOND, v.created_at, NOW()) as seconds_ago
            FROM votes v
            INNER JOIN contestants c ON v.contestant_id = c.id
            INNER JOIN events e ON c.event_id = e.id
            INNER JOIN transactions t ON v.transaction_id = t.id
            WHERE e.tenant_id = :tenant_id
            AND t.status = 'success'
            $eventFilter
            $categoryFilter
            ORDER BY v.created_at DESC
            LIMIT :limit
        ";
        
        return $this->eventModel->getDatabase()->select($sql, $params);
    }
    
    private function getRecentTransactionsWithDetails($tenantId, $limit = 10)
    {
        $sql = "
            SELECT 
                t.id as transaction_id,
                t.amount,
                t.status,
                t.created_at,
                e.name as event_name,
                c.name as contestant_name,
                v.quantity
            FROM transactions t
            INNER JOIN events e ON t.event_id = e.id
            LEFT JOIN contestants c ON t.contestant_id = c.id
            LEFT JOIN votes v ON t.id = v.transaction_id
            WHERE e.tenant_id = :tenant_id
            AND t.status = 'success'
            ORDER BY t.created_at DESC
            LIMIT :limit
        ";
        
        return $this->eventModel->getDatabase()->select($sql, [
            'tenant_id' => $tenantId,
            'limit' => $limit
        ]);
    }
    
    private function getRevenueTrends($tenantId, $months = 6)
    {
        // Get revenue data for the last N months
        $sql = "
            SELECT 
                DATE_FORMAT(rt.created_at, '%Y-%m') as month,
                DATE_FORMAT(rt.created_at, '%b') as month_label,
                COALESCE(SUM(rt.net_tenant_amount), 0) as revenue
            FROM revenue_transactions rt
            WHERE rt.tenant_id = :tenant_id
            AND rt.distribution_status = 'completed'
            AND rt.created_at >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            GROUP BY DATE_FORMAT(rt.created_at, '%Y-%m'), DATE_FORMAT(rt.created_at, '%b')
            ORDER BY month ASC
        ";
        
        $results = $this->balanceModel->getDatabase()->select($sql, [
            'tenant_id' => $tenantId,
            'months' => $months
        ]);
        
        // Fill in missing months with zero revenue
        $trends = [];
        $labels = [];
        $data = [];
        
        // Create array of last N months
        for ($i = $months - 1; $i >= 0; $i--) {
            $monthKey = date('Y-m', strtotime("-$i months"));
            $monthLabel = date('M', strtotime("-$i months"));
            $trends[$monthKey] = 0;
            $labels[] = $monthLabel;
        }
        
        // Fill in actual revenue data
        foreach ($results as $row) {
            if (isset($trends[$row['month']])) {
                $trends[$row['month']] = floatval($row['revenue']);
            }
        }
        
        // Convert to indexed array for chart
        $data = array_values($trends);
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    private function getRevenueStats($tenantId)
    {
        // This week vs last week
        $thisWeekStart = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $lastWeekStart = date('Y-m-d 00:00:00', strtotime('monday last week'));
        $lastWeekEnd = date('Y-m-d 23:59:59', strtotime('sunday last week'));
        
        $sql = "
            SELECT 
                COALESCE(SUM(CASE WHEN rt.created_at >= :this_week_start THEN rt.net_tenant_amount END), 0) as this_week,
                COALESCE(SUM(CASE WHEN rt.created_at BETWEEN :last_week_start AND :last_week_end THEN rt.net_tenant_amount END), 0) as last_week
            FROM revenue_transactions rt
            WHERE rt.tenant_id = :tenant_id
            AND rt.distribution_status = 'completed'
        ";
        
        $result = $this->balanceModel->getDatabase()->selectOne($sql, [
            'tenant_id' => $tenantId,
            'this_week_start' => $thisWeekStart,
            'last_week_start' => $lastWeekStart,
            'last_week_end' => $lastWeekEnd
        ]);
        
        $thisWeek = $result['this_week'] ?? 0;
        $lastWeek = $result['last_week'] ?? 0;
        
        $growth = 0;
        if ($lastWeek > 0) {
            $growth = (($thisWeek - $lastWeek) / $lastWeek) * 100;
        }
        
        return [
            'this_week' => $thisWeek,
            'last_week' => $lastWeek,
            'growth_percentage' => $growth
        ];
    }
    
    private function renderView($view, $data = [])
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../../views/' . $view . '.php';
        return ob_get_clean();
    }
    
    private function renderLayout($layout, $content, $data = [])
    {
        $data['content'] = $content;
        extract($data);
        ob_start();
        include __DIR__ . '/../../views/layout/' . $layout . '.php';
        return ob_get_clean();
    }
    
    // Revenue Dashboard Helper Methods
    
    private function getTodayEarnings($tenantId)
    {
        $today = date('Y-m-d');
        $sql = "
            SELECT COALESCE(SUM(rt.net_tenant_amount), 0) as today_earnings
            FROM revenue_transactions rt
            WHERE rt.tenant_id = :tenant_id
            AND DATE(rt.created_at) = :today
            AND rt.distribution_status = 'completed'
        ";
        
        $result = $this->balanceModel->getDatabase()->selectOne($sql, [
            'tenant_id' => $tenantId,
            'today' => $today
        ]);
        
        return $result['today_earnings'] ?? 0;
    }
    
    private function getTopEarningEvents($tenantId, $limit = 5)
    {
        $sql = "
            SELECT 
                e.id,
                e.name,
                COUNT(DISTINCT rt.transaction_id) as transaction_count,
                COALESCE(SUM(rt.net_tenant_amount), 0) as total_revenue,
                COALESCE(SUM(rt.gross_amount), 0) as gross_revenue,
                COALESCE(SUM(rt.platform_fee), 0) as total_fees
            FROM events e
            LEFT JOIN revenue_transactions rt ON e.id = rt.event_id AND rt.distribution_status = 'completed'
            WHERE e.tenant_id = :tenant_id
            GROUP BY e.id, e.name
            HAVING total_revenue > 0
            ORDER BY total_revenue DESC
            LIMIT :limit
        ";
        
        return $this->balanceModel->getDatabase()->select($sql, [
            'tenant_id' => $tenantId,
            'limit' => $limit
        ]);
    }
    
    private function getRecentRevenueTransactions($tenantId, $limit = 10)
    {
        $sql = "
            SELECT 
                t.id,
                t.amount,
                t.created_at,
                e.name as event_name,
                c.name as contestant_name,
                v.quantity as vote_count,
                -- Use revenue_transactions table for accurate fee data
                COALESCE(rt.platform_fee, 0) as platform_fee,
                COALESCE(rt.processing_fee, 0) as processing_fee,
                COALESCE(rt.net_tenant_amount, t.amount) as net_amount,
                -- Calculate actual fee percentage based on platform fee amount
                CASE 
                    WHEN t.amount > 0 AND COALESCE(rt.platform_fee, 0) > 0 THEN 
                        ROUND((COALESCE(rt.platform_fee, 0) / t.amount) * 100, 1)
                    ELSE 0
                END as calculated_fee_percentage,
                -- Use actual platform fee from revenue_transactions table
                COALESCE(rt.platform_fee, 0) as calculated_platform_fee,
                -- Use actual net amount from revenue_transactions
                COALESCE(rt.net_tenant_amount, t.amount) as calculated_net_amount
            FROM transactions t
            INNER JOIN events e ON t.event_id = e.id
            INNER JOIN contestants c ON t.contestant_id = c.id
            LEFT JOIN votes v ON t.id = v.transaction_id
            LEFT JOIN revenue_transactions rt ON t.id = rt.transaction_id AND rt.distribution_status = 'completed'
            WHERE e.tenant_id = :tenant_id
            AND t.status = 'success'
            ORDER BY t.created_at DESC
            LIMIT :limit
        ";
        
        return $this->balanceModel->getDatabase()->select($sql, [
            'tenant_id' => $tenantId,
            'limit' => $limit
        ]);
    }
    
    private function getRevenueChartData($tenantId, $days = 30)
    {
        $labels = [];
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('M j', strtotime($date));
            
            $sql = "
                SELECT COALESCE(SUM(rt.net_tenant_amount), 0) as daily_revenue
                FROM revenue_transactions rt
                WHERE rt.tenant_id = :tenant_id
                AND rt.distribution_status = 'completed'
                AND DATE(rt.created_at) = :date
            ";
            
            $result = $this->balanceModel->getDatabase()->selectOne($sql, [
                'tenant_id' => $tenantId,
                'date' => $date
            ]);
            
            $data[] = floatval($result['daily_revenue'] ?? 0);
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    private function getPayoutMethod($tenantId)
    {
        // Get actual payout method from database
        try {
            $sql = "
                SELECT 
                    pm.id,
                    pm.type,
                    pm.provider_name as name,
                    pm.account_number as account,
                    pm.account_name,
                    pm.is_active,
                    pm.created_at
                FROM payout_methods pm
                WHERE pm.tenant_id = :tenant_id
                AND pm.is_active = 1
                ORDER BY pm.created_at DESC
                LIMIT 1
            ";
            
            $result = $this->eventModel->getDatabase()->selectOne($sql, ['tenant_id' => $tenantId]);
            
            if ($result) {
                return [
                    'type' => $result['type'],
                    'name' => $result['name'],
                    'account' => $result['account'],
                    'account_name' => $result['account_name'] ?? null
                ];
            }
        } catch (\Exception $e) {
            // Log error but don't break the page
            error_log("Error fetching payout method: " . $e->getMessage());
        }
        
        // Return null if no payout method configured
        return null;
    }
    
    // Additional methods for missing routes
    public function draftEvents()
    {
        $tenantId = $this->session->getTenantId();
        $events = $this->eventModel->findAll(['tenant_id' => $tenantId, 'status' => 'draft'], 'created_at DESC');
        
        // Get subscription limits for publish actions
        $subscriptionModel = new \SmartCast\Models\TenantSubscription();
        $tenantLimits = $subscriptionModel->getTenantLimits($tenantId) ?? $this->getDefaultTenantLimits();
        
        $content = $this->renderView('organizer/events/drafts', [
            'events' => $events,
            'tenantLimits' => $tenantLimits,
            'title' => 'Draft Events'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Draft Events',
            'breadcrumbs' => [
                ['title' => 'Events', 'url' => ORGANIZER_URL . '/events'],
                ['title' => 'Drafts']
            ]
        ]);
    }
    
    public function storeEventWizard()
    {
        // Check if this is actually a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(ORGANIZER_URL . '/events/wizard', 'Invalid request method', 'error');
            return;
        }
        
        $data = $this->sanitizeInput($_POST);
        
        // Debug: Check if we have any POST data at all
        if (empty($_POST)) {
            $this->redirect(ORGANIZER_URL . '/events/wizard', 'No form data received', 'error');
            return;
        }
        
        $tenantId = $this->session->getTenantId();
        
        // Check if this is an edit
        $isEditing = !empty($data['event_id']);
        
        // Determine the action (draft, publish, or debug)
        $action = $data['action'] ?? 'draft';
        
        // Check plan limits when publishing
        if ($action === 'publish') {
            $subscriptionModel = new \SmartCast\Models\TenantSubscription();
            
            // For new events, always check limit
            // For existing events, only check if it's currently a draft (changing from draft to published)
            $needsLimitCheck = false;
            
            if (!$isEditing) {
                // New event being published
                $needsLimitCheck = true;
            } else {
                // Existing event - check if it's currently a draft
                $currentEvent = $this->eventModel->find($data['event_id']);
                if ($currentEvent && $currentEvent['status'] === 'draft') {
                    // Draft being published - this counts as a new published event
                    $needsLimitCheck = true;
                }
            }
            
            if ($needsLimitCheck && !$subscriptionModel->canCreateEvent($tenantId)) {
                $this->redirect(ORGANIZER_URL . '/events', 'You have reached your plan limit for published events. Please upgrade your plan or keep as draft.', 'error');
                return;
            }
        }
        
        // Debug: Log vote_price value
        error_log("Vote price received: " . ($data['vote_price'] ?? 'NOT SET'));
        
        // Check required fields
        if (empty($data['name']) || empty($data['start_date']) || empty($data['end_date'])) {
            $this->redirect(ORGANIZER_URL . '/events/wizard', 'Please fill in all required fields (Name, Start Date, End Date)', 'error');
            return;
        }
        
        // Determine action (draft, publish, or debug)
        $action = $data['action'] ?? 'draft';
        $status = ($action === 'publish') ? 'active' : 'draft';
        
        // Check if this is an edit operation
        $editEventId = $data['edit_event_id'] ?? null;
        $isEditing = !empty($editEventId);
        
        try {
            // Start transaction
            $this->eventModel->getDatabase()->beginTransaction();
            
            // Handle event featured image upload
            $featuredImagePath = null;
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $featuredImagePath = $this->handleImageUpload($_FILES['featured_image'], 'events');
            } elseif ($isEditing && !empty($eventData['featured_image'])) {
                // Keep existing image if no new one uploaded
                $featuredImagePath = $eventData['featured_image'];
            }
            
            // Step 1: Create or Update Event
            $eventData = [
                'name' => $data['name'],
                'code' => $data['code'] ?? strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', strtoupper($data['name'])), 0, 10)),
                'description' => $data['description'] ?? '',
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'vote_price' => $data['vote_price'] ?? 0.50,
                'visibility' => $data['visibility'] ?? 'public',
                'status' => $status
            ];
            
            // Add featured image if uploaded
            if ($featuredImagePath) {
                $eventData['featured_image'] = $featuredImagePath;
            }
            
            if ($isEditing) {
                // Verify ownership before updating
                $existingEvent = $this->eventModel->find($editEventId);
                if (!$existingEvent || $existingEvent['tenant_id'] != $this->session->getTenantId()) {
                    throw new \Exception('Event not found or access denied');
                }
                
                $updated = $this->eventModel->update($editEventId, $eventData);
                if (!$updated) {
                    throw new \Exception('Failed to update event');
                }
                $eventId = $editEventId;
            } else {
                // Creating new event
                $eventData['tenant_id'] = $this->session->getTenantId();
                $eventData['created_by'] = $this->session->getUserId();
                
                $eventId = $this->eventModel->create($eventData);
                if (!$eventId) {
                    throw new \Exception('Failed to create event');
                }
            }
            
            // Step 2: Handle Categories
            $categoryModel = new \SmartCast\Models\Category();
            $contestantCategoryModel = new \SmartCast\Models\ContestantCategory();
            $categoryIds = [];
            
            // Store existing shortcodes before deleting (when editing) - per contestant-category pair
            $existingShortcodes = [];
            if ($isEditing) {
                // Get all existing contestant-category assignments with shortcodes
                $existingAssignments = $contestantCategoryModel->getDatabase()->select(
                    "SELECT contestant_id, category_id, short_code FROM contestant_categories WHERE category_id IN (SELECT id FROM categories WHERE event_id = :event_id)",
                    ['event_id' => $eventId]
                );
                foreach ($existingAssignments as $assignment) {
                    // Store by contestant_id + category_id combination
                    $key = $assignment['contestant_id'] . '_' . $assignment['category_id'];
                    $existingShortcodes[$key] = $assignment['short_code'];
                }
                error_log("DEBUG: Preserved " . count($existingShortcodes) . " existing shortcodes (per contestant-category)");
                
                // Delete existing categories and their assignments
                $existingCategories = $categoryModel->findAll(['event_id' => $eventId]);
                foreach ($existingCategories as $existingCategory) {
                    // Delete category assignments first
                    $contestantCategoryModel->getDatabase()->delete(
                        'contestant_categories', 
                        'category_id = :category_id', 
                        ['category_id' => $existingCategory['id']]
                    );
                    // Delete category
                    $categoryModel->delete($existingCategory['id']);
                }
            }
            
            // Create new categories
            if (!empty($data['categories']) && is_array($data['categories'])) {
                foreach ($data['categories'] as $categoryId => $categoryData) {
                    if (!empty($categoryData['name'])) {
                        $newCategoryId = $categoryModel->create([
                            'event_id' => $eventId,
                            'tenant_id' => $this->session->getTenantId(),
                            'name' => $categoryData['name'],
                            'description' => $categoryData['description'] ?? '',
                            'display_order' => $categoryData['order'] ?? 0,
                            'created_by' => $this->session->getUserId()
                        ]);
                        $categoryIds[$categoryId] = $newCategoryId; // Map old ID to new ID
                    }
                }
            }
            
            // Step 3: Handle Nominees and Assign to Categories
            $contestantModel = new \SmartCast\Models\Contestant();
            
            // Get existing contestants for comparison if editing
            $existingContestants = [];
            if ($isEditing) {
                $existingContestants = $contestantModel->findAll(['event_id' => $eventId]);
                error_log("DEBUG: Found " . count($existingContestants) . " existing contestants");
                foreach ($existingContestants as $contestant) {
                    error_log("DEBUG: Existing contestant ID: " . $contestant['id'] . ", name: " . $contestant['name'] . ", image: " . ($contestant['image_url'] ?? 'NULL'));
                }
                
                // Handle explicitly deleted nominees
                if (!empty($data['deleted_nominee_ids'])) {
                    $deletedIds = array_filter(array_map('trim', explode(',', $data['deleted_nominee_ids'])));
                    foreach ($deletedIds as $deletedId) {
                        if (is_numeric($deletedId)) {
                            error_log("DEBUG: Explicitly deleting nominee ID: $deletedId");
                            $contestantModel->delete((int)$deletedId);
                            // Remove from existing contestants array to avoid double-processing
                            $existingContestants = array_filter($existingContestants, function($c) use ($deletedId) {
                                return $c['id'] != $deletedId;
                            });
                        }
                    }
                }
            }
            
            // Handle nominees (update existing or create new)
            if (!empty($data['nominees']) && is_array($data['nominees']) && !empty($categoryIds)) {
                $processedContestantIds = [];
                
                foreach ($data['nominees'] as $nomineeId => $nomineeData) {
                    if (!empty($nomineeData['name'])) {
                        error_log("DEBUG: Processing nominee ID: $nomineeId, name: " . $nomineeData['name']);
                        
                        // Check if this is an existing contestant (when editing) - match by name
                        $existingContestant = null;
                        if ($isEditing) {
                            foreach ($existingContestants as $contestant) {
                                if ($contestant['name'] === $nomineeData['name']) {
                                    $existingContestant = $contestant;
                                    error_log("DEBUG: Found existing contestant by name match: " . $contestant['name'] . " with image: " . ($contestant['image_url'] ?? 'NULL'));
                                    break;
                                }
                            }
                            if (!$existingContestant) {
                                error_log("DEBUG: No existing contestant found for name: " . $nomineeData['name']);
                            }
                        }
                        
                        // Handle nominee photo upload
                        $nomineeImagePath = null;
                        $photoFieldName = "nominee_photo_{$nomineeId}";
                        if (isset($_FILES[$photoFieldName]) && $_FILES[$photoFieldName]['error'] === UPLOAD_ERR_OK) {
                            $nomineeImagePath = $this->handleImageUpload($_FILES[$photoFieldName], 'nominees');
                        } elseif ($existingContestant && !empty($existingContestant['image_url'])) {
                            // Preserve existing image if no new upload
                            $nomineeImagePath = $existingContestant['image_url'];
                            error_log("DEBUG: Preserving existing image for ID $nomineeId: $nomineeImagePath");
                        } else {
                            error_log("DEBUG: No image to preserve for ID $nomineeId");
                        }
                        
                        $contestantData = [
                            'tenant_id' => $this->session->getTenantId(),
                            'event_id' => $eventId,
                            'name' => $nomineeData['name'],
                            'bio' => $nomineeData['bio'] ?? '',
                            'display_order' => $nomineeData['order'] ?? 0,
                            'created_by' => $this->session->getUserId(),
                            'active' => 1
                        ];
                        
                        if ($nomineeImagePath) {
                            $contestantData['image_url'] = $nomineeImagePath;
                        }
                        
                        if ($existingContestant) {
                            // Update existing contestant
                            $contestantData['contestant_code'] = $existingContestant['contestant_code']; // Preserve code
                            $contestantModel->update($existingContestant['id'], $contestantData);
                            $contestantId = $existingContestant['id'];
                            $processedContestantIds[] = $existingContestant['id'];
                        } else {
                            // Create new contestant
                            $contestantData['contestant_code'] = $contestantModel->generateContestantCode($this->session->getTenantId(), $eventId);
                            $contestantId = $contestantModel->create($contestantData);
                        }
                        
                        // Assign to categories with preserved or auto-generated shortcodes
                        if (!empty($nomineeData['categories']) && is_array($nomineeData['categories'])) {
                            foreach ($nomineeData['categories'] as $oldCategoryId) {
                                if (isset($categoryIds[$oldCategoryId])) {
                                    $newCategoryId = $categoryIds[$oldCategoryId];
                                    
                                    // Use existing shortcode if available (when editing) - per contestant-category pair
                                    $customShortcode = null;
                                    if ($isEditing) {
                                        // Look up shortcode by contestant_id + old_category_id
                                        $key = $contestantId . '_' . $oldCategoryId;
                                        if (isset($existingShortcodes[$key])) {
                                            $customShortcode = $existingShortcodes[$key];
                                            error_log("DEBUG: Using preserved shortcode '{$customShortcode}' for contestant {$contestantId} category {$oldCategoryId}");
                                        }
                                    }
                                    
                                    $contestantCategoryModel->assignContestantToCategory($contestantId, $newCategoryId, $customShortcode);
                                    
                                    // Handle per-category photo upload
                                    $categoryPhotoFieldName = "nominee_category_photo_{$nomineeId}_{$oldCategoryId}";
                                    if (isset($_FILES[$categoryPhotoFieldName]) && $_FILES[$categoryPhotoFieldName]['error'] === UPLOAD_ERR_OK) {
                                        $categoryImagePath = $this->handleImageUpload($_FILES[$categoryPhotoFieldName], 'nominees/categories');
                                        if ($categoryImagePath) {
                                            // Update contestant_categories table with category-specific photo
                                            $contestantCategoryModel->updateCategoryPhoto($contestantId, $newCategoryId, $categoryImagePath);
                                            error_log("DEBUG: Uploaded category photo for contestant {$contestantId} category {$newCategoryId}: {$categoryImagePath}");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                // Clean up contestants that were removed (only when editing)
                if ($isEditing) {
                    foreach ($existingContestants as $existingContestant) {
                        if (!in_array($existingContestant['id'], $processedContestantIds)) {
                            // This contestant was removed from the form, delete it
                            error_log("DEBUG: Deleting removed contestant: " . $existingContestant['name']);
                            $contestantModel->delete($existingContestant['id']);
                        }
                    }
                }
            }
            
            // Commit transaction
            $this->eventModel->getDatabase()->commit();
            
            // Create success message with details
            $categoriesCount = count($categoryIds);
            $nomineesCount = !empty($data['nominees']) ? count(array_filter($data['nominees'], function($n) { return !empty($n['name']); })) : 0;
            
            $action_word = $isEditing ? 'updated' : 'created';
            $message = 'Event "' . $data['name'] . '" ' . $action_word . ' successfully';
            if ($categoriesCount > 0) {
                $message .= ' with ' . $categoriesCount . ' categories';
            }
            if ($nomineesCount > 0) {
                $message .= ' and ' . $nomineesCount . ' nominees';
            }
            $message .= '!';
            
            if ($status === 'draft') {
                $message .= ' (Saved as draft)';
            }
            
            // Redirect to events list with success message
            $this->redirect(ORGANIZER_URL . '/events', $message, 'success');
            
        } catch (\Exception $e) {
            // Rollback transaction
            try {
                $this->eventModel->getDatabase()->rollback();
            } catch (\Exception $rollbackException) {
                // Ignore rollback errors
            }
            
            // Redirect back to wizard with error
            $this->redirect(ORGANIZER_URL . '/events/wizard', 'Failed to create event: ' . $e->getMessage(), 'error');
        }
    }
    
    public function shortcodeDemo()
    {
        $content = $this->renderView('organizer/events/shortcode-demo', [
            'title' => 'USSD Shortcode System Demo'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Shortcode Demo',
            'breadcrumbs' => [
                ['title' => 'Events', 'url' => ORGANIZER_URL . '/events'],
                ['title' => 'USSD Demo']
            ]
        ]);
    }
    
    public function showEvent($id)
    {
        $tenantId = $this->session->getTenantId();
        
        // Get event details
        $event = $this->eventModel->find($id);
        if (!$event || $event['tenant_id'] != $tenantId) {
            $this->redirect(ORGANIZER_URL . '/events', 'Event not found', 'error');
            return;
        }
        
        // Get event statistics
        $eventStats = $this->getEventStatistics($id);
        
        // Get categories with contestants and voting codes
        $categoriesWithContestants = $this->getCategoriesWithContestants($id);
        
        // Get recent votes
        $recentVotes = $this->getRecentVotesForEvent($id, 10);
        
        // Get vote bundles
        $voteBundles = $this->getEventVoteBundles($id);
        
        // Get subscription limits for actions
        $subscriptionModel = new \SmartCast\Models\TenantSubscription();
        $tenantLimits = $subscriptionModel->getTenantLimits($tenantId) ?? $this->getDefaultTenantLimits();
        
        $content = $this->renderView('organizer/events/show', [
            'event' => $event,
            'eventStats' => $eventStats,
            'categoriesWithContestants' => $categoriesWithContestants,
            'recentVotes' => $recentVotes,
            'voteBundles' => $voteBundles,
            'tenantLimits' => $tenantLimits,
            'title' => $event['name']
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => $event['name'],
            'breadcrumbs' => [
                ['title' => 'Events', 'url' => ORGANIZER_URL . '/events'],
                ['title' => $event['name']]
            ]
        ]);
    }
    
    /**
     * Preview event as it would appear to the public
     */
    public function previewEvent($id)
    {
        $tenantId = $this->session->getTenantId();
        
        // Get event details
        $event = $this->eventModel->find($id);
        if (!$event || $event['tenant_id'] != $tenantId) {
            $this->redirect(ORGANIZER_URL . '/events', 'Event not found', 'error');
            return;
        }
        
        // Get event details similar to public view
        $categories = $this->categoryModel->getCategoriesByEvent($event['id']);
        $contestants = $this->contestantModel->getContestantsByEvent($event['id']);
        $bundles = $this->bundleModel->getBundlesByEvent($event['id']);
        
        // Get category-specific leaderboards
        $leaderboards = [];
        foreach ($categories as $category) {
            $leaderboards[$category['id']] = [
                'category' => $category,
                'leaderboard' => $this->leaderboardModel->getLeaderboard($event['id'], $category['id'], 10)
            ];
        }
        
        // For backward compatibility, get overall leaderboard (first category or empty)
        $leaderboard = !empty($leaderboards) ? reset($leaderboards)['leaderboard'] : [];
        
        // Check if voting would be allowed (simulate public view)
        $canVote = ($event['status'] === 'active' && 
                   strtotime($event['start_date']) <= time() && 
                   strtotime($event['end_date']) >= time());
        
        // Use the public event view but with preview context
        $this->view('events/preview', [
            'event' => $event,
            'categories' => $categories,
            'contestants' => $contestants,
            'bundles' => $bundles,
            'leaderboard' => $leaderboard,
            'leaderboards' => $leaderboards,
            'canVote' => $canVote,
            'isPreview' => true,
            'title' => 'Preview: ' . $event['name']
        ]);
    }
    
    /**
     * Get comprehensive event statistics
     */
    private function getEventStatistics($eventId)
    {
        try {
            // Get basic event info first
            $event = $this->eventModel->find($eventId);
            if (!$event) {
                return [];
            }
            
            // Get total contestants
            $contestantSql = "
                SELECT COUNT(DISTINCT c.id) as total_contestants
                FROM contestants c
                WHERE c.event_id = :event_id AND c.active = 1
            ";
            $contestantResult = $this->eventModel->getDatabase()->selectOne($contestantSql, ['event_id' => $eventId]);
            
            // Get total categories
            $categorySql = "
                SELECT COUNT(DISTINCT cat.id) as total_categories
                FROM categories cat
                WHERE cat.event_id = :event_id
            ";
            $categoryResult = $this->eventModel->getDatabase()->selectOne($categorySql, ['event_id' => $eventId]);
            
            // Get vote statistics
            $voteSql = "
                SELECT 
                    COALESCE(SUM(v.quantity), 0) as total_votes,
                    COUNT(DISTINCT v.id) as total_vote_records
                FROM votes v
                WHERE v.event_id = :event_id
            ";
            $voteResult = $this->eventModel->getDatabase()->selectOne($voteSql, ['event_id' => $eventId]);
            
            // Get transaction statistics
            $transactionSql = "
                SELECT 
                    COUNT(DISTINCT t.id) as total_transactions,
                    COALESCE(SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END), 0) as total_revenue,
                    COALESCE(AVG(CASE WHEN t.status = 'success' THEN t.amount ELSE NULL END), 0) as avg_transaction_amount,
                    COUNT(DISTINCT CASE WHEN t.status = 'success' THEN t.phone_number ELSE NULL END) as unique_voters
                FROM transactions t
                WHERE t.event_id = :event_id
            ";
            $transactionResult = $this->eventModel->getDatabase()->selectOne($transactionSql, ['event_id' => $eventId]);
            
            // Combine all results
            $stats = array_merge($event, [
                'total_contestants' => (int)($contestantResult['total_contestants'] ?? 0),
                'total_categories' => (int)($categoryResult['total_categories'] ?? 0),
                'total_votes' => (int)($voteResult['total_votes'] ?? 0),
                'total_vote_records' => (int)($voteResult['total_vote_records'] ?? 0),
                'total_transactions' => (int)($transactionResult['total_transactions'] ?? 0),
                'total_revenue' => (float)($transactionResult['total_revenue'] ?? 0),
                'avg_transaction_amount' => (float)($transactionResult['avg_transaction_amount'] ?? 0),
                'unique_voters' => (int)($transactionResult['unique_voters'] ?? 0),
                'vote_price' => (float)($event['vote_price'] ?? 0)
            ]);
            
            return $stats;
            
        } catch (\Exception $e) {
            // Log error and return empty stats
            error_log("Error getting event statistics for event $eventId: " . $e->getMessage());
            
            // Try to get basic event info for vote_price at least
            $basicEvent = $this->eventModel->find($eventId);
            
            return [
                'total_contestants' => 0,
                'total_categories' => 0,
                'total_votes' => 0,
                'total_transactions' => 0,
                'total_revenue' => 0,
                'avg_transaction_amount' => 0,
                'unique_voters' => 0,
                'vote_price' => (float)($basicEvent['vote_price'] ?? 0)
            ];
        }
    }
    
    /**
     * Get categories with their contestants and voting codes
     */
    private function getCategoriesWithContestants($eventId)
    {
        // Get categories
        $categorySql = "
            SELECT 
                cat.id as category_id,
                cat.name as category_name,
                cat.description as category_description,
                COUNT(DISTINCT cc.contestant_id) as contestant_count
            FROM categories cat
            LEFT JOIN contestant_categories cc ON cat.id = cc.category_id
            LEFT JOIN contestants c ON cc.contestant_id = c.id AND c.active = 1
            WHERE cat.event_id = :event_id
            GROUP BY cat.id, cat.name, cat.description
            ORDER BY cat.name ASC
        ";
        
        $categories = $this->eventModel->getDatabase()->select($categorySql, ['event_id' => $eventId]);
        
        // Get contestants for each category
        foreach ($categories as &$category) {
            $contestantSql = "
                SELECT 
                    c.id,
                    c.name,
                    c.contestant_code,
                    c.image_url,
                    c.bio,
                    cc.short_code as voting_shortcode,
                    COALESCE(vote_stats.total_votes, 0) as total_votes,
                    COALESCE(vote_stats.revenue, 0) as revenue,
                    COALESCE(vote_stats.last_vote_at, NULL) as last_vote_at
                FROM contestants c
                INNER JOIN contestant_categories cc ON c.id = cc.contestant_id
                LEFT JOIN (
                    SELECT 
                        v.contestant_id,
                        SUM(v.quantity) as total_votes,
                        SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END) as revenue,
                        MAX(v.created_at) as last_vote_at
                    FROM votes v
                    LEFT JOIN transactions t ON v.transaction_id = t.id
                    WHERE v.category_id = :category_id_for_votes
                    GROUP BY v.contestant_id
                ) vote_stats ON c.id = vote_stats.contestant_id
                WHERE cc.category_id = :category_id
                AND c.active = 1
                ORDER BY vote_stats.total_votes DESC, c.name ASC
            ";
            
            $category['contestants'] = $this->eventModel->getDatabase()->select($contestantSql, [
                'category_id' => $category['category_id'],
                'category_id_for_votes' => $category['category_id']
            ]);
        }
        
        return $categories;
    }
    
    /**
     * Get recent votes for the event
     */
    private function getRecentVotesForEvent($eventId, $limit = 10)
    {
        $sql = "
            SELECT 
                c.name as contestant_name,
                c.contestant_code,
                cc.short_code as voting_code,
                cat.name as category_name,
                v.quantity,
                t.amount,
                t.status as payment_status,
                v.created_at,
                TIMESTAMPDIFF(SECOND, v.created_at, NOW()) as seconds_ago
            FROM votes v
            INNER JOIN contestants c ON v.contestant_id = c.id
            INNER JOIN contestant_categories cc ON c.id = cc.contestant_id
            INNER JOIN categories cat ON cc.category_id = cat.id
            LEFT JOIN transactions t ON v.transaction_id = t.id
            WHERE v.event_id = :event_id
            ORDER BY v.created_at DESC
            LIMIT :limit
        ";
        
        return $this->eventModel->getDatabase()->select($sql, ['event_id' => $eventId, 'limit' => $limit]);
    }
    
    /**
     * Get vote bundles for the event
     */
    private function getEventVoteBundles($eventId)
    {
        $sql = "
            SELECT 
                vb.id,
                vb.name,
                vb.votes,
                vb.price,
                vb.active,
                COUNT(t.id) as times_purchased,
                SUM(t.amount) as total_revenue
            FROM vote_bundles vb
            LEFT JOIN transactions t ON vb.id = t.bundle_id AND t.status = 'success'
            WHERE vb.event_id = :event_id
            GROUP BY vb.id, vb.name, vb.votes, vb.price, vb.active
            ORDER BY vb.votes ASC
        ";
        
        return $this->eventModel->getDatabase()->select($sql, ['event_id' => $eventId]);
    }
    
    /**
     * Export event categories and contestants to PDF
     */
    public function exportEventPDF($eventId)
    {
        $tenantId = $this->session->getTenantId();
        
        // Get event details
        $event = $this->eventModel->find($eventId);
        if (!$event || $event['tenant_id'] != $tenantId) {
            $this->redirect(ORGANIZER_URL . '/events', 'Event not found', 'error');
            return;
        }
        
        // Get event statistics and data
        $eventStats = $this->getEventStatistics($eventId);
        $categoriesWithContestants = $this->getCategoriesWithContestants($eventId);
        
        // Generate PDF content
        $pdfContent = $this->generateEventPDFContent($event, $eventStats, $categoriesWithContestants);
        
        // For now, we'll output HTML that can be printed as PDF
        // In production, you'd use a library like TCPDF, mPDF, or wkhtmltopdf
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="' . $this->sanitizeFilename($event['name']) . '_contestants.html"');
        
        echo $pdfContent;
        exit;
    }
    
    /**
     * Generate PDF content (simple HTML to PDF approach)
     */
    private function generateEventPDFContent($event, $eventStats, $categoriesWithContestants)
    {
        // For now, we'll generate HTML that can be converted to PDF
        // In a production environment, you'd use a library like TCPDF or mPDF
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?= htmlspecialchars($event['name']) ?> - Contestants List</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.4; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                .event-info { margin-bottom: 30px; }
                .event-info table { width: 100%; border-collapse: collapse; }
                .event-info table td { padding: 5px; border-bottom: 1px solid #eee; }
                .category { margin-bottom: 25px; page-break-inside: avoid; }
                .category-header { background: #f5f5f5; padding: 10px; border-left: 4px solid #007bff; margin-bottom: 10px; }
                .contestants-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                .contestants-table th, .contestants-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .contestants-table th { background: #f8f9fa; font-weight: bold; }
                .voting-code { font-family: monospace; font-weight: bold; color: #007bff; background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
                .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #eee; padding-top: 20px; }
                @media print {
                    body { margin: 0; }
                    .category { page-break-inside: avoid; }
                    .contestants-table { page-break-inside: auto; }
                    .contestants-table tr { page-break-inside: avoid; }
                }
                .print-button { position: fixed; top: 10px; right: 10px; z-index: 1000; }
                @media print { .print-button { display: none; } }
            </style>
        </head>
        <body>
            <button class="print-button btn btn-primary" onclick="window.print()" style="background: #007bff; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">
                Print PDF
            </button>
            
            <div class="header">
                <h1><?= htmlspecialchars($event['name']) ?></h1>
                <p><strong>Contestants & Voting Codes</strong></p>
                <p>Generated on <?= date('F j, Y \a\t g:i A') ?></p>
            </div>
            
            <div class="event-info">
                <h2>Event Information</h2>
                <table style="width: 100%; margin-bottom: 20px;">
                    <tr><td><strong>Event Name:</strong></td><td><?= htmlspecialchars($event['name']) ?></td></tr>
                    <tr><td><strong>Status:</strong></td><td><?= ucfirst($event['status']) ?></td></tr>
                    <tr><td><strong>Vote Price:</strong></td><td>$<?= number_format($eventStats['vote_price'], 2) ?></td></tr>
                    <tr><td><strong>Start Date:</strong></td><td><?= date('F j, Y', strtotime($event['start_date'])) ?></td></tr>
                    <tr><td><strong>End Date:</strong></td><td><?= date('F j, Y', strtotime($event['end_date'])) ?></td></tr>
                    <tr><td><strong>Total Categories:</strong></td><td><?= $eventStats['total_categories'] ?></td></tr>
                    <tr><td><strong>Total Contestants:</strong></td><td><?= $eventStats['total_contestants'] ?></td></tr>
                    <tr><td><strong>Total Votes:</strong></td><td><?= number_format($eventStats['total_votes']) ?></td></tr>
                    <tr><td><strong>Total Revenue:</strong></td><td>$<?= number_format($eventStats['total_revenue'], 2) ?></td></tr>
                </table>
            </div>
            
            <?php foreach ($categoriesWithContestants as $category): ?>
            <div class="category">
                <div class="category-header">
                    <h3><?= htmlspecialchars($category['category_name']) ?></h3>
                    <?php if (!empty($category['category_description'])): ?>
                        <p><?= htmlspecialchars($category['category_description']) ?></p>
                    <?php endif; ?>
                    <p><strong><?= count($category['contestants']) ?> Contestants</strong></p>
                </div>
                
                <?php if (!empty($category['contestants'])): ?>
                <table class="contestants-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Contestant Name</th>
                            <th>Voting Code</th>
                            <th>Votes</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($category['contestants'] as $index => $contestant): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($contestant['name']) ?></td>
                            <td class="voting-code"><?= htmlspecialchars($contestant['voting_shortcode']) ?></td>
                            <td><?= number_format($contestant['total_votes']) ?></td>
                            <td>$<?= number_format($contestant['revenue'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p><em>No contestants in this category yet.</em></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            
            <div class="footer">
                <p>This document was generated by SmartCast Voting Platform</p>
                <p>For support, visit our website or contact your administrator</p>
            </div>
        </body>
        </html>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Sanitize filename for download
     */
    private function sanitizeFilename($filename)
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
    }
    
    public function createContestant()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get events for this tenant
        $events = $this->eventModel->getEventsByTenant($tenantId);
        
        $this->view('organizer/contestants/create', [
            'events' => $events,
            'title' => 'Add Contestant'
        ]);
    }

    /**
     * Store new contestants with category assignments
     */
    public function storeContestant()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(ORGANIZER_URL . '/contestants/create', 'Invalid request method', 'error');
            return;
        }

        try {
            $tenantId = $this->session->getTenantId();
            $eventId = $_POST['event_id'] ?? null;
            $contestants = $_POST['contestants'] ?? [];

            // Validation
            if (!$eventId) {
                $this->redirect(ORGANIZER_URL . '/contestants/create', 'Please select an event', 'error');
                return;
            }

            if (empty($contestants)) {
                $this->redirect(ORGANIZER_URL . '/contestants/create', 'Please add at least one contestant', 'error');
                return;
            }

            // Verify event belongs to tenant
            $event = $this->eventModel->find($eventId);
            if (!$event || $event['tenant_id'] != $tenantId) {
                $this->redirect(ORGANIZER_URL . '/contestants/create', 'Invalid event selected', 'error');
                return;
            }

            // Start database transaction
            $this->db->beginTransaction();

            $createdCount = 0;
            $errors = [];

            foreach ($contestants as $contestantId => $contestantData) {
                try {
                    // Create contestant
                    $contestantRecord = [
                        'tenant_id' => $tenantId,
                        'event_id' => $eventId,
                        'name' => $contestantData['name'],
                        'bio' => $contestantData['bio'] ?? '',
                        'active' => 1
                    ];

                    // Handle photo upload
                    $photoKey = "contestant_photo_{$contestantId}";
                    if (isset($_FILES[$photoKey]) && $_FILES[$photoKey]['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = $this->handlePhotoUpload($_FILES[$photoKey], 'contestants');
                        if ($uploadResult['success']) {
                            $contestantRecord['image_url'] = $uploadResult['file_path'];
                        } else {
                            error_log("Photo upload failed for contestant {$contestantData['name']}: " . $uploadResult['error']);
                            // Continue without photo - don't fail the entire contestant creation
                        }
                    } elseif (isset($_FILES[$photoKey]) && $_FILES[$photoKey]['error'] !== UPLOAD_ERR_NO_FILE) {
                        // Log upload errors (but don't fail the creation)
                        error_log("Photo upload error for contestant {$contestantData['name']}: " . $_FILES[$photoKey]['error']);
                    }

                    // Create contestant record
                    $newContestantId = $this->contestantModel->create($contestantRecord);

                    if ($newContestantId) {
                        // Assign to categories with shortcode generation
                        $categories = $contestantData['categories'] ?? [];
                        if (!empty($categories)) {
                            $contestantCategoryModel = new \SmartCast\Models\ContestantCategory();
                            
                            foreach ($categories as $categoryId) {
                                $contestantCategoryModel->assignContestantToCategory(
                                    $newContestantId, 
                                    $categoryId
                                );
                            }
                        }

                        $createdCount++;
                    }

                } catch (\Exception $e) {
                    $errors[] = "Error creating contestant '{$contestantData['name']}': " . $e->getMessage();
                    error_log("Contestant creation error: " . $e->getMessage());
                }
            }

            // Commit transaction
            $this->db->commit();

            // Prepare success message
            $message = "Successfully created {$createdCount} contestant(s)";
            if (!empty($errors)) {
                $message .= ". " . count($errors) . " error(s) occurred.";
            }

            $this->redirect(ORGANIZER_URL . '/contestants', $message, 'success');

        } catch (\Exception $e) {
            // Rollback transaction
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            
            error_log('Store contestant error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . '/contestants/create', 'An error occurred while creating contestants: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Handle photo upload for contestants
     */
    private function handlePhotoUpload($file, $folder = 'contestants')
    {
        try {
            // Basic validation
            if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                return ['success' => false, 'error' => 'Invalid upload file.'];
            }

            // Validate file type by extension (more reliable than MIME type)
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions)) {
                return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.'];
            }

            // Also check MIME type as secondary validation
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                return ['success' => false, 'error' => 'Invalid MIME type. Only image files are allowed.'];
            }

            // Check file size (2MB max)
            if ($file['size'] > 2 * 1024 * 1024) {
                return ['success' => false, 'error' => 'File too large. Maximum size is 2MB.'];
            }

            // Check if file is actually an image
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return ['success' => false, 'error' => 'File is not a valid image.'];
            }

            // Create upload directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../public/uploads/' . $folder . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Verify file was actually moved and is readable
                if (file_exists($filePath) && is_readable($filePath)) {
                    return [
                        'success' => true,
                        'file_path' => '/public/uploads/' . $folder . '/' . $filename,
                        'filename' => $filename
                    ];
                } else {
                    return ['success' => false, 'error' => 'File was moved but is not accessible.'];
                }
            } else {
                $error = error_get_last();
                return ['success' => false, 'error' => 'Failed to move uploaded file: ' . ($error['message'] ?? 'Unknown error')];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Upload error: ' . $e->getMessage()];
        }
    }

    /**
     * Show individual contestant details
     */
    public function showContestant($id)
    {
        try {
            $tenantId = $this->session->getTenantId();
            
            // Get contestant with event and category information
            $sql = "
                SELECT c.*, e.name as event_name, e.id as event_id,
                       GROUP_CONCAT(DISTINCT cat.name) as categories,
                       GROUP_CONCAT(DISTINCT cc.short_code) as shortcodes,
                       COUNT(DISTINCT CASE WHEN t.status = 'success' THEN t.id END) as total_votes,
                       COUNT(DISTINCT CASE WHEN t.status = 'success' THEN t.id END) as total_transactions
                FROM contestants c
                LEFT JOIN events e ON c.event_id = e.id
                LEFT JOIN contestant_categories cc ON c.id = cc.contestant_id
                LEFT JOIN categories cat ON cc.category_id = cat.id
                LEFT JOIN transactions t ON c.id = t.contestant_id
                WHERE c.id = :id AND c.tenant_id = :tenant_id
                GROUP BY c.id
            ";
            
            $contestant = $this->db->selectOne($sql, [
                'id' => $id,
                'tenant_id' => $tenantId
            ]);
            
            if (!$contestant) {
                $this->redirect(ORGANIZER_URL . '/contestants', 'Contestant not found', 'error');
                return;
            }
            
            $this->view('organizer/contestants/show', [
                'contestant' => $contestant,
                'title' => 'Contestant Details - ' . $contestant['name']
            ]);
            
        } catch (\Exception $e) {
            error_log('Show contestant error: ' . $e->getMessage());
            error_log('Show contestant stack trace: ' . $e->getTraceAsString());
            $this->redirect(ORGANIZER_URL . '/contestants', 'Error loading contestant details: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Show contestant edit form
     */
    public function editContestant($id)
    {
        try {
            $tenantId = $this->session->getTenantId();
            
            // Get contestant details
            $contestant = $this->db->selectOne("
                SELECT c.*, e.name as event_name 
                FROM contestants c
                LEFT JOIN events e ON c.event_id = e.id
                WHERE c.id = :id AND c.tenant_id = :tenant_id
            ", [
                'id' => $id,
                'tenant_id' => $tenantId
            ]);
            
            if (!$contestant) {
                $this->redirect(ORGANIZER_URL . '/contestants', 'Contestant not found', 'error');
                return;
            }
            
            // Get contestant's current categories (remove active column check)
            $currentCategories = $this->db->select("
                SELECT cc.category_id, cc.short_code, cat.name as category_name
                FROM contestant_categories cc
                INNER JOIN categories cat ON cc.category_id = cat.id
                WHERE cc.contestant_id = :contestant_id
            ", ['contestant_id' => $id]);
            
            // Get all categories for this event (remove active column check)
            $allCategories = $this->db->select("
                SELECT id, name
                FROM categories
                WHERE event_id = :event_id
                ORDER BY name ASC
            ", ['event_id' => $contestant['event_id']]);
            
            $this->view('organizer/contestants/edit', [
                'contestant' => $contestant,
                'current_categories' => $currentCategories,
                'all_categories' => $allCategories,
                'title' => 'Edit Contestant - ' . $contestant['name']
            ]);
            
        } catch (\Exception $e) {
            error_log('Edit contestant error: ' . $e->getMessage());
            error_log('Edit contestant stack trace: ' . $e->getTraceAsString());
            $this->redirect(ORGANIZER_URL . '/contestants', 'Error loading contestant for editing: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Update contestant details
     */
    public function updateContestant($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(ORGANIZER_URL . "/contestants/{$id}/edit", 'Invalid request method', 'error');
            return;
        }

        try {
            $tenantId = $this->session->getTenantId();
            
            // Verify contestant belongs to tenant
            $contestant = $this->db->selectOne("
                SELECT * FROM contestants 
                WHERE id = :id AND tenant_id = :tenant_id
            ", [
                'id' => $id,
                'tenant_id' => $tenantId
            ]);
            
            if (!$contestant) {
                $this->redirect(ORGANIZER_URL . '/contestants', 'Contestant not found', 'error');
                return;
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Update contestant basic info
            $updateData = [
                'name' => $_POST['name'] ?? $contestant['name'],
                'bio' => $_POST['bio'] ?? '',
                'active' => isset($_POST['active']) ? 1 : 0
            ];
            
            // Handle photo upload if provided
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handlePhotoUpload($_FILES['photo'], 'contestants');
                if ($uploadResult['success']) {
                    $updateData['image_url'] = $uploadResult['file_path'];
                }
            }
            
            // Update contestant record
            $this->db->query("
                UPDATE contestants 
                SET name = :name, bio = :bio, active = :active" . 
                (isset($updateData['image_url']) ? ", image_url = :image_url" : "") . "
                WHERE id = :id
            ", array_merge($updateData, ['id' => $id]));
            
            // Update category assignments if provided
            if (isset($_POST['categories'])) {
                // Get current active assignments to preserve existing shortcodes
                $currentAssignments = $this->db->select("
                    SELECT category_id, short_code 
                    FROM contestant_categories 
                    WHERE contestant_id = :contestant_id AND active = 1
                ", ['contestant_id' => $id]);
                
                $currentCategoryIds = array_column($currentAssignments, 'category_id');
                $currentShortCodes = [];
                foreach ($currentAssignments as $assignment) {
                    $currentShortCodes[$assignment['category_id']] = $assignment['short_code'];
                }
                
                // Deactivate current assignments
                $this->db->query("
                    UPDATE contestant_categories 
                    SET active = 0 
                    WHERE contestant_id = :contestant_id
                ", ['contestant_id' => $id]);
                
                // Process category assignments
                $contestantCategoryModel = new \SmartCast\Models\ContestantCategory();
                foreach ($_POST['categories'] as $categoryId) {
                    if (in_array($categoryId, $currentCategoryIds)) {
                        // Existing category - preserve shortcode
                        $existingShortCode = $currentShortCodes[$categoryId];
                        $contestantCategoryModel->assignContestantToCategory($id, $categoryId, $existingShortCode);
                    } else {
                        // New category - generate new shortcode
                        $contestantCategoryModel->assignContestantToCategory($id, $categoryId);
                    }
                }
            }
            
            $this->db->commit();
            
            $this->redirect(ORGANIZER_URL . "/contestants/{$id}", 'Contestant updated successfully', 'success');
            
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            
            error_log('Update contestant error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . "/contestants/{$id}/edit", 'Error updating contestant: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Show contestant statistics
     */
    public function contestantStats($id)
    {
        try {
            $tenantId = $this->session->getTenantId();
            
            // Get contestant basic info
            $contestant = $this->db->selectOne("
                SELECT c.*, e.name as event_name, e.id as event_id
                FROM contestants c
                LEFT JOIN events e ON c.event_id = e.id
                WHERE c.id = :id AND c.tenant_id = :tenant_id
            ", [
                'id' => $id,
                'tenant_id' => $tenantId
            ]);
            
            if (!$contestant) {
                $this->redirect(ORGANIZER_URL . '/contestants', 'Contestant not found', 'error');
                return;
            }
            
            // Get voting statistics (try with votes table first, fallback to transactions only)
            try {
                $voteStats = $this->db->selectOne("
                    SELECT 
                        COALESCE(SUM(CASE WHEN t.status = 'success' THEN 
                            COALESCE(v.quantity, 1) 
                        ELSE 0 END), 0) as total_votes,
                        COUNT(DISTINCT CASE WHEN t.status = 'success' THEN t.id END) as total_transactions,
                        COALESCE(SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END), 0) as total_revenue,
                        COUNT(DISTINCT CASE WHEN t.status = 'success' THEN DATE(t.created_at) END) as voting_days
                    FROM transactions t
                    LEFT JOIN votes v ON t.id = v.transaction_id
                    WHERE t.contestant_id = :contestant_id
                ", ['contestant_id' => $id]);
            } catch (\Exception $e) {
                error_log("Votes table query failed, using transactions only: " . $e->getMessage());
                // Fallback to transactions table only
                $voteStats = $this->db->selectOne("
                    SELECT 
                        COUNT(CASE WHEN t.status = 'success' THEN t.id END) as total_votes,
                        COUNT(DISTINCT CASE WHEN t.status = 'success' THEN t.id END) as total_transactions,
                        COALESCE(SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END), 0) as total_revenue,
                        COUNT(DISTINCT CASE WHEN t.status = 'success' THEN DATE(t.created_at) END) as voting_days
                    FROM transactions t
                    WHERE t.contestant_id = :contestant_id
                ", ['contestant_id' => $id]);
            }
            
            // Get category-wise breakdown (try with votes table first, fallback to transactions only)
            try {
                $categoryStats = $this->db->select("
                    SELECT 
                        cat.name as category_name,
                        cc.short_code,
                        COALESCE(SUM(CASE WHEN t.status = 'success' THEN 
                            COALESCE(v.quantity, 1) 
                        ELSE 0 END), 0) as votes,
                        COUNT(DISTINCT CASE WHEN t.status = 'success' THEN t.id END) as transactions
                    FROM contestant_categories cc
                    INNER JOIN categories cat ON cc.category_id = cat.id
                    LEFT JOIN transactions t ON cc.contestant_id = t.contestant_id AND cc.category_id = t.category_id
                    LEFT JOIN votes v ON t.id = v.transaction_id
                    WHERE cc.contestant_id = :contestant_id
                    GROUP BY cc.id, cat.name, cc.short_code
                    ORDER BY votes DESC
                ", ['contestant_id' => $id]);
            } catch (\Exception $e) {
                error_log("Category stats with votes failed, using transactions only: " . $e->getMessage());
                // Fallback to transactions table only
                $categoryStats = $this->db->select("
                    SELECT 
                        cat.name as category_name,
                        cc.short_code,
                        COUNT(CASE WHEN t.status = 'success' THEN t.id END) as votes,
                        COUNT(DISTINCT CASE WHEN t.status = 'success' THEN t.id END) as transactions
                    FROM contestant_categories cc
                    INNER JOIN categories cat ON cc.category_id = cat.id
                    LEFT JOIN transactions t ON cc.contestant_id = t.contestant_id AND cc.category_id = t.category_id
                    WHERE cc.contestant_id = :contestant_id
                    GROUP BY cc.id, cat.name, cc.short_code
                    ORDER BY votes DESC
                ", ['contestant_id' => $id]);
            }
            
            // Get recent voting activity (using both votes and transactions)
            $recentVotes = $this->db->select("
                SELECT 
                    t.created_at,
                    t.amount,
                    COALESCE(v.quantity, 1) as quantity,
                    cat.name as category_name,
                    'Anonymous' as voter_name,
                    t.msisdn
                FROM transactions t
                LEFT JOIN votes v ON t.id = v.transaction_id
                LEFT JOIN categories cat ON t.category_id = cat.id
                WHERE t.contestant_id = :contestant_id AND t.status = 'success'
                ORDER BY t.created_at DESC
                LIMIT 20
            ", ['contestant_id' => $id]);
            
            // Debug logging to check what data we're getting
            error_log("Contestant Stats Debug for ID {$id}:");
            error_log("Vote Stats: " . json_encode($voteStats));
            error_log("Category Stats: " . json_encode($categoryStats));
            error_log("Recent Votes: " . json_encode($recentVotes));
            
            $this->view('organizer/contestants/stats', [
                'contestant' => $contestant,
                'vote_stats' => $voteStats,
                'category_stats' => $categoryStats,
                'recent_votes' => $recentVotes,
                'title' => 'Statistics - ' . $contestant['name']
            ]);
            
        } catch (\Exception $e) {
            error_log('Contestant stats error: ' . $e->getMessage());
            error_log('Contestant stats stack trace: ' . $e->getTraceAsString());
            $this->redirect(ORGANIZER_URL . '/contestants', 'Error loading contestant statistics: ' . $e->getMessage(), 'error');
        }
    }
    
    public function categories()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get categories with real statistics including event dates and status
        $sql = "
            SELECT 
                cat.*,
                e.name as event_name,
                e.status as event_status,
                e.start_date as event_start_date,
                e.end_date as event_end_date,
                e.vote_price,
                COUNT(DISTINCT cc.contestant_id) as contestant_count,
                COALESCE(SUM(v.quantity), 0) as total_votes,
                COALESCE(SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END), 0) as revenue
            FROM categories cat
            INNER JOIN events e ON cat.event_id = e.id
            LEFT JOIN contestant_categories cc ON cat.id = cc.category_id
            LEFT JOIN contestants c ON cc.contestant_id = c.id AND c.active = 1
            LEFT JOIN votes v ON c.id = v.contestant_id AND v.category_id = cat.id
            LEFT JOIN transactions t ON v.transaction_id = t.id
            WHERE e.tenant_id = :tenant_id
            GROUP BY cat.id, cat.name, cat.description, cat.event_id, cat.created_at, cat.updated_at,
                     e.name, e.status, e.start_date, e.end_date, e.vote_price
            ORDER BY cat.created_at DESC
        ";
        
        try {
            $categories = $this->contestantModel->getDatabase()->select($sql, ['tenant_id' => $tenantId]);
            
            // Calculate additional metrics for each category
            foreach ($categories as &$category) {
                $category['contestant_count'] = (int)$category['contestant_count'];
                $category['total_votes'] = (int)$category['total_votes'];
                $category['revenue'] = (float)$category['revenue'];
                
                // Calculate average votes per contestant
                $category['avg_votes'] = $category['contestant_count'] > 0 ? 
                    $category['total_votes'] / $category['contestant_count'] : 0;
            }
            
        } catch (\Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            $categories = [];
        }
        
        // Get events for the dropdown
        $events = $this->eventModel->getEventsByTenant($tenantId);
        
        $content = $this->renderView('organizer/categories/index', [
            'categories' => $categories,
            'events' => $events,
            'title' => 'Categories'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Categories',
            'breadcrumbs' => [
                ['title' => 'Categories']
            ]
        ]);
    }
    
    /**
     * Store a new category
     */
    public function storeCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(ORGANIZER_URL . '/categories', 'Invalid request method', 'error');
            return;
        }
        
        $tenantId = $this->session->getTenantId();
        $data = $this->sanitizeInput($_POST);
        
        try {
            // Validate required fields
            if (empty($data['name']) || empty($data['event_id'])) {
                throw new \Exception('Category name and event are required');
            }
            
            // Verify event ownership
            $event = $this->eventModel->find($data['event_id']);
            if (!$event || $event['tenant_id'] != $tenantId) {
                throw new \Exception('Event not found or access denied');
            }
            
            // Create category
            $categoryModel = new \SmartCast\Models\Category();
            $categoryId = $categoryModel->create([
                'event_id' => $data['event_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'active' => isset($data['active']) ? 1 : 0,
                'display_order' => 0
            ]);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Category created successfully', 'id' => $categoryId]);
                return;
            }
            
            $this->redirect(ORGANIZER_URL . '/categories', 'Category created successfully', 'success');
            
        } catch (\Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                return;
            }
            
            $this->redirect(ORGANIZER_URL . '/categories', 'Failed to create category: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Show a specific category
     */
    public function showCategory($id)
    {
        $tenantId = $this->session->getTenantId();
        
        try {
            // Get category with event info
            $sql = "
                SELECT cat.*, e.name as event_name, e.status as event_status,
                       e.start_date, e.end_date, e.tenant_id
                FROM categories cat
                INNER JOIN events e ON cat.event_id = e.id
                WHERE cat.id = :category_id AND e.tenant_id = :tenant_id
            ";
            
            $category = $this->categoryModel->getDatabase()->selectOne($sql, [
                'category_id' => $id,
                'tenant_id' => $tenantId
            ]);
            
            if (!$category) {
                $this->redirect(ORGANIZER_URL . '/categories', 'Category not found', 'error');
                return;
            }
            
            // Get contestants in this category
            $contestantsSql = "
                SELECT c.*, cc.short_code as voting_shortcode,
                       COALESCE(vote_stats.total_votes, 0) as total_votes,
                       COALESCE(vote_stats.revenue, 0) as revenue
                FROM contestants c
                INNER JOIN contestant_categories cc ON c.id = cc.contestant_id
                LEFT JOIN (
                    SELECT v.contestant_id,
                           SUM(v.quantity) as total_votes,
                           SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END) as revenue
                    FROM votes v
                    LEFT JOIN transactions t ON v.transaction_id = t.id
                    WHERE v.category_id = :category_id
                    GROUP BY v.contestant_id
                ) vote_stats ON c.id = vote_stats.contestant_id
                WHERE cc.category_id = :category_id AND c.active = 1
                ORDER BY vote_stats.total_votes DESC, c.name ASC
            ";
            
            $contestants = $this->categoryModel->getDatabase()->select($contestantsSql, ['category_id' => $id]);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'category' => $category,
                    'contestants' => $contestants
                ]);
                return;
            }
            
            $content = $this->renderView('organizer/categories/show', [
                'category' => $category,
                'contestants' => $contestants,
                'title' => $category['name']
            ]);
            
            echo $this->renderLayout('organizer_layout', $content, [
                'title' => $category['name'],
                'breadcrumbs' => [
                    ['title' => 'Categories', 'url' => ORGANIZER_URL . '/categories'],
                    ['title' => $category['name']]
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->redirect(ORGANIZER_URL . '/categories', 'Error loading category: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Update a category
     */
    public function updateCategory($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(ORGANIZER_URL . '/categories', 'Invalid request method', 'error');
            return;
        }
        
        $tenantId = $this->session->getTenantId();
        $data = $this->sanitizeInput($_POST);
        
        try {
            // Verify category ownership
            $sql = "
                SELECT cat.*, e.tenant_id, e.status as event_status
                FROM categories cat
                INNER JOIN events e ON cat.event_id = e.id
                WHERE cat.id = :category_id AND e.tenant_id = :tenant_id
            ";
            
            $category = $this->categoryModel->getDatabase()->selectOne($sql, [
                'category_id' => $id,
                'tenant_id' => $tenantId
            ]);
            
            if (!$category) {
                throw new \Exception('Category not found or access denied');
            }
            
            // Validate required fields
            if (empty($data['name'])) {
                throw new \Exception('Category name is required');
            }
            
            // Update category
            $categoryModel = new \SmartCast\Models\Category();
            $categoryModel->update($id, [
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'active' => isset($data['active']) ? 1 : 0
            ]);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
                return;
            }
            
            $this->redirect(ORGANIZER_URL . '/categories', 'Category updated successfully', 'success');
            
        } catch (\Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                return;
            }
            
            $this->redirect(ORGANIZER_URL . '/categories', 'Failed to update category: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Delete a category and all its contestants
     */
    public function deleteCategory($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(ORGANIZER_URL . '/categories', 'Invalid request method', 'error');
            return;
        }
        
        $tenantId = $this->session->getTenantId();
        
        try {
            // Get category with event info to check if deletion is allowed
            $sql = "
                SELECT cat.*, e.tenant_id, e.status as event_status, e.start_date, e.end_date
                FROM categories cat
                INNER JOIN events e ON cat.event_id = e.id
                WHERE cat.id = :category_id AND e.tenant_id = :tenant_id
            ";
            
            $category = $this->categoryModel->getDatabase()->selectOne($sql, [
                'category_id' => $id,
                'tenant_id' => $tenantId
            ]);
            
            if (!$category) {
                throw new \Exception('Category not found or access denied');
            }
            
            // Check if event is live/ongoing - prevent deletion
            $now = time();
            $startTime = strtotime($category['start_date']);
            $endTime = strtotime($category['end_date']);
            $isLive = ($category['event_status'] === 'active' && $now >= $startTime && $now <= $endTime);
            
            if ($isLive) {
                throw new \Exception('Cannot delete category while event is live/ongoing');
            }
            
            // Start transaction
            $this->categoryModel->getDatabase()->beginTransaction();
            
            // Get all contestants in this category
            $contestantsSql = "
                SELECT c.id
                FROM contestants c
                INNER JOIN contestant_categories cc ON c.id = cc.contestant_id
                WHERE cc.category_id = :category_id
            ";
            
            $contestants = $this->categoryModel->getDatabase()->select($contestantsSql, ['category_id' => $id]);
            
            // Delete all votes for contestants in this category
            foreach ($contestants as $contestant) {
                $this->categoryModel->getDatabase()->delete('votes', 'contestant_id = :contestant_id', ['contestant_id' => $contestant['id']]);
            }
            
            // Delete contestant-category assignments
            $this->categoryModel->getDatabase()->delete('contestant_categories', 'category_id = :category_id', ['category_id' => $id]);
            
            // Delete all contestants in this category
            foreach ($contestants as $contestant) {
                $this->categoryModel->getDatabase()->delete('contestants', 'id = :contestant_id', ['contestant_id' => $contestant['id']]);
            }
            
            // Finally delete the category
            $categoryModel = new \SmartCast\Models\Category();
            $categoryModel->delete($id);
            
            // Commit transaction
            $this->categoryModel->getDatabase()->commit();
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => 'Category and all its contestants deleted successfully',
                    'deleted_contestants' => count($contestants)
                ]);
                return;
            }
            
            $this->redirect(ORGANIZER_URL . '/categories', 'Category and ' . count($contestants) . ' contestants deleted successfully', 'success');
            
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->categoryModel->getDatabase()->rollback();
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                return;
            }
            
            $this->redirect(ORGANIZER_URL . '/categories', 'Failed to delete category: ' . $e->getMessage(), 'error');
        }
    }
    
    public function votingAnalytics()
    {
        $tenantId = $this->session->getTenantId();
        $eventId = $_GET['event'] ?? null;
        $timePeriod = $_GET['period'] ?? '7d';
        
        // Get all events for dropdown
        $events = $this->eventModel->getEventsByTenant($tenantId);
        $categories = $this->getCategoriesByTenant($tenantId);
        
        // Get voting analytics data
        $votingStats = $this->getVotingAnalytics($tenantId, $eventId, $timePeriod);
        $topContestants = $this->getTopContestants($tenantId, 20, $eventId);
        $votingTrends = $this->getVotingTrendsAnalytics($tenantId, $eventId, $timePeriod);
        $performanceInsights = $this->getPerformanceInsights($tenantId, $eventId, $timePeriod);
        
        // Handle AJAX requests
        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'votingStats' => $votingStats,
                'topContestants' => $topContestants,
                'votingTrends' => $votingTrends,
                'performanceInsights' => $performanceInsights
            ]);
            return;
        }
        
        $content = $this->renderView('organizer/voting/analytics', [
            'events' => $events,
            'categories' => $categories,
            'selectedEventId' => $eventId,
            'selectedPeriod' => $timePeriod,
            'votingStats' => $votingStats,
            'topContestants' => $topContestants,
            'votingTrends' => $votingTrends,
            'performanceInsights' => $performanceInsights,
            'title' => 'Voting Analytics'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Voting Analytics',
            'breadcrumbs' => [
                ['title' => 'Voting', 'url' => ORGANIZER_URL . '/voting'],
                ['title' => 'Analytics']
            ]
        ]);
    }
    
    public function votingReceipts()
    {
        $tenantId = $this->session->getTenantId();
        $eventId = $_GET['event'] ?? null;
        $status = $_GET['status'] ?? null;
        $dateRange = $_GET['range'] ?? 'week';
        $search = $_GET['search'] ?? null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get all events for dropdown
        $events = $this->eventModel->getEventsByTenant($tenantId);
        
        // Build filters
        $filters = ['tenant_id' => $tenantId];
        $whereConditions = ['e.tenant_id = :tenant_id'];
        
        if ($eventId) {
            $whereConditions[] = 'e.id = :event_id';
            $filters['event_id'] = $eventId;
        }
        
        if ($status) {
            $whereConditions[] = 't.status = :status';
            $filters['status'] = $status;
        }
        
        if ($search) {
            $whereConditions[] = '(t.id LIKE :search OR t.msisdn LIKE :search OR c.name LIKE :search)';
            $filters['search'] = "%$search%";
        }
        
        // Date range filter
        $dateFilter = $this->getDateRangeFilter($dateRange);
        if ($dateFilter) {
            $whereConditions[] = $dateFilter;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Get receipts with pagination
        $sql = "
            SELECT t.*, 
                   e.name as event_name,
                   e.code as event_code,
                   c.name as contestant_name,
                   c.contestant_code,
                   SUM(v.quantity) as votes_purchased,
                   t.msisdn as voter_phone
            FROM transactions t
            INNER JOIN events e ON t.event_id = e.id
            LEFT JOIN votes v ON t.id = v.transaction_id
            LEFT JOIN contestants c ON v.contestant_id = c.id
            WHERE $whereClause
            GROUP BY t.id
            ORDER BY t.created_at DESC
            LIMIT $limit OFFSET $offset
        ";
        
        $receipts = $this->transactionModel->getDatabase()->select($sql, $filters);
        
        // Get total count for pagination
        $countSql = "
            SELECT COUNT(DISTINCT t.id) as total
            FROM transactions t
            INNER JOIN events e ON t.event_id = e.id
            LEFT JOIN votes v ON t.id = v.transaction_id
            LEFT JOIN contestants c ON v.contestant_id = c.id
            WHERE $whereClause
        ";
        
        $totalResult = $this->transactionModel->getDatabase()->selectOne($countSql, $filters);
        $totalReceipts = $totalResult['total'] ?? 0;
        $totalPages = ceil($totalReceipts / $limit);
        
        // Get receipt statistics
        $receiptStats = $this->getReceiptStats($tenantId, $eventId, $dateRange);
        
        $content = $this->renderView('organizer/voting/receipts', [
            'receipts' => $receipts,
            'events' => $events,
            'receiptStats' => $receiptStats,
            'selectedEventId' => $eventId,
            'selectedStatus' => $status,
            'selectedRange' => $dateRange,
            'searchQuery' => $search,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalReceipts' => $totalReceipts,
            'title' => 'Voting Receipts'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Voting Receipts',
            'breadcrumbs' => [
                ['title' => 'Voting', 'url' => '#'],
                ['title' => 'Receipts']
            ]
        ]);
    }
    
    public function transactions()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get transactions with related data (events, contestants, bundles, actual votes)
        $sql = "
            SELECT 
                t.*,
                e.name as event_name,
                e.code as event_code,
                c.name as contestant_name,
                c.contestant_code,
                vb.name as bundle_name,
                vb.votes as bundle_vote_count,
                vb.price as bundle_price,
                cat.name as category_name,
                COALESCE(v.quantity, vb.votes, 1) as actual_votes
            FROM transactions t
            LEFT JOIN events e ON t.event_id = e.id
            LEFT JOIN contestants c ON t.contestant_id = c.id
            LEFT JOIN vote_bundles vb ON t.bundle_id = vb.id
            LEFT JOIN categories cat ON t.category_id = cat.id
            LEFT JOIN votes v ON t.id = v.transaction_id
            WHERE t.tenant_id = :tenant_id
            ORDER BY t.created_at DESC
            LIMIT 100
        ";
        
        $transactions = $this->db->select($sql, ['tenant_id' => $tenantId]);
        
        // Calculate statistics from the transactions
        $stats = [
            'total_transactions' => count($transactions),
            'successful_transactions' => 0,
            'failed_transactions' => 0,
            'pending_transactions' => 0,
            'total_volume' => 0,
            'successful_volume' => 0,
            'total_votes' => 0
        ];
        
        foreach ($transactions as $transaction) {
            $amount = floatval($transaction['amount'] ?? 0);
            $votes = intval($transaction['actual_votes'] ?? $transaction['bundle_vote_count'] ?? 0);
            $status = strtolower($transaction['status'] ?? '');
            
            // Always count in total volume for display purposes
            $stats['total_volume'] += $amount;
            
            switch ($status) {
                case 'success':
                case 'completed':
                    $stats['successful_transactions']++;
                    $stats['successful_volume'] += $amount;
                    $stats['total_votes'] += $votes; // Only count votes from successful transactions
                    break;
                case 'failed':
                case 'error':
                    $stats['failed_transactions']++;
                    break;
                case 'pending':
                case 'processing':
                    $stats['pending_transactions']++;
                    break;
            }
        }
        
        // Get events for filter dropdown
        $events = $this->eventModel->getEventsByTenant($tenantId);
        
        // Pagination info
        $pagination = [
            'current_page' => 1,
            'total_pages' => 1,
            'per_page' => 100,
            'total_records' => count($transactions)
        ];
        
        $content = $this->renderView('organizer/financial/transactions', [
            'transactions' => $transactions,
            'stats' => $stats,
            'events' => $events,
            'pagination' => $pagination,
            'title' => 'Transactions'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Transactions',
            'breadcrumbs' => [
                ['title' => 'Financial', 'url' => ORGANIZER_URL . '/financial'],
                ['title' => 'Transactions']
            ]
        ]);
    }
    
    public function payouts()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get balance information
        $balance = $this->balanceModel->getBalance($tenantId);
        
        // Get payout history
        $payoutModel = new \SmartCast\Models\Payout();
        $payouts = $payoutModel->getPayoutsByTenant($tenantId);
        
        // Get payout statistics
        $payoutStats = $payoutModel->getPayoutStats($tenantId);
        
        // Calculate stats from payout data
        $stats = [
            'total_payouts' => 0,
            'pending_amount' => 0,
            'completed_amount' => 0,
            'failed_amount' => 0,
            'total_count' => 0
        ];
        
        foreach ($payoutStats as $stat) {
            $stats['total_count'] += $stat['count'];
            
            switch ($stat['status']) {
                case 'success':
                    $stats['completed_amount'] += $stat['total_amount'];
                    break;
                case 'queued':
                case 'processing':
                    $stats['pending_amount'] += $stat['total_amount'];
                    break;
                case 'failed':
                    $stats['failed_amount'] += $stat['total_amount'];
                    break;
            }
        }
        
        $stats['total_payouts'] = $stats['completed_amount'] + $stats['pending_amount'] + $stats['failed_amount'];
        
        $content = $this->renderView('organizer/financial/payouts', [
            'balance' => $balance,
            'payouts' => $payouts,
            'stats' => $stats,
            'title' => 'Payouts'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Payouts',
            'breadcrumbs' => [
                ['title' => 'Financial', 'url' => ORGANIZER_URL . '/financial'],
                ['title' => 'Payouts']
            ]
        ]);
    }
    
    public function bundles()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get actual bundles from database
        $bundleModel = new \SmartCast\Models\VoteBundle();
        $eventModel = new \SmartCast\Models\Event();
        
        // Get all events for this tenant
        $events = $eventModel->getEventsByTenant($tenantId);
        
        // Get bundles for each event
        $eventBundles = [];
        foreach ($events as $event) {
            $bundles = $bundleModel->getBundlesByEvent($event['id']);
            if (!empty($bundles)) {
                $eventBundles[$event['id']] = [
                    'event' => $event,
                    'bundles' => $bundles
                ];
            }
        }
        
        // Get bundle statistics with real transaction data
        $transactionModel = new \SmartCast\Models\Transaction();
        $allBundles = $bundleModel->getBundlesByTenant($tenantId);
        
        // Calculate real statistics
        $bundlesSold = 0;
        $totalRevenue = 0;
        
        foreach ($allBundles as $bundle) {
            $bundleTransactions = $transactionModel->findAll([
                'bundle_id' => $bundle['id'],
                'status' => 'success'
            ]);
            
            $bundlesSold += count($bundleTransactions);
            $totalRevenue += array_sum(array_column($bundleTransactions, 'amount'));
        }
        
        $bundleStats = [
            'total_bundles' => count($allBundles),
            'active_bundles' => count(array_filter($allBundles, function($b) { return $b['active']; })),
            'bundles_sold' => $bundlesSold,
            'total_revenue' => $totalRevenue
        ];
        
        $content = $this->renderView('organizer/financial/bundles', [
            'bundleStats' => $bundleStats,
            'eventBundles' => $eventBundles,
            'events' => $events,
            'title' => 'Vote Bundles'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Vote Bundles',
            'breadcrumbs' => [
                ['title' => 'Financial', 'url' => ORGANIZER_URL . '/financial'],
                ['title' => 'Bundles']
            ]
        ]);
    }
    
    public function createBundle()
    {
        $tenantId = $this->session->getTenantId();
        $data = $_POST;
        
        // Validate input
        $errors = $this->validateInput($data, [
            'event_id' => ['required' => true, 'numeric' => true],
            'name' => ['required' => true, 'min' => 3],
            'votes' => ['required' => true, 'numeric' => true, 'min' => 1],
            'price' => ['required' => true, 'numeric' => true, 'min' => 0.01]
        ]);
        
        if (!empty($errors)) {
            $this->redirect(ORGANIZER_URL . '/financial/bundles', 'Please fix the validation errors', 'error');
            return;
        }
        
        // Verify event belongs to tenant
        $eventModel = new \SmartCast\Models\Event();
        $event = $eventModel->find($data['event_id']);
        
        if (!$event || $event['tenant_id'] != $tenantId) {
            $this->redirect(ORGANIZER_URL . '/financial/bundles', 'Invalid event selected', 'error');
            return;
        }
        
        // Create bundle
        $bundleModel = new \SmartCast\Models\VoteBundle();
        $bundleId = $bundleModel->create([
            'event_id' => $data['event_id'],
            'name' => $data['name'],
            'votes' => intval($data['votes']),
            'price' => floatval($data['price']),
            'active' => isset($data['active']) ? 1 : 0
        ]);
        
        if ($bundleId) {
            $this->redirect(ORGANIZER_URL . '/financial/bundles', 'Vote bundle created successfully', 'success');
        } else {
            $this->redirect(ORGANIZER_URL . '/financial/bundles', 'Failed to create vote bundle', 'error');
        }
    }
    
    public function updateBundle($bundleId)
    {
        $tenantId = $this->session->getTenantId();
        $data = $_POST;
        
        // Get bundle and verify ownership
        $bundleModel = new \SmartCast\Models\VoteBundle();
        $eventModel = new \SmartCast\Models\Event();
        
        $bundle = $bundleModel->find($bundleId);
        if (!$bundle) {
            $this->redirect(ORGANIZER_URL . '/financial/bundles', 'Bundle not found', 'error');
            return;
        }
        
        $event = $eventModel->find($bundle['event_id']);
        if (!$event || $event['tenant_id'] != $tenantId) {
            $this->redirect(ORGANIZER_URL . '/financial/bundles', 'Access denied', 'error');
            return;
        }
        
        // Handle toggle active/inactive
        if (isset($data['active'])) {
            $updated = $bundleModel->update($bundleId, [
                'active' => intval($data['active'])
            ]);
            
            $status = $data['active'] ? 'activated' : 'deactivated';
            if ($updated) {
                $this->redirect(ORGANIZER_URL . '/financial/bundles', "Bundle {$status} successfully", 'success');
            } else {
                $this->redirect(ORGANIZER_URL . '/financial/bundles', "Failed to {$status} bundle", 'error');
            }
            return;
        }
        
        // Handle full update (if implementing edit functionality later)
        if (isset($data['name']) && isset($data['votes']) && isset($data['price'])) {
            $updated = $bundleModel->update($bundleId, [
                'name' => $data['name'],
                'votes' => intval($data['votes']),
                'price' => floatval($data['price']),
                'active' => isset($data['active']) ? 1 : 0
            ]);
            
            if ($updated) {
                $this->redirect(ORGANIZER_URL . '/financial/bundles', 'Bundle updated successfully', 'success');
            } else {
                $this->redirect(ORGANIZER_URL . '/financial/bundles', 'Failed to update bundle', 'error');
            }
        }
    }
    
    public function deleteBundle($bundleId)
    {
        $tenantId = $this->session->getTenantId();
        
        // Get bundle and verify ownership
        $bundleModel = new \SmartCast\Models\VoteBundle();
        $eventModel = new \SmartCast\Models\Event();
        
        $bundle = $bundleModel->find($bundleId);
        if (!$bundle) {
            $this->redirect(ORGANIZER_URL . '/financial/bundles', 'Bundle not found', 'error');
            return;
        }
        
        $event = $eventModel->find($bundle['event_id']);
        if (!$event || $event['tenant_id'] != $tenantId) {
            $this->redirect(ORGANIZER_URL . '/financial/bundles', 'Access denied', 'error');
            return;
        }
        
        // Check if bundle has been used in transactions
        $transactionModel = new \SmartCast\Models\Transaction();
        $usageCount = $transactionModel->count(['bundle_id' => $bundleId]);
        
        if ($usageCount > 0) {
            // Don't delete, just deactivate
            $bundleModel->update($bundleId, ['active' => 0]);
            $this->redirect(ORGANIZER_URL . '/financial/bundles', 'Bundle deactivated (cannot delete - has transactions)', 'warning');
        } else {
            // Safe to delete
            $deleted = $bundleModel->delete($bundleId);
            if ($deleted) {
                $this->redirect(ORGANIZER_URL . '/financial/bundles', 'Bundle deleted successfully', 'success');
            } else {
                $this->redirect(ORGANIZER_URL . '/financial/bundles', 'Failed to delete bundle', 'error');
            }
        }
    }
    
    public function coupons()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get coupon statistics (this would need a coupons table in real implementation)
        $couponStats = [
            'total_coupons' => 12,
            'active_coupons' => 8,
            'total_redemptions' => 156,
            'total_savings' => 2340.50
        ];
        
        $content = $this->renderView('organizer/marketing/coupons', [
            'couponStats' => $couponStats,
            'title' => 'Coupons'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Coupons',
            'breadcrumbs' => [
                ['title' => 'Marketing', 'url' => '#'],
                ['title' => 'Coupons']
            ]
        ]);
    }
    
    public function referrals()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get referral statistics (this would need a referrals table in real implementation)
        $referralStats = [
            'total_referrals' => 156,
            'successful_referrals' => 89,
            'earned_rewards' => 445.00,
            'this_month' => 23
        ];
        
        $content = $this->renderView('organizer/marketing/referrals', [
            'referralStats' => $referralStats,
            'title' => 'Referrals'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Referrals',
            'breadcrumbs' => [
                ['title' => 'Marketing', 'url' => '#'],
                ['title' => 'Referrals']
            ]
        ]);
    }
    
    public function campaigns()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get campaign statistics (this would need a campaigns table in real implementation)
        $campaignStats = [
            'active_campaigns' => 12,
            'emails_sent' => 8456,
            'open_rate' => 24.5,
            'click_rate' => 8.7
        ];
        
        $content = $this->renderView('organizer/marketing/campaigns', [
            'campaignStats' => $campaignStats,
            'title' => 'Campaigns'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Campaigns',
            'breadcrumbs' => [
                ['title' => 'Marketing', 'url' => '#'],
                ['title' => 'Campaigns']
            ]
        ]);
    }
    
    public function reports()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get comprehensive report data
        $reportData = [
            'events_summary' => $this->getEventsSummary($tenantId),
            'voting_summary' => $this->getVotingSummary($tenantId),
            'financial_summary' => $this->getFinancialSummary($tenantId),
            'top_events' => $this->getTopPerformingEvents($tenantId, 10),
            'revenue_trends' => $this->getRevenueTrendsForReports($tenantId, 30), // Last 30 days
            'performance_metrics' => $this->getPerformanceMetrics($tenantId)
        ];
        
        $content = $this->renderView('organizer/reports/index', [
            'reportData' => $reportData,
            'title' => 'Reports & Analytics'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Reports & Analytics'
        ]);
    }
    
    private function getEventsSummary($tenantId)
    {
        return [
            'total' => $this->eventModel->count(['tenant_id' => $tenantId]),
            'active' => $this->eventModel->count(['tenant_id' => $tenantId, 'status' => 'active']),
            'completed' => $this->eventModel->count(['tenant_id' => $tenantId, 'status' => 'completed']),
            'draft' => $this->eventModel->count(['tenant_id' => $tenantId, 'status' => 'draft'])
        ];
    }
    
    private function getVotingSummary($tenantId)
    {
        try {
            // First, get basic vote statistics
            $sql = "
                SELECT 
                    COUNT(v.id) as total_votes,
                    SUM(v.quantity) as total_quantity
                FROM votes v
                INNER JOIN events e ON v.event_id = e.id
                WHERE e.tenant_id = :tenant_id
            ";
            
            $result = $this->eventModel->getDatabase()->selectOne($sql, ['tenant_id' => $tenantId]);
            
            // Try to get unique voters from transactions table if it has the right columns
            $uniqueVotersQuery = "
                SELECT COUNT(DISTINCT t.id) as unique_voters
                FROM transactions t
                INNER JOIN events e ON t.event_id = e.id
                WHERE e.tenant_id = :tenant_id
                AND t.status = 'success'
            ";
            
            $uniqueVotersResult = $this->eventModel->getDatabase()->selectOne($uniqueVotersQuery, ['tenant_id' => $tenantId]);
            
            return [
                'total_votes' => $result['total_votes'] ?? 0,
                'total_quantity' => $result['total_quantity'] ?? 0,
                'unique_voters' => $uniqueVotersResult['unique_voters'] ?? 0
            ];
            
        } catch (\Exception $e) {
            // If queries fail, return default values
            return [
                'total_votes' => 0,
                'total_quantity' => 0,
                'unique_voters' => 0
            ];
        }
    }
    
    private function getFinancialSummary($tenantId)
    {
        // Get actual revenue from revenue_transactions (tenant's share after fees)
        $sql = "
            SELECT 
                COUNT(DISTINCT rt.transaction_id) as total_transactions,
                COALESCE(SUM(rt.net_tenant_amount), 0) as total_revenue,
                COALESCE(AVG(rt.net_tenant_amount), 0) as avg_transaction,
                COALESCE(SUM(rt.gross_amount), 0) as gross_revenue,
                COALESCE(SUM(rt.platform_fee), 0) as total_fees
            FROM revenue_transactions rt
            WHERE rt.tenant_id = :tenant_id
            AND rt.distribution_status = 'completed'
        ";
        
        $result = $this->eventModel->getDatabase()->selectOne($sql, ['tenant_id' => $tenantId]);
        
        // Calculate growth percentage (this month vs last month)
        $thisMonthStart = date('Y-m-01 00:00:00');
        $lastMonthStart = date('Y-m-01 00:00:00', strtotime('first day of last month'));
        $lastMonthEnd = date('Y-m-t 23:59:59', strtotime('last day of last month'));
        
        $growthSql = "
            SELECT 
                COALESCE(SUM(CASE WHEN rt.created_at >= :this_month THEN rt.net_tenant_amount END), 0) as this_month,
                COALESCE(SUM(CASE WHEN rt.created_at BETWEEN :last_month_start AND :last_month_end THEN rt.net_tenant_amount END), 0) as last_month
            FROM revenue_transactions rt
            WHERE rt.tenant_id = :tenant_id
            AND rt.distribution_status = 'completed'
        ";
        
        $growthResult = $this->eventModel->getDatabase()->selectOne($growthSql, [
            'tenant_id' => $tenantId,
            'this_month' => $thisMonthStart,
            'last_month_start' => $lastMonthStart,
            'last_month_end' => $lastMonthEnd
        ]);
        
        $thisMonth = $growthResult['this_month'] ?? 0;
        $lastMonth = $growthResult['last_month'] ?? 0;
        
        $growthPercentage = 0;
        if ($lastMonth > 0) {
            $growthPercentage = (($thisMonth - $lastMonth) / $lastMonth) * 100;
        }
        
        $result['growth_percentage'] = $growthPercentage;
        $result['this_month_revenue'] = $thisMonth;
        $result['last_month_revenue'] = $lastMonth;
        
        return $result;
    }
    
    private function getTopPerformingEvents($tenantId, $limit = 10)
    {
        $sql = "
            SELECT 
                e.id,
                e.name,
                e.created_at,
                e.status,
                COALESCE(vote_data.vote_count, 0) as vote_count,
                COALESCE(vote_data.total_votes, 0) as total_votes,
                COALESCE(contestant_data.contestant_count, 0) as contestant_count,
                COALESCE(revenue_data.revenue, 0) as revenue,
                COALESCE(revenue_data.gross_revenue, 0) as gross_revenue
            FROM events e
            LEFT JOIN (
                SELECT 
                    event_id,
                    COUNT(DISTINCT id) as vote_count,
                    SUM(quantity) as total_votes
                FROM votes
                GROUP BY event_id
            ) vote_data ON e.id = vote_data.event_id
            LEFT JOIN (
                SELECT 
                    event_id,
                    COUNT(DISTINCT id) as contestant_count
                FROM contestants
                GROUP BY event_id
            ) contestant_data ON e.id = contestant_data.event_id
            LEFT JOIN (
                SELECT 
                    event_id,
                    SUM(net_tenant_amount) as revenue,
                    SUM(gross_amount) as gross_revenue
                FROM revenue_transactions
                WHERE distribution_status = 'completed'
                GROUP BY event_id
            ) revenue_data ON e.id = revenue_data.event_id
            WHERE e.tenant_id = :tenant_id
            ORDER BY revenue DESC, total_votes DESC
            LIMIT :limit
        ";
        
        return $this->eventModel->getDatabase()->select($sql, [
            'tenant_id' => $tenantId,
            'limit' => $limit
        ]);
    }
    
    private function getRevenueTrendsForReports($tenantId, $days = 30)
    {
        // Get daily revenue for the last N days
        $sql = "
            SELECT 
                DATE(rt.created_at) as date,
                COALESCE(SUM(rt.net_tenant_amount), 0) as revenue,
                COUNT(DISTINCT rt.transaction_id) as transactions,
                COALESCE(SUM(v.quantity), 0) as votes
            FROM revenue_transactions rt
            LEFT JOIN transactions t ON rt.transaction_id = t.id
            LEFT JOIN votes v ON t.id = v.transaction_id
            WHERE rt.tenant_id = :tenant_id
            AND rt.distribution_status = 'completed'
            AND rt.created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
            GROUP BY DATE(rt.created_at)
            ORDER BY date ASC
        ";
        
        $results = $this->eventModel->getDatabase()->select($sql, [
            'tenant_id' => $tenantId,
            'days' => $days
        ]);
        
        // Fill in missing days with zero values
        $trends = [];
        $labels = [];
        $revenueData = [];
        $votesData = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $label = date('M j', strtotime("-$i days"));
            $trends[$date] = ['revenue' => 0, 'votes' => 0];
            $labels[] = $label;
        }
        
        // Fill in actual data
        foreach ($results as $row) {
            if (isset($trends[$row['date']])) {
                $trends[$row['date']] = [
                    'revenue' => floatval($row['revenue']),
                    'votes' => intval($row['votes'])
                ];
            }
        }
        
        // Convert to arrays for chart
        foreach ($trends as $data) {
            $revenueData[] = $data['revenue'];
            $votesData[] = $data['votes'];
        }
        
        return [
            'labels' => $labels,
            'revenue' => $revenueData,
            'votes' => $votesData
        ];
    }
    
    private function getPerformanceMetrics($tenantId)
    {
        // Calculate real performance metrics
        
        // 1. Payment Success Rate
        $transactionStats = $this->eventModel->getDatabase()->selectOne("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN t.status = 'success' THEN 1 ELSE 0 END) as successful
            FROM transactions t
            INNER JOIN events e ON t.event_id = e.id
            WHERE e.tenant_id = :tenant_id
        ", ['tenant_id' => $tenantId]);
        
        $successRate = 0;
        if ($transactionStats['total'] > 0) {
            $successRate = ($transactionStats['successful'] / $transactionStats['total']) * 100;
        }
        
        // 2. Average votes per transaction
        $votingStats = $this->eventModel->getDatabase()->selectOne("
            SELECT 
                COUNT(DISTINCT v.transaction_id) as transactions_with_votes,
                COALESCE(SUM(v.quantity), 0) as total_votes
            FROM votes v
            INNER JOIN events e ON v.event_id = e.id
            WHERE e.tenant_id = :tenant_id
        ", ['tenant_id' => $tenantId]);
        
        $avgVotesPerTransaction = 0;
        if ($votingStats['transactions_with_votes'] > 0) {
            $avgVotesPerTransaction = $votingStats['total_votes'] / $votingStats['transactions_with_votes'];
        }
        
        // 3. Average revenue per transaction
        $revenuePerTransaction = 0;
        if ($transactionStats['successful'] > 0) {
            $revenueStats = $this->eventModel->getDatabase()->selectOne("
                SELECT COALESCE(SUM(net_tenant_amount), 0) as total_revenue
                FROM revenue_transactions
                WHERE tenant_id = :tenant_id
                AND distribution_status = 'completed'
            ", ['tenant_id' => $tenantId]);
            
            $revenuePerTransaction = $revenueStats['total_revenue'] / $transactionStats['successful'];
        }
        
        return [
            'success_rate' => $successRate,
            'avg_votes_per_transaction' => $avgVotesPerTransaction,
            'avg_revenue_per_transaction' => $revenuePerTransaction,
            'total_transactions' => $transactionStats['total'],
            'successful_transactions' => $transactionStats['successful'],
            'failed_transactions' => $transactionStats['total'] - $transactionStats['successful']
        ];
    }
    
    private function getLiveVotingStats($tenantId, $eventId = null, $categoryId = null)
    {
        try {
            $eventFilter = $eventId ? "AND e.id = :event_id" : "";
            $categoryFilter = $categoryId ? "AND v.category_id = :category_id" : "";
            $params = ['tenant_id' => $tenantId];
            if ($eventId) {
                $params['event_id'] = $eventId;
            }
            if ($categoryId) {
                $params['category_id'] = $categoryId;
            }
            
            // Get total votes and revenue (showing tenant earnings, not gross revenue)
            $totalSql = "
                SELECT 
                    COALESCE(SUM(v.quantity), 0) as total_votes,
                    COUNT(DISTINCT v.transaction_id) as unique_voters,
                    COALESCE(SUM(
                        CASE WHEN t.status = 'success' THEN 
                            t.amount - COALESCE(rs.amount, 
                                t.amount * (COALESCE((
                                    SELECT fr.percentage_rate 
                                    FROM fee_rules fr 
                                    WHERE fr.tenant_id = e.tenant_id 
                                    AND fr.active = 1
                                    ORDER BY fr.created_at DESC 
                                    LIMIT 1
                                ), 12.0) / 100)
                            )
                        ELSE 0 
                        END
                    ), 0) as total_revenue,
                    COALESCE(SUM(
                        CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END
                    ), 0) as gross_revenue
                FROM votes v
                INNER JOIN contestants c ON v.contestant_id = c.id
                INNER JOIN events e ON c.event_id = e.id
                LEFT JOIN transactions t ON v.transaction_id = t.id
                LEFT JOIN revenue_shares rs ON t.id = rs.transaction_id
                WHERE e.tenant_id = :tenant_id
                AND e.status = 'active'
                $eventFilter
                $categoryFilter
            ";
            
            $totalStats = $this->eventModel->getDatabase()->selectOne($totalSql, $params);
            
            // Get votes from last hour
            $hourSql = "
                SELECT COALESCE(SUM(v.quantity), 0) as votes_this_hour
                FROM votes v
                INNER JOIN contestants c ON v.contestant_id = c.id
                INNER JOIN events e ON c.event_id = e.id
                WHERE e.tenant_id = :tenant_id
                AND e.status = 'active'
                AND v.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                $eventFilter
                $categoryFilter
            ";
            
            $hourStats = $this->eventModel->getDatabase()->selectOne($hourSql, $params);
            
            // Get votes per minute (last 10 minutes)
            $minuteSql = "
                SELECT COALESCE(SUM(v.quantity), 0) as votes_last_10min
                FROM votes v
                INNER JOIN contestants c ON v.contestant_id = c.id
                INNER JOIN events e ON c.event_id = e.id
                WHERE e.tenant_id = :tenant_id
                AND e.status = 'active'
                AND v.created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                $eventFilter
                $categoryFilter
            ";
            
            $minuteStats = $this->eventModel->getDatabase()->selectOne($minuteSql, $params);
            $votesPerMinute = ($minuteStats['votes_last_10min'] ?? 0) / 10;
            
            return [
                'total_votes' => $totalStats['total_votes'] ?? 0,
                'votes_this_hour' => $hourStats['votes_this_hour'] ?? 0,
                'total_revenue' => $totalStats['total_revenue'] ?? 0,
                'unique_voters' => $totalStats['unique_voters'] ?? 0,
                'votes_per_minute' => round($votesPerMinute, 1)
            ];
        } catch (\Exception $e) {
            return [
                'total_votes' => 0,
                'votes_this_hour' => 0,
                'total_revenue' => 0,
                'unique_voters' => 0,
                'votes_per_minute' => 0
            ];
        }
    }
    
    private function getVotingAnalytics($tenantId, $eventId = null, $timePeriod = '7d')
    {
        try {
            $eventFilter = $eventId ? "AND e.id = :event_id" : "";
            $timeFilter = $this->getTimeFilter($timePeriod);
            
            $params = ['tenant_id' => $tenantId];
            if ($eventId) {
                $params['event_id'] = $eventId;
            }
            
            $sql = "
                SELECT 
                    COALESCE(SUM(v.quantity), 0) as total_votes,
                    COUNT(DISTINCT v.transaction_id) as unique_voters,
                    COUNT(DISTINCT e.id) as events_with_votes,
                    AVG(v.quantity) as avg_votes_per_transaction,
                    SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END) as total_revenue
                FROM votes v
                INNER JOIN contestants c ON v.contestant_id = c.id
                INNER JOIN events e ON c.event_id = e.id
                LEFT JOIN transactions t ON v.transaction_id = t.id
                WHERE e.tenant_id = :tenant_id
                $eventFilter
                $timeFilter
            ";
            
            $result = $this->eventModel->getDatabase()->selectOne($sql, $params);
            
            return [
                'total_votes' => $result['total_votes'] ?? 0,
                'unique_voters' => $result['unique_voters'] ?? 0,
                'events_with_votes' => $result['events_with_votes'] ?? 0,
                'avg_votes_per_transaction' => round($result['avg_votes_per_transaction'] ?? 0, 1),
                'total_revenue' => $result['total_revenue'] ?? 0
            ];
        } catch (\Exception $e) {
            return [
                'total_votes' => 0,
                'unique_voters' => 0,
                'events_with_votes' => 0,
                'avg_votes_per_transaction' => 0,
                'total_revenue' => 0
            ];
        }
    }
    
    private function getTopContestants($tenantId, $limit = 10, $eventId = null, $categoryId = null)
    {
        $eventFilter = $eventId ? "AND e.id = :event_id" : "";
        $params = ['tenant_id' => $tenantId, 'limit' => $limit];
        if ($eventId) {
            $params['event_id'] = $eventId;
        }

        if ($categoryId) {
            // When category is selected, only show contestants in that category with their category-specific votes
            $params['category_id'] = $categoryId;
            $sql = "
                SELECT
                    c.id,
                    c.name,
                    c.contestant_code,
                    c.image_url,
                    e.name as event_name,
                    e.id as event_id,
                    cat.name as category_name,
                    COUNT(DISTINCT v.id) as vote_count,
                    COALESCE(SUM(v.quantity), 0) as total_votes,
                    SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END) as revenue
                FROM contestants c
                INNER JOIN events e ON c.event_id = e.id
                INNER JOIN contestant_categories cc ON c.id = cc.contestant_id
                INNER JOIN categories cat ON cc.category_id = cat.id AND cat.id = :category_id
                LEFT JOIN votes v ON c.id = v.contestant_id AND v.category_id = cat.id
                LEFT JOIN transactions t ON v.transaction_id = t.id AND t.status = 'success'
                WHERE e.tenant_id = :tenant_id
                AND c.active = 1
                $eventFilter
                GROUP BY c.id, c.name, c.contestant_code, c.image_url, e.name, e.id, cat.name
                ORDER BY total_votes DESC, c.name ASC
                LIMIT :limit
            ";
        } else {
            // When no category selected, show all contestants with their total votes across all categories
            $sql = "
                SELECT
                    c.id,
                    c.name,
                    c.contestant_code,
                    c.image_url,
                    e.name as event_name,
                    e.id as event_id,
                    GROUP_CONCAT(DISTINCT cat.name SEPARATOR ', ') as category_name,
                    vote_data.vote_count,
                    vote_data.total_votes,
                    vote_data.revenue
                FROM contestants c
                INNER JOIN events e ON c.event_id = e.id
                LEFT JOIN contestant_categories cc ON c.id = cc.contestant_id
                LEFT JOIN categories cat ON cc.category_id = cat.id
                LEFT JOIN (
                    SELECT 
                        v.contestant_id,
                        COUNT(v.id) as vote_count,
                        COALESCE(SUM(v.quantity), 0) as total_votes,
                        SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END) as revenue
                    FROM votes v
                    LEFT JOIN transactions t ON v.transaction_id = t.id
                    GROUP BY v.contestant_id
                ) vote_data ON c.id = vote_data.contestant_id
                WHERE e.tenant_id = :tenant_id
                AND c.active = 1
                $eventFilter
                GROUP BY c.id, c.name, c.contestant_code, c.image_url, e.name, e.id, vote_data.vote_count, vote_data.total_votes, vote_data.revenue
                ORDER BY vote_data.total_votes DESC, c.name ASC
                LIMIT :limit
            ";
        }

        return $this->eventModel->getDatabase()->select($sql, $params);
    }
    
    private function getVotingTrends($tenantId, $eventId = null, $categoryId = null, $hours = 24)
    {
        try {
            $eventFilter = $eventId ? "AND e.id = :event_id" : "";
            $categoryFilter = $categoryId ? "AND v.category_id = :category_id" : "";
            $params = ['tenant_id' => $tenantId];
            if ($eventId) {
                $params['event_id'] = $eventId;
            }
            if ($categoryId) {
                $params['category_id'] = $categoryId;
            }
            
            $sql = "
                SELECT 
                    HOUR(v.created_at) as vote_hour,
                    c.name as contestant_name,
                    c.id as contestant_id,
                    SUM(v.quantity) as hourly_votes
                FROM votes v
                INNER JOIN contestants c ON v.contestant_id = c.id
                INNER JOIN events e ON c.event_id = e.id
                WHERE e.tenant_id = :tenant_id
                AND v.created_at >= DATE_SUB(NOW(), INTERVAL $hours HOUR)
                $eventFilter
                $categoryFilter
                GROUP BY HOUR(v.created_at), c.id, c.name
                ORDER BY vote_hour ASC, hourly_votes DESC
            ";
            
            $results = $this->eventModel->getDatabase()->select($sql, $params);
            
            // Format data for Chart.js
            $chartData = [];
            $contestants = [];
            
            foreach ($results as $row) {
                $contestants[$row['contestant_id']] = $row['contestant_name'];
                if (!isset($chartData[$row['contestant_id']])) {
                    $chartData[$row['contestant_id']] = array_fill(0, 24, 0);
                }
                $chartData[$row['contestant_id']][$row['vote_hour']] = (int)$row['hourly_votes'];
            }
            
            return [
                'contestants' => $contestants,
                'data' => $chartData,
                'hours' => range(0, 23)
            ];
        } catch (\Exception $e) {
            return [
                'contestants' => [],
                'data' => [],
                'hours' => range(0, 23)
            ];
        }
    }
    
    private function getVotingTrendsOld($tenantId, $days = 7)
    {
        try {
            $sql = "
                SELECT 
                    DATE(v.created_at) as vote_date,
                    COUNT(v.id) as vote_count,
                    SUM(v.quantity) as total_quantity,
                    COUNT(DISTINCT t.id) as unique_voters
                FROM votes v
                INNER JOIN events e ON v.event_id = e.id
                LEFT JOIN transactions t ON v.transaction_id = t.id
                WHERE e.tenant_id = :tenant_id
                AND v.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(v.created_at)
                ORDER BY vote_date ASC
            ";
            
            return $this->eventModel->getDatabase()->select($sql, [
                'tenant_id' => $tenantId,
                'days' => $days
            ]);
        } catch (\Exception $e) {
            // Return empty array if query fails
            return [];
        }
    }
    
    // Settings methods
    public function organizationSettings()
    {
        $tenantId = $this->session->getTenantId();
        $tenant = $this->tenantModel->find($tenantId);
        
        $content = $this->renderView('organizer/settings/organization', [
            'tenant' => $tenant,
            'title' => 'Organization Settings'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Organization Settings',
            'breadcrumbs' => [
                ['title' => 'Settings', 'url' => '#'],
                ['title' => 'Organization']
            ]
        ]);
    }
    
    public function updateOrganizationSettings()
    {
        $tenantId = $this->session->getTenantId();
        $data = $this->sanitizeInput($_POST);
        
        try {
            $this->tenantModel->update($tenantId, $data);
            $this->redirect(ORGANIZER_URL . '/settings/organization', 'Organization settings updated successfully', 'success');
        } catch (\Exception $e) {
            $this->redirect(ORGANIZER_URL . '/settings/organization', 'Error updating settings: ' . $e->getMessage(), 'error');
        }
    }
    
    public function userSettings()
    {
        $tenantId = $this->session->getTenantId();
        $users = $this->userModel->findAll(['tenant_id' => $tenantId], 'created_at DESC');
        
        $content = $this->renderView('organizer/settings/users', [
            'users' => $users,
            'title' => 'Team Members'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Team Members',
            'breadcrumbs' => [
                ['title' => 'Settings', 'url' => '#'],
                ['title' => 'Team Members']
            ]
        ]);
    }
    
    public function updateUserSettings()
    {
        // Handle user management actions
        $action = $_POST['action'] ?? '';
        $userId = $_POST['user_id'] ?? '';
        
        try {
            switch ($action) {
                case 'invite':
                    // Handle user invitation
                    $this->redirect(ORGANIZER_URL . '/settings/users', 'User invited successfully', 'success');
                    break;
                case 'update_role':
                    // Handle role update
                    $this->userModel->update($userId, ['role' => $_POST['role']]);
                    $this->redirect(ORGANIZER_URL . '/settings/users', 'User role updated successfully', 'success');
                    break;
                case 'deactivate':
                    // Handle user deactivation
                    $this->userModel->update($userId, ['active' => 0]);
                    $this->redirect(ORGANIZER_URL . '/settings/users', 'User deactivated successfully', 'success');
                    break;
                default:
                    $this->redirect(ORGANIZER_URL . '/settings/users', 'Invalid action', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect(ORGANIZER_URL . '/settings/users', 'Error: ' . $e->getMessage(), 'error');
        }
    }
    
    public function integrationSettings()
    {
        $tenantId = $this->session->getTenantId();
        
        $content = $this->renderView('organizer/settings/integrations', [
            'title' => 'Integrations'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Integrations',
            'breadcrumbs' => [
                ['title' => 'Settings', 'url' => '#'],
                ['title' => 'Integrations']
            ]
        ]);
    }
    
    public function updateIntegrationSettings()
    {
        $tenantId = $this->session->getTenantId();
        $data = $this->sanitizeInput($_POST);
        
        try {
            // Handle integration settings update
            $this->redirect(ORGANIZER_URL . '/settings/integrations', 'Integration settings updated successfully', 'success');
        } catch (\Exception $e) {
            $this->redirect(ORGANIZER_URL . '/settings/integrations', 'Error updating integration settings: ' . $e->getMessage(), 'error');
        }
    }
    
    public function securitySettings()
    {
        $tenantId = $this->session->getTenantId();
        
        $content = $this->renderView('organizer/settings/security', [
            'title' => 'Security Settings'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'Security Settings',
            'breadcrumbs' => [
                ['title' => 'Settings', 'url' => '#'],
                ['title' => 'Security']
            ]
        ]);
    }
    
    public function updateSecuritySettings()
    {
        $tenantId = $this->session->getTenantId();
        $data = $this->sanitizeInput($_POST);
        
        try {
            // Handle security settings update
            $this->redirect(ORGANIZER_URL . '/settings/security', 'Security settings updated successfully', 'success');
        } catch (\Exception $e) {
            $this->redirect(ORGANIZER_URL . '/settings/security', 'Error updating security settings: ' . $e->getMessage(), 'error');
        }
    }
    
    public function publishEvent($id)
    {
        try {
            $tenantId = $this->session->getTenantId();
            $event = $this->eventModel->find($id);
            
            if (!$event || $event['tenant_id'] != $tenantId) {
                $this->redirect(ORGANIZER_URL . '/events', 'Event not found', 'error');
                return;
            }
            
            // Check if event is already published
            if ($event['status'] === 'active') {
                $this->redirect(ORGANIZER_URL . '/events/' . $id, 'Event is already published', 'info');
                return;
            }
            
            // Check plan limits when publishing a draft event
            if ($event['status'] === 'draft') {
                $subscriptionModel = new \SmartCast\Models\TenantSubscription();
                if (!$subscriptionModel->canCreateEvent($tenantId)) {
                    $this->redirect(ORGANIZER_URL . '/events', 'You have reached your plan limit for published events. Please upgrade your plan to publish this draft event.', 'error');
                    return;
                }
            }
            
            // Update event status to active
            $updated = $this->eventModel->update($id, [
                'status' => 'active',
                'published_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($updated) {
                $this->redirect(ORGANIZER_URL . '/events/' . $id, 'Event "' . $event['name'] . '" has been published successfully!', 'success');
            } else {
                $this->redirect(ORGANIZER_URL . '/events/' . $id, 'Failed to publish event', 'error');
            }
            
        } catch (\Exception $e) {
            $this->redirect(ORGANIZER_URL . '/events', 'Error publishing event: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Handle image upload for events and nominees
     */
    private function handleImageUpload($file, $type = 'events')
    {
        // Validate file
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('Invalid file upload');
        }
        
        // Check file size (5MB for events, 2MB for nominees)
        $maxSize = ($type === 'events') ? 5 * 1024 * 1024 : 2 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $maxSizeMB = $maxSize / (1024 * 1024);
            throw new \Exception("File size must be less than {$maxSizeMB}MB");
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new \Exception('Only JPG, PNG, GIF, and WebP images are allowed');
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        // Replace slashes in type for filename to avoid issues
        $typeForFilename = str_replace('/', '_', $type);
        $filename = uniqid($typeForFilename . '_') . '.' . $extension;
        
        // Create upload directory if it doesn't exist
        $uploadDir = UPLOAD_PATH . $type . '/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create directory: {$uploadDir}");
                throw new \Exception('Failed to create upload directory');
            }
        }
        
        $uploadPath = $uploadDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            error_log("Failed to move uploaded file from {$file['tmp_name']} to {$uploadPath}");
            error_log("Upload directory exists: " . (is_dir($uploadDir) ? 'yes' : 'no'));
            error_log("Upload directory writable: " . (is_writable($uploadDir) ? 'yes' : 'no'));
            throw new \Exception('Failed to save uploaded file');
        }
        
        // Optimize image (optional - resize if too large)
        $this->optimizeImage($uploadPath, $type);
        
        // Return relative path instead of full URL for better portability
        return 'public/uploads/' . $type . '/' . $filename;
    }
    
    /**
     * Optimize uploaded images
     */
    private function optimizeImage($imagePath, $type)
    {
        // Set max dimensions based on type
        $maxWidth = ($type === 'events') ? 1200 : 400;
        $maxHeight = ($type === 'events') ? 600 : 400;
        
        // Get image info
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) return;
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        // Skip if image is already small enough
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return;
        }
        
        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        // Create image resource
        switch ($mimeType) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($imagePath);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($imagePath);
                break;
            default:
                return;
        }
        
        if (!$source) return;
        
        // Create new image
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize image
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save resized image
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($resized, $imagePath, 85);
                break;
            case 'image/png':
                imagepng($resized, $imagePath, 6);
                break;
            case 'image/gif':
                imagegif($resized, $imagePath);
                break;
            case 'image/webp':
                imagewebp($resized, $imagePath, 85);
                break;
        }
        
        // Clean up
        imagedestroy($source);
        imagedestroy($resized);
    }
    
    public function toggleResults($eventId)
    {
        $tenantId = $this->session->getTenantId();
        
        // Get event and verify ownership
        $eventModel = new \SmartCast\Models\Event();
        $event = $eventModel->find($eventId);
        
        if (!$event || $event['tenant_id'] != $tenantId) {
            $this->redirect(ORGANIZER_URL . '/events', 'Event not found or access denied', 'error');
            return;
        }
        
        // Toggle results visibility
        $newVisibility = !$event['results_visible'];
        $updated = $eventModel->update($eventId, [
            'results_visible' => $newVisibility ? 1 : 0
        ]);
        
        if ($updated) {
            $action = $newVisibility ? 'shown' : 'hidden';
            $this->redirect(ORGANIZER_URL . "/events/{$eventId}", "Results {$action} successfully", 'success');
        } else {
            $this->redirect(ORGANIZER_URL . "/events/{$eventId}", 'Failed to update results visibility', 'error');
        }
    }
    
    private function getCategoriesByTenant($tenantId)
    {
        $sql = "
            SELECT DISTINCT cat.id, cat.name
            FROM categories cat
            INNER JOIN events e ON cat.event_id = e.id
            WHERE e.tenant_id = :tenant_id
            ORDER BY cat.name ASC
        ";
        
        return $this->eventModel->getDatabase()->select($sql, ['tenant_id' => $tenantId]);
    }
    
    private function getVotingTrendsAnalytics($tenantId, $eventId = null, $timePeriod = '7d')
    {
        try {
            $eventFilter = $eventId ? "AND e.id = :event_id" : "";
            $timeFilter = $this->getTimeFilter($timePeriod);
            $dateFormat = $this->getDateFormat($timePeriod);
            
            $params = ['tenant_id' => $tenantId];
            if ($eventId) {
                $params['event_id'] = $eventId;
            }
            
            $sql = "
                SELECT 
                    $dateFormat as period,
                    COALESCE(SUM(v.quantity), 0) as votes,
                    SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END) as revenue,
                    COUNT(DISTINCT v.transaction_id) as unique_voters
                FROM votes v
                INNER JOIN contestants c ON v.contestant_id = c.id
                INNER JOIN events e ON c.event_id = e.id
                LEFT JOIN transactions t ON v.transaction_id = t.id
                WHERE e.tenant_id = :tenant_id
                $eventFilter
                $timeFilter
                GROUP BY $dateFormat
                ORDER BY v.created_at ASC
            ";
            
            return $this->eventModel->getDatabase()->select($sql, $params);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    private function getPerformanceInsights($tenantId, $eventId = null, $timePeriod = '7d')
    {
        try {
            $eventFilter = $eventId ? "AND e.id = :event_id" : "";
            $timeFilter = $this->getTimeFilter($timePeriod);
            
            $params = ['tenant_id' => $tenantId];
            if ($eventId) {
                $params['event_id'] = $eventId;
            }
            
            // Get contestant performance data
            $sql = "
                SELECT 
                    c.id,
                    c.name,
                    c.contestant_code,
                    c.image_url,
                    COALESCE(SUM(v.quantity), 0) as total_votes,
                    COUNT(DISTINCT v.transaction_id) as unique_voters,
                    ROUND(AVG(v.quantity), 1) as avg_votes_per_voter,
                    SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END) as revenue,
                    HOUR(MAX(v.created_at)) as peak_hour
                FROM contestants c
                INNER JOIN events e ON c.event_id = e.id
                LEFT JOIN votes v ON c.id = v.contestant_id $timeFilter
                LEFT JOIN transactions t ON v.transaction_id = t.id
                WHERE e.tenant_id = :tenant_id
                AND c.active = 1
                $eventFilter
                GROUP BY c.id, c.name, c.contestant_code, c.image_url
                HAVING total_votes > 0
                ORDER BY total_votes DESC
                LIMIT 10
            ";
            
            return $this->eventModel->getDatabase()->select($sql, $params);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    private function getTimeFilter($timePeriod)
    {
        switch ($timePeriod) {
            case '24h':
                return "AND v.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            case '7d':
                return "AND v.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case '30d':
                return "AND v.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '90d':
                return "AND v.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            default:
                return "AND v.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        }
    }
    
    private function getDateFormat($timePeriod)
    {
        switch ($timePeriod) {
            case '24h':
                return "DATE_FORMAT(v.created_at, '%H:00')";
            case '7d':
                return "DATE_FORMAT(v.created_at, '%a')";
            case '30d':
            case '90d':
                return "DATE(v.created_at)";
            default:
                return "DATE_FORMAT(v.created_at, '%a')";
        }
    }
    
    private function getDateRangeFilter($dateRange)
    {
        switch ($dateRange) {
            case 'today':
                return "DATE(t.created_at) = CURDATE()";
            case 'week':
                return "t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'month':
                return "t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case 'custom':
                // Would need start_date and end_date parameters
                return null;
            default:
                return "t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        }
    }
    
    private function getReceiptStats($tenantId, $eventId = null, $dateRange = 'week')
    {
        try {
            $eventFilter = $eventId ? "AND e.id = :event_id" : "";
            $dateFilter = $this->getDateRangeFilter($dateRange);
            $dateCondition = $dateFilter ? "AND $dateFilter" : "";
            
            $params = ['tenant_id' => $tenantId];
            if ($eventId) {
                $params['event_id'] = $eventId;
            }
            
            $sql = "
                SELECT 
                    COUNT(t.id) as total_receipts,
                    SUM(CASE WHEN t.status = 'success' THEN 1 ELSE 0 END) as successful_receipts,
                    SUM(CASE WHEN t.status = 'pending' THEN 1 ELSE 0 END) as pending_receipts,
                    SUM(CASE WHEN t.status = 'failed' THEN 1 ELSE 0 END) as failed_receipts,
                    SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END) as total_revenue
                FROM transactions t
                INNER JOIN events e ON t.event_id = e.id
                WHERE e.tenant_id = :tenant_id
                $eventFilter
                $dateCondition
            ";
            
            $result = $this->transactionModel->getDatabase()->selectOne($sql, $params);
            
            return [
                'total_receipts' => $result['total_receipts'] ?? 0,
                'successful_receipts' => $result['successful_receipts'] ?? 0,
                'pending_receipts' => $result['pending_receipts'] ?? 0,
                'failed_receipts' => $result['failed_receipts'] ?? 0,
                'total_revenue' => $result['total_revenue'] ?? 0,
                'success_rate' => $result['total_receipts'] > 0 ? 
                    round(($result['successful_receipts'] / $result['total_receipts']) * 100, 1) : 0,
                'failure_rate' => $result['total_receipts'] > 0 ? 
                    round(($result['failed_receipts'] / $result['total_receipts']) * 100, 2) : 0
            ];
        } catch (\Exception $e) {
            return [
                'total_receipts' => 0,
                'successful_receipts' => 0,
                'pending_receipts' => 0,
                'failed_receipts' => 0,
                'total_revenue' => 0,
                'success_rate' => 0,
                'failure_rate' => 0
            ];
        }
    }
    
    /**
     * Get default tenant limits when no subscription is active
     */
    private function getDefaultTenantLimits()
    {
        return [
            'plan_name' => 'No Active Plan',
            'max_events' => null,
            'current_events' => 0,
            'events_remaining' => 'Unlimited',
            'max_contestants_per_event' => null,
            'max_votes_per_event' => null,
            'unlimited_events' => true,
            'unlimited_contestants' => true,
            'unlimited_votes' => true,
            'expires_at' => null,
            'status' => 'inactive'
        ];
    }
    
    /**
     * Update event status (draft, private, active)
     */
    public function updateEventStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }
        
        $tenantId = $this->session->getTenantId();
        
        // Get event details
        $event = $this->eventModel->find($id);
        if (!$event || $event['tenant_id'] != $tenantId) {
            $this->json(['success' => false, 'message' => 'Event not found'], 404);
            return;
        }
        
        $newStatus = $_POST['status'] ?? '';
        $newVisibility = $_POST['visibility'] ?? $event['visibility'];
        
        // Validate status
        $validStatuses = ['draft', 'active', 'suspended', 'closed'];
        $validVisibilities = ['private', 'public', 'unlisted'];
        
        if (!in_array($newStatus, $validStatuses)) {
            $this->json(['success' => false, 'message' => 'Invalid status'], 400);
            return;
        }
        
        if (!in_array($newVisibility, $validVisibilities)) {
            $this->json(['success' => false, 'message' => 'Invalid visibility'], 400);
            return;
        }
        
        try {
            // Update event status
            $updateData = [
                'status' => $newStatus,
                'visibility' => $newVisibility
            ];
            
            // Add timestamps for specific status changes
            if ($newStatus === 'active' && $event['status'] !== 'active') {
                // Event is being activated
                $updateData['admin_status'] = 'approved'; // Auto-approve for organizer changes
            } elseif ($newStatus === 'suspended') {
                $updateData['suspended_at'] = date('Y-m-d H:i:s');
                $updateData['suspended_by'] = $this->session->getUserId();
            } elseif ($newStatus === 'closed') {
                $updateData['closed_at'] = date('Y-m-d H:i:s');
            }
            
            $success = $this->eventModel->update($id, $updateData);
            
            if ($success) {
                // Log the status change
                $auditModel = new \SmartCast\Models\AuditLog();
                $auditModel->log([
                    'user_id' => $this->session->getUserId(),
                    'tenant_id' => $tenantId,
                    'action' => 'event_status_updated',
                    'resource_type' => 'event',
                    'resource_id' => $id,
                    'details' => json_encode([
                        'old_status' => $event['status'],
                        'new_status' => $newStatus,
                        'old_visibility' => $event['visibility'],
                        'new_visibility' => $newVisibility
                    ])
                ]);
                
                $this->json([
                    'success' => true, 
                    'message' => 'Event status updated successfully',
                    'status' => $newStatus,
                    'visibility' => $newVisibility
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to update event status'], 500);
            }
            
        } catch (\Exception $e) {
            error_log('Event status update error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while updating event status'], 500);
        }
    }
    
    /**
     * Get categories for a specific event (API endpoint)
     */
    public function getEventCategories($id)
    {
        header('Content-Type: application/json');
        
        $tenantId = $this->session->getTenantId();
        
        // Get event details to verify ownership
        $event = $this->eventModel->find($id);
        if (!$event || $event['tenant_id'] != $tenantId) {
            $this->json(['success' => false, 'message' => 'Event not found'], 404);
            return;
        }
        
        try {
            // Get categories for this event
            $categories = $this->categoryModel->getCategoriesByEvent($id);
            
            $this->json([
                'success' => true,
                'categories' => $categories,
                'event' => [
                    'id' => $event['id'],
                    'name' => $event['name'],
                    'status' => $event['status']
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log('Get event categories error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while loading categories'], 500);
        }
    }

    /**
     * Show shortcode generation statistics and test the new system
     */
    public function shortcodeStats()
    {
        try {
            $contestantCategoryModel = new \SmartCast\Models\ContestantCategory();
            
            // Get comprehensive statistics
            $stats = $contestantCategoryModel->getShortCodeStats();
            
            $this->view('organizer/events/shortcode-stats', [
                'stats' => $stats,
                'title' => 'Random Shortcode Generation Statistics'
            ]);

        } catch (\Exception $e) {
            error_log('Shortcode stats error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . '/events', 'Error loading shortcode statistics', 'error');
        }
    }

    /**
     * Run shortcode migration to update existing codes to random format
     */
    public function migrateShortcodes()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Run the migration
                $contestantCategoryModel = new \SmartCast\Models\ContestantCategory();
                
                // Get existing shortcodes
                $existingCodes = $this->db->select("
                    SELECT id, short_code, contestant_id, category_id 
                    FROM contestant_categories 
                    WHERE short_code IS NOT NULL 
                    AND short_code != ''
                    AND active = 1
                    ORDER BY id ASC
                ");
                
                if (empty($existingCodes)) {
                    $this->redirect(ORGANIZER_URL . '/shortcode-stats', 'No shortcodes found to migrate.', 'info');
                    return;
                }
                
                $updated = 0;
                $errors = 0;
                
                // Start transaction
                $this->db->getConnection()->beginTransaction();
                
                foreach ($existingCodes as $record) {
                    try {
                        // Generate new random shortcode using the model method
                        $newShortcode = $contestantCategoryModel->generateShortCode(
                            $record['category_id'], 
                            'Migration', 
                            $record['contestant_id']
                        );
                        
                        // Update the record
                        $this->db->query("
                            UPDATE contestant_categories 
                            SET short_code = :short_code, updated_at = NOW()
                            WHERE id = :id
                        ", [
                            'short_code' => $newShortcode,
                            'id' => $record['id']
                        ]);
                        
                        $updated++;
                        
                    } catch (\Exception $e) {
                        error_log("Error updating shortcode ID {$record['id']}: " . $e->getMessage());
                        $errors++;
                    }
                }
                
                // Commit transaction
                $this->db->getConnection()->commit();
                
                $message = "Migration completed! Updated {$updated} shortcodes.";
                if ($errors > 0) {
                    $message .= " {$errors} errors occurred.";
                }
                
                $this->redirect(ORGANIZER_URL . '/shortcode-stats', $message, 'success');
                return;
            }
            
            // Show migration confirmation page
            $contestantCategoryModel = new \SmartCast\Models\ContestantCategory();
            $stats = $contestantCategoryModel->getShortCodeStats();
            
            // Get count of existing shortcodes that need migration
            $existingCount = $this->db->selectOne("
                SELECT COUNT(*) as count 
                FROM contestant_categories 
                WHERE short_code IS NOT NULL 
                AND short_code != ''
                AND active = 1
                AND (
                    LENGTH(short_code) != 4 
                    OR short_code NOT REGEXP '^[ABCDEFGHJKLMNPQRSTUVWXYZ]{2}[0-9]{2}$'
                )
            ")['count'] ?? 0;
            
            $this->view('organizer/events/migrate-shortcodes', [
                'stats' => $stats,
                'existing_count' => $existingCount,
                'title' => 'Migrate Shortcodes'
            ]);

        } catch (\Exception $e) {
            error_log('Shortcode migration error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . '/shortcode-stats', 'Migration failed: ' . $e->getMessage(), 'error');
        }
    }
    
    public function reorderCategories()
    {
        // Start output buffering to catch any unexpected output
        ob_start();
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ob_clean(); // Clear any buffered output
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $tenantId = $this->session->getTenantId();
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['event_id']) || !isset($input['category_ids'])) {
            ob_clean(); // Clear any buffered output
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            return;
        }
        
        $eventId = intval($input['event_id']);
        $categoryIds = $input['category_ids'];
        
        // Verify event ownership
        $eventModel = new \SmartCast\Models\Event();
        $event = $eventModel->find($eventId);
        
        if (!$event || $event['tenant_id'] != $tenantId) {
            ob_clean(); // Clear any buffered output
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        try {
            // Update category display order
            $categoryModel = new \SmartCast\Models\Category();
            
            foreach ($categoryIds as $index => $categoryId) {
                $result = $categoryModel->update($categoryId, ['display_order' => $index + 1]);
                if (!$result) {
                    throw new \Exception("Failed to update display order for category {$categoryId}");
                }
            }
            
            ob_clean(); // Clear any buffered output
            echo json_encode(['success' => true, 'message' => 'Category order updated successfully']);
        } catch (\Exception $e) {
            error_log('Error reordering categories: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            ob_clean(); // Clear any buffered output
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update category order: ' . $e->getMessage()]);
        }
    }
    
    public function reorderContestants()
    {
        // Start output buffering to catch any unexpected output
        ob_start();
        
        header('Content-Type: application/json');
        
        try {
            error_log('=== reorderContestants START ===');
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                error_log('reorderContestants: Method not allowed');
                ob_clean();
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                return;
            }
            
            $tenantId = $this->session->getTenantId();
            error_log('reorderContestants: tenantId = ' . $tenantId);
            
            $rawInput = file_get_contents('php://input');
            error_log('reorderContestants: raw input = ' . $rawInput);
            
            $input = json_decode($rawInput, true);
            error_log('reorderContestants: parsed input = ' . json_encode($input));
            
            if (!isset($input['category_id']) || !isset($input['contestant_ids'])) {
                error_log('reorderContestants: Missing required parameters');
                ob_clean();
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
                return;
            }
            
            $categoryId = intval($input['category_id']);
            $contestantIds = $input['contestant_ids'];
            
            error_log('reorderContestants: categoryId = ' . $categoryId);
            error_log('reorderContestants: contestantIds = ' . json_encode($contestantIds));
            
            // Verify category ownership through event
            $categoryModel = new \SmartCast\Models\Category();
            $eventModel = new \SmartCast\Models\Event();
            
            error_log('reorderContestants: Finding category...');
            $category = $categoryModel->find($categoryId);
            if (!$category) {
                error_log('reorderContestants: Category not found - ' . $categoryId);
                ob_clean();
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Category not found']);
                return;
            }
            
            error_log('reorderContestants: Category found = ' . json_encode($category));
            
            error_log('reorderContestants: Finding event...');
            $event = $eventModel->find($category['event_id']);
            if (!$event || $event['tenant_id'] != $tenantId) {
                error_log('reorderContestants: Unauthorized access - event: ' . json_encode($event) . ', tenantId: ' . $tenantId);
                ob_clean();
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
                return;
            }
            
            error_log('reorderContestants: Event found = ' . json_encode($event));
            
            // Update contestant display order in contestant_categories table
            $contestantCategoryModel = new \SmartCast\Models\ContestantCategory();
            error_log('reorderContestants: ContestantCategory model created');
            
            foreach ($contestantIds as $index => $contestantId) {
                error_log("reorderContestants: Updating contestant {$contestantId} in category {$categoryId} to position " . ($index + 1));
                
                $result = $contestantCategoryModel->updateDisplayOrderForCategory($contestantId, $categoryId, $index + 1);
                error_log("reorderContestants: Update result for contestant {$contestantId}: " . ($result ? 'SUCCESS' : 'FAILED'));
                
                if (!$result) {
                    throw new \Exception("Failed to update display order for contestant {$contestantId}");
                }
            }
            
            error_log('reorderContestants: All updates successful');
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Contestant order updated successfully']);
            error_log('=== reorderContestants END SUCCESS ===');
            
        } catch (\Exception $e) {
            error_log('=== reorderContestants ERROR ===');
            error_log('Error reordering contestants: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            error_log('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            
            ob_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update contestant order: ' . $e->getMessage()]);
        }
    }
}
