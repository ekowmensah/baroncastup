<?php

namespace SmartCast\Controllers;

use SmartCast\Models\Event;
use SmartCast\Models\Contestant;
use SmartCast\Models\Category;
use SmartCast\Models\User;
use SmartCast\Models\Tenant;
use SmartCast\Models\Vote;
use SmartCast\Models\VoteBundle;
use SmartCast\Models\AuditLog;

/**
 * Admin Controller
 */
class AdminController extends BaseController
{
    private $eventModel;
    private $contestantModel;
    private $categoryModel;
    private $userModel;
    private $tenantModel;
    private $voteModel;
    private $bundleModel;
    private $auditModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        
        $this->eventModel = new Event();
        $this->contestantModel = new Contestant();
        $this->categoryModel = new Category();
        $this->userModel = new User();
        $this->tenantModel = new Tenant();
        $this->voteModel = new Vote();
        $this->bundleModel = new VoteBundle();
        $this->auditModel = new AuditLog();
    }
    
    public function dashboard()
    {
        $tenantId = $this->session->getTenantId();
        $userId = $this->session->getUserId();
        
        // Get dashboard stats
        $stats = $this->getDashboardStats($tenantId);
        
        // Get recent events
        $recentEvents = $this->eventModel->getEventsByTenant($tenantId);
        $recentEvents = array_slice($recentEvents, 0, 5);
        
        // Get recent audit logs
        $recentLogs = $this->auditModel->getRecentLogs($tenantId, 10);
        
        $this->view('admin/dashboard', [
            'stats' => $stats,
            'recentEvents' => $recentEvents,
            'recentLogs' => $recentLogs,
            'title' => 'Dashboard'
        ]);
    }
    
    public function events()
    {
        $tenantId = $this->session->getTenantId();
        $events = $this->eventModel->getEventsByTenant($tenantId);
        
        $this->view('admin/events/index', [
            'events' => $events,
            'title' => 'Events'
        ]);
    }
    
    public function createEvent()
    {
        $this->view('admin/events/create', [
            'title' => 'Create Event'
        ]);
    }
    
    public function storeEvent()
    {
        $data = $this->sanitizeInput($_POST);
        
        // Validate input
        $errors = $this->validateInput($data, [
            'name' => ['required' => true, 'min' => 3, 'max' => 255],
            'code' => ['required' => true, 'min' => 3, 'max' => 50],
            'start_date' => ['required' => true],
            'end_date' => ['required' => true]
        ]);
        
        // Check if event code is unique
        if ($this->eventModel->findByCode($data['code'])) {
            $errors['code'] = 'Event code already exists';
        }
        
        if (!empty($errors)) {
            $this->view('admin/events/create', [
                'errors' => $errors,
                'data' => $data,
                'title' => 'Create Event'
            ]);
            return;
        }
        
        try {
            // Handle file upload if present
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['size'] > 0) {
                $data['featured_image'] = $this->uploadFile($_FILES['featured_image'], 'events');
            }
            
            $data['tenant_id'] = $this->session->getTenantId();
            $data['created_by'] = $this->session->getUserId();
            $data['status'] = 'draft';
            $data['visibility'] = $data['visibility'] ?? 'private';
            
            $eventId = $this->eventModel->create($data);
            
            // Create default vote bundles
            $this->bundleModel->createDefaultBundles($eventId);
            
            // Log the action
            $this->auditModel->logEventCreated(
                $this->session->getUserId(),
                $eventId,
                $data['name']
            );
            
            $this->redirect('/admin/events', 'Event created successfully', 'success');
            
        } catch (\Exception $e) {
            $this->view('admin/events/create', [
                'error' => 'Failed to create event: ' . $e->getMessage(),
                'data' => $data,
                'title' => 'Create Event'
            ]);
        }
    }
    
    public function editEvent($eventId)
    {
        $event = $this->eventModel->find($eventId);
        
        if (!$event || !$this->canManageEvent($eventId)) {
            $this->redirect('/admin/events', 'Event not found or access denied', 'error');
        }
        
        $categories = $this->categoryModel->getCategoriesByEvent($eventId);
        $contestants = $this->contestantModel->getContestantsByEvent($eventId);
        $bundles = $this->bundleModel->getBundlesByEvent($eventId);
        
        $this->view('admin/events/edit', [
            'event' => $event,
            'categories' => $categories,
            'contestants' => $contestants,
            'bundles' => $bundles,
            'title' => 'Edit Event: ' . $event['name']
        ]);
    }
    
    public function contestants()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get all contestants for this tenant
        $contestants = $this->contestantModel->findAll(['tenant_id' => $tenantId], 'created_at DESC');
        
        $this->view('admin/contestants/index', [
            'contestants' => $contestants,
            'title' => 'Contestants'
        ]);
    }
    
    public function createContestant()
    {
        $tenantId = $this->session->getTenantId();
        $events = $this->eventModel->getEventsByTenant($tenantId);
        
        $this->view('admin/contestants/create', [
            'events' => $events,
            'title' => 'Create Contestant'
        ]);
    }
    
    public function storeContestant()
    {
        $data = $this->sanitizeInput($_POST);
        
        // Validate input
        $errors = $this->validateInput($data, [
            'name' => ['required' => true, 'min' => 2, 'max' => 255],
            'event_id' => ['required' => true, 'numeric' => true]
        ]);
        
        if (!empty($errors)) {
            $tenantId = $this->session->getTenantId();
            $events = $this->eventModel->getEventsByTenant($tenantId);
            
            $this->view('admin/contestants/create', [
                'errors' => $errors,
                'data' => $data,
                'events' => $events,
                'title' => 'Create Contestant'
            ]);
            return;
        }
        
        try {
            // Handle file upload if present
            if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                $data['image_url'] = $this->uploadFile($_FILES['image'], 'contestants');
            }
            
            $data['tenant_id'] = $this->session->getTenantId();
            $data['created_by'] = $this->session->getUserId();
            
            // Generate contestant code if not provided
            if (empty($data['contestant_code'])) {
                $data['contestant_code'] = $this->contestantModel->generateContestantCode(
                    $data['tenant_id'],
                    $data['event_id']
                );
            }
            
            $contestantId = $this->contestantModel->create($data);
            
            // Log the action
            $this->auditModel->logContestantCreated(
                $this->session->getUserId(),
                $contestantId,
                $data['name']
            );
            
            $this->redirect('/admin/contestants', 'Contestant created successfully', 'success');
            
        } catch (\Exception $e) {
            $tenantId = $this->session->getTenantId();
            $events = $this->eventModel->getEventsByTenant($tenantId);
            
            $this->view('admin/contestants/create', [
                'error' => 'Failed to create contestant: ' . $e->getMessage(),
                'data' => $data,
                'events' => $events,
                'title' => 'Create Contestant'
            ]);
        }
    }
    
    public function reports()
    {
        $tenantId = $this->session->getTenantId();
        
        // Get reporting data
        $events = $this->eventModel->getEventsByTenant($tenantId);
        $totalVotes = 0;
        $totalRevenue = 0;
        
        foreach ($events as &$event) {
            $eventVotes = $this->voteModel->getTotalVotes($event['id']);
            $event['total_votes'] = $eventVotes;
            $totalVotes += $eventVotes;
        }
        
        $this->view('admin/reports', [
            'events' => $events,
            'totalVotes' => $totalVotes,
            'totalRevenue' => $totalRevenue,
            'title' => 'Reports'
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
            'active' => 1,
            'status' => 'active'
        ]);
        
        // Total contestants
        $stats['total_contestants'] = $this->contestantModel->count([
            'tenant_id' => $tenantId,
            'active' => 1
        ]);
        
        // Total votes (approximate)
        $sql = "
            SELECT COALESCE(SUM(v.quantity), 0) as total
            FROM votes v
            INNER JOIN events e ON v.event_id = e.id
            WHERE e.tenant_id = :tenant_id
        ";
        $result = $this->eventModel->db->selectOne($sql, ['tenant_id' => $tenantId]);
        $stats['total_votes'] = $result['total'] ?? 0;
        
        return $stats;
    }
    
    private function canManageEvent($eventId)
    {
        $userId = $this->session->getUserId();
        return $this->userModel->canManageEvent($userId, $eventId);
    }
}
