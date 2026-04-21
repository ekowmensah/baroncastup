<?php

namespace SmartCast\Controllers;

use SmartCast\Core\Database;
use SmartCast\Models\Tenant;
use SmartCast\Models\User;
use SmartCast\Models\Event;
use SmartCast\Models\Category;
use SmartCast\Models\Contestant;
use SmartCast\Models\Transaction;
use SmartCast\Models\AuditLog;
use SmartCast\Models\FraudEvent;
use SmartCast\Models\RevenueShare;
use SmartCast\Models\Payout;
use SmartCast\Models\TenantBalance;
use SmartCast\Models\SmsGateway;
use SmartCast\Models\SmsLog;
use SmartCast\Services\PayoutService;
use SmartCast\Services\SmsService;
use SmartCast\Services\NotificationService;

/**
 * Super Admin Dashboard Controller
 */
class SuperAdminController extends BaseController
{
    private $db;
    private $tenantModel;
    private $userModel;
    private $eventModel;
    private $categoryModel;
    private $contestantModel;
    private $transactionModel;
    private $auditModel;
    private $fraudModel;
    private $revenueModel;
    private $payoutModel;
    private $balanceModel;
    private $payoutService;
    private $smsGateway;
    private $smsLog;
    private $smsService;
    private $notificationService;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->requireRole('platform_admin');
        
        $this->db = Database::getInstance();
        $this->tenantModel = new Tenant();
        $this->userModel = new User();
        $this->eventModel = new Event();
        $this->categoryModel = new Category();
        $this->contestantModel = new Contestant();
        $this->transactionModel = new Transaction();
        $this->auditModel = new AuditLog();
        $this->fraudModel = new FraudEvent();
        $this->revenueModel = new RevenueShare();
        $this->payoutModel = new Payout();
        $this->balanceModel = new TenantBalance();
        $this->payoutService = new PayoutService();
        $this->smsGateway = new SmsGateway();
        $this->smsLog = new SmsLog();
        $this->smsService = new SmsService();
        $this->notificationService = new NotificationService();
    }
    
    public function dashboard()
    {
        // Get platform-wide statistics
        $stats = $this->getPlatformStats();
        
        // Get recent activity
        $recentActivity = $this->auditModel->getRecentLogs(null, 20);
        
        // Get security alerts
        $securityAlerts = $this->fraudModel->getFraudEvents(null, null, 10);
        
        // Get top performing events
        $topEvents = $this->getTopPerformingEvents(5);
        
        // Get top tenants by revenue
        $topTenants = $this->getTopTenantsByRevenue(5);
        
        // Get recent transactions
        $recentTransactions = $this->getRecentTransactions(10);
        
        $content = $this->renderView('superadmin/dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'securityAlerts' => $securityAlerts,
            'topEvents' => $topEvents,
            'topTenants' => $topTenants,
            'recentTransactions' => $recentTransactions,
            'title' => 'Platform Dashboard'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Platform Dashboard'
        ]);
    }
    
    private function getTopPerformingEvents($limit = 5)
    {
        $sql = "
            SELECT e.id, e.name, e.code, e.status,
                   t.name as tenant_name,
                   COUNT(DISTINCT tr.id) as transaction_count,
                   COALESCE(SUM(CASE WHEN tr.status = 'success' THEN v.quantity ELSE 0 END), 0) as total_votes,
                   COALESCE(SUM(CASE WHEN tr.status = 'success' THEN tr.amount ELSE 0 END), 0) as total_amount,
                   COALESCE(SUM(CASE 
                       WHEN tr.status = 'success' 
                       THEN COALESCE(rs.amount, tr.amount * COALESCE(fr.percentage_rate, 15) / 100)
                       ELSE 0 
                   END), 0) as platform_revenue
            FROM events e
            INNER JOIN tenants t ON e.tenant_id = t.id
            LEFT JOIN contestants c ON e.id = c.event_id
            LEFT JOIN votes v ON c.id = v.contestant_id
            LEFT JOIN transactions tr ON v.transaction_id = tr.id
            LEFT JOIN revenue_shares rs ON tr.id = rs.transaction_id
            LEFT JOIN subscription_plans sp ON t.current_plan_id = sp.id
            LEFT JOIN fee_rules fr ON sp.fee_rule_id = fr.id
            WHERE e.status = 'active'
            GROUP BY e.id
            ORDER BY platform_revenue DESC
            LIMIT :limit
        ";
        
        return $this->db->select($sql, ['limit' => $limit]);
    }
    
    private function getTopTenantsByRevenue($limit = 5)
    {
        $sql = "
            SELECT t.id, t.name, t.email,
                   COUNT(DISTINCT e.id) as event_count,
                   COUNT(DISTINCT CASE WHEN e.status = 'active' THEN e.id END) as active_events,
                   COALESCE(tb.total_earned, 0) as total_earned,
                   COALESCE(tb.available, 0) as available_balance
            FROM tenants t
            LEFT JOIN events e ON t.id = e.tenant_id
            LEFT JOIN tenant_balances tb ON t.id = tb.tenant_id
            WHERE t.active = 1
            GROUP BY t.id
            ORDER BY total_earned DESC
            LIMIT :limit
        ";
        
        return $this->db->select($sql, ['limit' => $limit]);
    }
    
    private function getRecentTransactions($limit = 10)
    {
        $sql = "
            SELECT tr.id, tr.amount, tr.status, tr.created_at,
                   e.name as event_name,
                   t.name as tenant_name,
                   v.quantity as votes
            FROM transactions tr
            INNER JOIN events e ON tr.event_id = e.id
            INNER JOIN tenants t ON e.tenant_id = t.id
            LEFT JOIN votes v ON tr.id = v.transaction_id
            ORDER BY tr.created_at DESC
            LIMIT :limit
        ";
        
        return $this->db->select($sql, ['limit' => $limit]);
    }
    
    public function tenants()
    {
        // Get tenants with comprehensive data using new subscription system
        $sql = "
            SELECT 
                t.*,
                sp.name as plan_name,
                sp.price as plan_price,
                sp.billing_cycle,
                ts.status as subscription_status,
                ts.expires_at as subscription_expires,
                
                -- Event and contestant counts
                (SELECT COUNT(*) FROM events WHERE tenant_id = t.id) as total_events,
                (SELECT COUNT(*) FROM events WHERE tenant_id = t.id AND status = 'active') as active_events,
                (SELECT COUNT(DISTINCT c.id) FROM events e JOIN contestants c ON e.id = c.event_id WHERE e.tenant_id = t.id AND c.active = 1) as total_contestants,
                
                -- Revenue calculations using subqueries for accuracy
                COALESCE(tb.total_earned, 0) as total_revenue,
                COALESCE((
                    SELECT SUM(t_inner.amount - COALESCE(rs_inner.amount, 0))
                    FROM transactions t_inner
                    JOIN events e_inner ON t_inner.event_id = e_inner.id
                    LEFT JOIN revenue_shares rs_inner ON t_inner.id = rs_inner.transaction_id
                    WHERE e_inner.tenant_id = t.id 
                    AND t_inner.status = 'success'
                    AND t_inner.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ), 0) as monthly_revenue,
                
                -- Transaction and user counts
                (SELECT COUNT(*) FROM transactions tr JOIN events e ON tr.event_id = e.id WHERE e.tenant_id = t.id AND tr.status = 'success') as total_transactions,
                (SELECT MAX(tr.created_at) FROM transactions tr JOIN events e ON tr.event_id = e.id WHERE e.tenant_id = t.id AND tr.status = 'success') as last_transaction_date,
                (SELECT COUNT(*) FROM users WHERE tenant_id = t.id AND active = 1) as user_count,
                
                -- Balance information
                tb.available,
                tb.pending
            FROM tenants t
            LEFT JOIN tenant_subscriptions ts ON t.id = ts.tenant_id AND ts.status = 'active'
            LEFT JOIN subscription_plans sp ON ts.plan_id = sp.id
            LEFT JOIN tenant_balances tb ON t.id = tb.tenant_id
            ORDER BY t.created_at DESC
        ";
        
        $tenants = $this->tenantModel->getDatabase()->select($sql);
        
        // Debug: Check tenant_balances data
        error_log("=== TENANT REVENUE DEBUG ===");
        foreach ($tenants as $tenant) {
            if ($tenant['id']) {
                error_log("Tenant {$tenant['id']} ({$tenant['name']}): total_revenue={$tenant['total_revenue']}, monthly_revenue={$tenant['monthly_revenue']}, available={$tenant['available']}");
            }
        }
        
        // Get available subscription plans from database
        $planModel = new \SmartCast\Models\SubscriptionPlan();
        $availablePlans = $planModel->findAll(['is_active' => 1], 'sort_order ASC, name ASC');
        
        $content = $this->renderView('superadmin/tenants/index', [
            'tenants' => $tenants,
            'availablePlans' => $availablePlans,
            'title' => 'All Tenants'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Tenants',
            'breadcrumbs' => [
                ['title' => 'Tenants']
            ]
        ]);
    }
    
    public function approveTenant()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $tenantId = $_POST['tenant_id'] ?? null;
        
        if (!$tenantId || !is_numeric($tenantId)) {
            return $this->json(['success' => false, 'message' => 'Invalid tenant ID'], 400);
        }
        
        try {
            $tenant = $this->tenantModel->find($tenantId);
            
            if (!$tenant) {
                return $this->json(['success' => false, 'message' => 'Tenant not found'], 404);
            }
            
            if ($tenant['verified']) {
                return $this->json(['success' => false, 'message' => 'Tenant is already approved'], 400);
            }
            
            // Approve the tenant
            $this->tenantModel->update($tenantId, [
                'verified' => 1,
                'approved_at' => date('Y-m-d H:i:s'),
                'approved_by' => $this->session->getUserId()
            ]);
            
            // Log the approval
            $this->auditModel->create([
                'user_id' => $this->session->getUserId(),
                'action' => 'tenant_approved',
                'details' => json_encode([
                    'tenant_id' => $tenantId,
                    'tenant_name' => $tenant['name'],
                    'tenant_email' => $tenant['email']
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            // Send approval notifications (email and SMS)
            try {
                $notificationResults = $this->notificationService->sendTenantApprovalNotifications($tenant);
                
                // Log notification results
                $this->auditModel->create([
                    'user_id' => $this->session->getUserId(),
                    'action' => 'tenant_approval_notifications_sent',
                    'details' => json_encode([
                        'tenant_id' => $tenantId,
                        'email_result' => $notificationResults['email'],
                        'sms_result' => $notificationResults['sms']
                    ]),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                ]);
                
            } catch (\Exception $e) {
                error_log("Tenant approval notification error: " . $e->getMessage());
                // Continue with success response even if notifications fail
            }
            
            return $this->json([
                'success' => true, 
                'message' => 'Tenant approved successfully',
                'tenant_name' => $tenant['name']
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Approval failed: ' . $e->getMessage()], 500);
        }
    }
    
    public function rejectTenant()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $tenantId = $_POST['tenant_id'] ?? null;
        $reason = $_POST['reason'] ?? 'No reason provided';
        
        if (!$tenantId || !is_numeric($tenantId)) {
            return $this->json(['success' => false, 'message' => 'Invalid tenant ID'], 400);
        }
        
        try {
            $tenant = $this->tenantModel->find($tenantId);
            
            if (!$tenant) {
                return $this->json(['success' => false, 'message' => 'Tenant not found'], 404);
            }
            
            if ($tenant['verified']) {
                return $this->json(['success' => false, 'message' => 'Cannot reject an approved tenant'], 400);
            }
            
            // Deactivate the tenant instead of deleting
            $this->tenantModel->update($tenantId, [
                'active' => 0,
                'rejection_reason' => $reason,
                'rejected_at' => date('Y-m-d H:i:s'),
                'rejected_by' => $this->session->getUserId()
            ]);
            
            // Log the rejection
            $this->auditModel->create([
                'user_id' => $this->session->getUserId(),
                'action' => 'tenant_rejected',
                'details' => json_encode([
                    'tenant_id' => $tenantId,
                    'tenant_name' => $tenant['name'],
                    'tenant_email' => $tenant['email'],
                    'reason' => $reason
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            // TODO: Send rejection email notification to tenant
            
            return $this->json([
                'success' => true, 
                'message' => 'Tenant rejected successfully',
                'tenant_name' => $tenant['name']
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Rejection failed: ' . $e->getMessage()], 500);
        }
    }
    
    public function suspendTenant()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $tenantId = $_POST['tenant_id'] ?? null;
        $reason = $_POST['reason'] ?? 'No reason provided';
        
        if (!$tenantId || !is_numeric($tenantId)) {
            return $this->json(['success' => false, 'message' => 'Invalid tenant ID'], 400);
        }
        
        try {
            $tenant = $this->tenantModel->find($tenantId);
            
            if (!$tenant) {
                return $this->json(['success' => false, 'message' => 'Tenant not found'], 404);
            }
            
            // Suspend the tenant
            $this->tenantModel->update($tenantId, [
                'active' => 0,
                'suspension_reason' => $reason,
                'suspended_at' => date('Y-m-d H:i:s'),
                'suspended_by' => $this->session->getUserId()
            ]);
            
            // Log the suspension
            $this->auditModel->create([
                'user_id' => $this->session->getUserId(),
                'action' => 'tenant_suspended',
                'details' => json_encode([
                    'tenant_id' => $tenantId,
                    'tenant_name' => $tenant['name'],
                    'tenant_email' => $tenant['email'],
                    'reason' => $reason
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            return $this->json([
                'success' => true, 
                'message' => 'Tenant suspended successfully',
                'tenant_name' => $tenant['name']
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Suspension failed: ' . $e->getMessage()], 500);
        }
    }
    
    public function reactivateTenant()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $tenantId = $_POST['tenant_id'] ?? null;
        
        if (!$tenantId || !is_numeric($tenantId)) {
            return $this->json(['success' => false, 'message' => 'Invalid tenant ID'], 400);
        }
        
        try {
            $tenant = $this->tenantModel->find($tenantId);
            
            if (!$tenant) {
                return $this->json(['success' => false, 'message' => 'Tenant not found'], 404);
            }
            
            // Reactivate the tenant
            $this->tenantModel->update($tenantId, [
                'active' => 1,
                'verified' => 1, // Ensure it's also verified
                'suspension_reason' => null,
                'rejection_reason' => null,
                'reactivated_at' => date('Y-m-d H:i:s'),
                'reactivated_by' => $this->session->getUserId()
            ]);
            
            // Log the reactivation
            $this->auditModel->create([
                'user_id' => $this->session->getUserId(),
                'action' => 'tenant_reactivated',
                'details' => json_encode([
                    'tenant_id' => $tenantId,
                    'tenant_name' => $tenant['name'],
                    'tenant_email' => $tenant['email']
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            return $this->json([
                'success' => true, 
                'message' => 'Tenant reactivated successfully',
                'tenant_name' => $tenant['name']
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Reactivation failed: ' . $e->getMessage()], 500);
        }
    }
    
    public function loginAsTenant($tenantId = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        if (!$tenantId || !is_numeric($tenantId)) {
            return $this->json(['success' => false, 'message' => 'Invalid tenant ID'], 400);
        }
        
        try {
            // Verify tenant exists and is active
            $tenant = $this->tenantModel->find($tenantId);
            
            if (!$tenant) {
                return $this->json(['success' => false, 'message' => 'Tenant not found'], 404);
            }
            
            if (!$tenant['active'] || !$tenant['verified']) {
                return $this->json(['success' => false, 'message' => 'Tenant is not active or verified'], 400);
            }
            
            // Find the tenant owner/admin user
            $tenantUsers = $this->userModel->getUsersByTenant($tenantId);
            $tenantOwner = null;
            
            foreach ($tenantUsers as $user) {
                if ($user['role'] === 'owner') {
                    $tenantOwner = $user;
                    break;
                } elseif ($user['role'] === 'manager' && !$tenantOwner) {
                    $tenantOwner = $user; // Fallback to manager if no owner found
                }
            }
            
            if (!$tenantOwner) {
                return $this->json(['success' => false, 'message' => 'No tenant owner/admin found'], 404);
            }
            
            // Store current superadmin session info for potential restoration
            $originalUserId = $this->session->get('user_id');
            $originalUserRole = $this->session->get('user_role');
            
            // Log the impersonation action
            $this->auditModel->create([
                'user_id' => $originalUserId,
                'action' => 'tenant_impersonation',
                'details' => json_encode([
                    'tenant_id' => $tenantId,
                    'tenant_name' => $tenant['name'],
                    'impersonated_user_id' => $tenantOwner['id'],
                    'impersonated_user_email' => $tenantOwner['email'],
                    'impersonated_user_role' => $tenantOwner['role']
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            // Switch session to tenant user
            $this->session->set('user_id', $tenantOwner['id']);
            $this->session->set('tenant_id', $tenantId);
            $this->session->set('user_role', $tenantOwner['role']);
            $this->session->set('user_email', $tenantOwner['email']);
            
            // Store original superadmin info for potential restoration
            $this->session->set('original_superadmin_id', $originalUserId);
            $this->session->set('original_superadmin_role', $originalUserRole);
            $this->session->set('is_impersonating', true);
            
            $this->session->regenerate();
            
            // Determine redirect URL based on tenant user role
            $redirectUrl = ORGANIZER_URL;
            if ($tenantOwner['role'] === 'staff') {
                $redirectUrl = ADMIN_URL;
            }
            
            return $this->json([
                'success' => true,
                'message' => 'Successfully switched to tenant account',
                'redirect_url' => $redirectUrl,
                'tenant_name' => $tenant['name'],
                'user_role' => $tenantOwner['role']
            ]);
            
        } catch (\Exception $e) {
            error_log('Login as tenant error: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to switch to tenant: ' . $e->getMessage()], 500);
        }
    }
    
    public function returnToSuperAdmin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Check if currently impersonating
            if (!$this->session->get('is_impersonating')) {
                return $this->json(['success' => false, 'message' => 'Not currently impersonating a tenant'], 400);
            }
            
            $originalUserId = $this->session->get('original_superadmin_id');
            $originalUserRole = $this->session->get('original_superadmin_role');
            
            if (!$originalUserId || !$originalUserRole) {
                return $this->json(['success' => false, 'message' => 'Original superadmin session not found'], 400);
            }
            
            // Get original superadmin user details
            $originalUser = $this->userModel->find($originalUserId);
            if (!$originalUser) {
                return $this->json(['success' => false, 'message' => 'Original superadmin user not found'], 404);
            }
            
            // Log the return action
            $this->auditModel->create([
                'user_id' => $this->session->get('user_id'), // Current impersonated user
                'action' => 'superadmin_return',
                'details' => json_encode([
                    'returned_to_user_id' => $originalUserId,
                    'returned_to_user_email' => $originalUser['email'],
                    'from_tenant_id' => $this->session->get('tenant_id')
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            // Restore original superadmin session
            $this->session->set('user_id', $originalUserId);
            $this->session->set('tenant_id', null);
            $this->session->set('user_role', $originalUserRole);
            $this->session->set('user_email', $originalUser['email']);
            
            // Clear impersonation flags
            $this->session->remove('original_superadmin_id');
            $this->session->remove('original_superadmin_role');
            $this->session->remove('is_impersonating');
            
            $this->session->regenerate();
            
            return $this->json([
                'success' => true,
                'message' => 'Successfully returned to SuperAdmin account',
                'redirect_url' => SUPERADMIN_URL
            ]);
            
        } catch (\Exception $e) {
            error_log('Return to superadmin error: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Failed to return to SuperAdmin: ' . $e->getMessage()], 500);
        }
    }
    
    public function users()
    {
        $users = $this->userModel->findAll([], 'created_at DESC');
        
        $content = $this->renderView('superadmin/users/index', [
            'users' => $users,
            'title' => 'All Users'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Users',
            'breadcrumbs' => [
                ['title' => 'Users']
            ]
        ]);
    }
    
    public function platformAnalytics()
    {
        // Get comprehensive platform analytics
        $analytics = $this->getPlatformAnalytics();
        
        $content = $this->renderView('superadmin/platform/analytics', [
            'analytics' => $analytics,
            'title' => 'Platform Analytics'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Platform Analytics',
            'breadcrumbs' => [
                ['title' => 'Platform', 'url' => SUPERADMIN_URL . '/platform'],
                ['title' => 'Analytics']
            ]
        ]);
    }
    
    public function securityOverview()
    {
        // Get security metrics
        $securityMetrics = $this->getSecurityMetrics();
        
        // Get recent fraud events
        $fraudEvents = $this->fraudModel->getFraudEvents(null, null, 50);
        
        // Get fraud statistics
        $fraudStats = $this->fraudModel->getFraudStats();
        
        $content = $this->renderView('superadmin/security/overview', [
            'securityMetrics' => $securityMetrics,
            'fraudEvents' => $fraudEvents,
            'fraudStats' => $fraudStats,
            'title' => 'Security Overview'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Security Overview',
            'breadcrumbs' => [
                ['title' => 'Security', 'url' => SUPERADMIN_URL . '/security'],
                ['title' => 'Overview']
            ]
        ]);
    }
    
    public function financialOverview()
    {
        // Get platform revenue data
        $revenueData = $this->getRevenueAnalytics();
        
        // Get top performing tenants
        $topTenants = $this->getTopTenants();
        
        $content = $this->renderView('superadmin/financial/overview', [
            'revenueData' => $revenueData,
            'topTenants' => $topTenants,
            'title' => 'Financial Overview'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Financial Overview',
            'breadcrumbs' => [
                ['title' => 'Financial', 'url' => SUPERADMIN_URL . '/financial'],
                ['title' => 'Overview']
            ]
        ]);
    }
    
    
    private function getPlatformStats()
    {
        $stats = [];
        
        // Total tenants
        $stats['total_tenants'] = $this->tenantModel->count();
        $stats['active_tenants'] = $this->tenantModel->count(['active' => 1]);
        $stats['pending_tenants'] = $this->tenantModel->count(['verified' => 0]);
        
        // Total users
        $stats['total_users'] = $this->userModel->count();
        $stats['active_users'] = $this->userModel->count(['active' => 1]);
        
        // Total events
        $stats['total_events'] = $this->eventModel->count();
        $stats['active_events'] = $this->eventModel->count(['status' => 'active']);
        $stats['draft_events'] = $this->eventModel->count(['status' => 'draft']);
        $stats['closed_events'] = $this->eventModel->count(['status' => 'closed']);
        
        // Total transactions
        $stats['total_transactions'] = $this->transactionModel->count();
        $stats['successful_transactions'] = $this->transactionModel->count(['status' => 'success']);
        $stats['pending_transactions'] = $this->transactionModel->count(['status' => 'pending']);
        $stats['failed_transactions'] = $this->transactionModel->count(['status' => 'failed']);
        
        // Platform revenue (corrected calculation)
        $revenueData = $this->getCorrectedPlatformRevenue();
        $stats['total_revenue'] = $revenueData['total_revenue'] ?? 0;
        $stats['total_transaction_amount'] = $revenueData['total_transaction_amount'] ?? 0;
        
        // Monthly revenue
        $monthlyRevenue = $this->getCorrectedPlatformRevenue(
            date('Y-m-01 00:00:00'),
            date('Y-m-t 23:59:59')
        );
        $stats['monthly_revenue'] = $monthlyRevenue['total_revenue'] ?? 0;
        $stats['monthly_transactions'] = $monthlyRevenue['total_transactions'] ?? 0;
        
        // Today's stats
        $todayRevenue = $this->getCorrectedPlatformRevenue(
            date('Y-m-d 00:00:00'),
            date('Y-m-d 23:59:59')
        );
        $stats['today_revenue'] = $todayRevenue['total_revenue'] ?? 0;
        $stats['today_transactions'] = $todayRevenue['total_transactions'] ?? 0;
        
        // Votes and contestants
        $votesQuery = "SELECT COUNT(*) as total_votes, SUM(quantity) as total_vote_count FROM votes";
        $votesData = $this->db->selectOne($votesQuery);
        $stats['total_votes'] = $votesData['total_vote_count'] ?? 0;
        $stats['total_vote_records'] = $votesData['total_votes'] ?? 0;
        
        $stats['total_contestants'] = $this->contestantModel->count(['active' => 1]);
        $stats['total_categories'] = $this->categoryModel->count();
        
        // Tenant balances
        $balanceQuery = "SELECT 
            SUM(available) as total_available,
            SUM(pending) as total_pending,
            SUM(total_paid) as total_paid
            FROM tenant_balances";
        $balanceData = $this->db->selectOne($balanceQuery);
        $stats['pending_payouts'] = $balanceData['total_available'] ?? 0;
        $stats['total_paid_out'] = $balanceData['total_paid'] ?? 0;
        
        // Growth calculations
        $lastMonthStart = date('Y-m-01 00:00:00', strtotime('first day of last month'));
        $lastMonthEnd = date('Y-m-t 23:59:59', strtotime('last day of last month'));
        $lastMonthRevenue = $this->getCorrectedPlatformRevenue($lastMonthStart, $lastMonthEnd);
        $lastMonth = $lastMonthRevenue['total_revenue'] ?? 0;
        $currentMonth = $stats['monthly_revenue'];
        $stats['revenue_growth'] = $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;
        
        return $stats;
    }
    
    // ==================== CONTENT MANAGEMENT METHODS ====================
    
    /**
     * Display all events across all tenants
     */
    public function events()
    {
        $sql = "
            SELECT e.*, 
                   t.name as tenant_name,
                   u.email as created_by_email,
                   COUNT(DISTINCT c.id) as contestant_count,
                   COUNT(DISTINCT cat.id) as category_count,
                   COALESCE(SUM(CASE WHEN tr.status = 'success' THEN tr.amount ELSE 0 END), 0) as total_transaction_amount,
                   COALESCE(SUM(CASE 
                       WHEN tr.status = 'success' 
                       THEN COALESCE(rs.amount, tr.amount * COALESCE(fr.percentage_rate, 15) / 100)
                       ELSE 0 
                   END), 0) as total_revenue,
                   COUNT(DISTINCT CASE WHEN tr.status = 'success' THEN tr.id END) as transaction_count,
                   COALESCE(SUM(CASE WHEN tr.status = 'success' THEN v.quantity ELSE 0 END), 0) as total_votes
            FROM events e
            LEFT JOIN tenants t ON e.tenant_id = t.id
            LEFT JOIN users u ON e.created_by = u.id
            LEFT JOIN contestants c ON e.id = c.event_id AND c.active = 1
            LEFT JOIN categories cat ON e.id = cat.event_id
            LEFT JOIN votes v ON c.id = v.contestant_id
            LEFT JOIN transactions tr ON v.transaction_id = tr.id
            LEFT JOIN revenue_shares rs ON tr.id = rs.transaction_id
            LEFT JOIN subscription_plans sp ON t.current_plan_id = sp.id
            LEFT JOIN fee_rules fr ON sp.fee_rule_id = fr.id
            GROUP BY e.id
            ORDER BY e.created_at DESC
        ";
        
        $events = $this->eventModel->getDatabase()->select($sql);
        
        $content = $this->renderView('superadmin/events/index', [
            'events' => $events,
            'title' => 'Events Management'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Events Management',
            'breadcrumbs' => [
                ['title' => 'Events Management']
            ]
        ]);
    }
    
    /**
     * Show event details
     */
    public function showEvent($eventId)
    {
        $sql = "
            SELECT e.*, 
                   t.name as tenant_name,
                   t.email as tenant_email,
                   u.email as created_by_email,
                   COUNT(DISTINCT c.id) as contestant_count,
                   COUNT(DISTINCT cat.id) as category_count,
                   COALESCE(SUM(CASE WHEN tr.status = 'success' THEN tr.amount ELSE 0 END), 0) as total_revenue,
                   COUNT(DISTINCT tr.id) as transaction_count,
                   COALESCE(SUM(v.quantity), 0) as total_votes
            FROM events e
            LEFT JOIN tenants t ON e.tenant_id = t.id
            LEFT JOIN users u ON e.created_by = u.id
            LEFT JOIN contestants c ON e.id = c.event_id AND c.active = 1
            LEFT JOIN categories cat ON e.id = cat.event_id
            LEFT JOIN votes v ON c.id = v.contestant_id
            LEFT JOIN transactions tr ON v.transaction_id = tr.id
            WHERE e.id = :event_id
            GROUP BY e.id
        ";
        
        $event = $this->eventModel->getDatabase()->selectOne($sql, ['event_id' => $eventId]);
        
        if (!$event) {
            $this->redirect(SUPERADMIN_URL . '/events', 'Event not found', 'error');
            return;
        }
        
        // Get categories for this event
        $categories = $this->categoryModel->getCategoriesByEvent($eventId);
        
        // Get contestants for this event
        $contestants = $this->contestantModel->findAll(['event_id' => $eventId], 'display_order ASC, name ASC');
        
        // Get recent transactions
        $recentTransactions = $this->getEventTransactions($eventId, 20);
        
        $content = $this->renderView('superadmin/events/show', [
            'event' => $event,
            'categories' => $categories,
            'contestants' => $contestants,
            'recentTransactions' => $recentTransactions,
            'title' => 'Event Details: ' . $event['name']
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Event Details',
            'breadcrumbs' => [
                ['title' => 'Events Management', 'url' => SUPERADMIN_URL . '/events'],
                ['title' => $event['name']]
            ]
        ]);
    }
    
    /**
     * Update event status (approve/reject/suspend)
     */
    public function updateEventStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $eventId = $_POST['event_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $adminNotes = $_POST['admin_notes'] ?? '';
        
        if (!$eventId || !$status) {
            return $this->json(['success' => false, 'message' => 'Missing required fields'], 400);
        }
        
        $validStatuses = ['approved', 'pending', 'rejected', 'under_review'];
        if (!in_array($status, $validStatuses)) {
            return $this->json(['success' => false, 'message' => 'Invalid status'], 400);
        }
        
        try {
            $event = $this->eventModel->find($eventId);
            if (!$event) {
                return $this->json(['success' => false, 'message' => 'Event not found'], 404);
            }
            
            // Update event admin status and corresponding event status
            $eventStatus = match($status) {
                'approved' => 'active',
                'rejected' => 'suspended',
                'under_review' => 'draft',
                'pending' => 'draft',
                default => 'draft'
            };
            
            $this->eventModel->update($eventId, [
                'admin_status' => $status,
                'admin_notes' => $adminNotes,
                'status' => $eventStatus
            ]);
            
            // Log the action
            $this->auditModel->create([
                'user_id' => $this->session->getUserId(),
                'action' => 'event_status_updated',
                'details' => json_encode([
                    'event_id' => $eventId,
                    'event_name' => $event['name'],
                    'old_status' => $event['admin_status'],
                    'new_status' => $status,
                    'admin_notes' => $adminNotes
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            return $this->json([
                'success' => true,
                'message' => 'Event status updated successfully',
                'status' => $status
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Display all categories across all events
     */
    public function categories()
    {
        $sql = "
            SELECT cat.*, 
                   e.name as event_name,
                   e.code as event_code,
                   t.name as tenant_name,
                   COUNT(DISTINCT cc.contestant_id) as contestant_count,
                   COALESCE(SUM(v.quantity), 0) as total_votes
            FROM categories cat
            LEFT JOIN events e ON cat.event_id = e.id
            LEFT JOIN tenants t ON cat.tenant_id = t.id
            LEFT JOIN contestant_categories cc ON cat.id = cc.category_id AND cc.active = 1
            LEFT JOIN votes v ON cc.contestant_id = v.contestant_id
            GROUP BY cat.id
            ORDER BY cat.created_at DESC
        ";
        
        $categories = $this->categoryModel->getDatabase()->select($sql);
        
        $content = $this->renderView('superadmin/categories/index', [
            'categories' => $categories,
            'title' => 'Categories Management'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Categories Management',
            'breadcrumbs' => [
                ['title' => 'Categories Management']
            ]
        ]);
    }
    
    /**
     * Show category details
     */
    public function showCategory($categoryId)
    {
        $sql = "
            SELECT cat.*, 
                   e.name as event_name,
                   e.code as event_code,
                   t.name as tenant_name,
                   COUNT(DISTINCT cc.contestant_id) as contestant_count,
                   COALESCE(SUM(v.quantity), 0) as total_votes
            FROM categories cat
            LEFT JOIN events e ON cat.event_id = e.id
            LEFT JOIN tenants t ON cat.tenant_id = t.id
            LEFT JOIN contestant_categories cc ON cat.id = cc.category_id AND cc.active = 1
            LEFT JOIN votes v ON cc.contestant_id = v.contestant_id
            WHERE cat.id = :category_id
            GROUP BY cat.id
        ";
        
        $category = $this->categoryModel->getDatabase()->selectOne($sql, ['category_id' => $categoryId]);
        
        if (!$category) {
            $this->redirect(SUPERADMIN_URL . '/categories', 'Category not found', 'error');
            return;
        }
        
        // Get contestants in this category
        $contestants = $this->getCategoryContestants($categoryId);
        
        $content = $this->renderView('superadmin/categories/show', [
            'category' => $category,
            'contestants' => $contestants,
            'title' => 'Category Details: ' . $category['name']
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Category Details',
            'breadcrumbs' => [
                ['title' => 'Categories Management', 'url' => SUPERADMIN_URL . '/categories'],
                ['title' => $category['name']]
            ]
        ]);
    }
    
    /**
     * Display all contestants across all events
     */
    public function contestants()
    {
        $sql = "
            SELECT c.*, 
                   e.name as event_name,
                   e.code as event_code,
                   t.name as tenant_name,
                   COALESCE(SUM(v.quantity), 0) as total_votes,
                   COALESCE(SUM(CASE WHEN tr.status = 'success' THEN tr.amount ELSE 0 END), 0) as total_revenue,
                   COUNT(DISTINCT tr.id) as transaction_count,
                   GROUP_CONCAT(DISTINCT cat.name SEPARATOR ', ') as categories,
                   GROUP_CONCAT(DISTINCT CONCAT(cat.name, ':', cc.short_code) SEPARATOR '|') as category_shortcodes
            FROM contestants c
            LEFT JOIN events e ON c.event_id = e.id
            LEFT JOIN tenants t ON c.tenant_id = t.id
            LEFT JOIN votes v ON c.id = v.contestant_id
            LEFT JOIN transactions tr ON v.transaction_id = tr.id
            LEFT JOIN contestant_categories cc ON c.id = cc.contestant_id AND cc.active = 1
            LEFT JOIN categories cat ON cc.category_id = cat.id
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ";
        
        $contestants = $this->contestantModel->getDatabase()->select($sql);
        
        $content = $this->renderView('superadmin/contestants/index', [
            'contestants' => $contestants,
            'title' => 'Contestants Management'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Contestants Management',
            'breadcrumbs' => [
                ['title' => 'Contestants Management']
            ]
        ]);
    }
    
    /**
     * Show contestant details
     */
    public function showContestant($contestantId)
    {
        $sql = "
            SELECT c.*, 
                   e.name as event_name,
                   e.code as event_code,
                   t.name as tenant_name,
                   t.email as tenant_email,
                   COALESCE(SUM(v.quantity), 0) as total_votes,
                   COALESCE(SUM(CASE WHEN tr.status = 'success' THEN tr.amount ELSE 0 END), 0) as total_revenue,
                   COUNT(DISTINCT tr.id) as transaction_count
            FROM contestants c
            LEFT JOIN events e ON c.event_id = e.id
            LEFT JOIN tenants t ON c.tenant_id = t.id
            LEFT JOIN votes v ON c.id = v.contestant_id
            LEFT JOIN transactions tr ON v.transaction_id = tr.id
            WHERE c.id = :contestant_id
            GROUP BY c.id
        ";
        
        $contestant = $this->contestantModel->getDatabase()->selectOne($sql, ['contestant_id' => $contestantId]);
        
        if (!$contestant) {
            $this->redirect(SUPERADMIN_URL . '/contestants', 'Contestant not found', 'error');
            return;
        }
        
        // Get categories for this contestant
        $categories = $this->getContestantCategories($contestantId);
        
        // Get recent votes/transactions
        $recentVotes = $this->getContestantVotes($contestantId, 20);
        
        $content = $this->renderView('superadmin/contestants/show', [
            'contestant' => $contestant,
            'categories' => $categories,
            'recentVotes' => $recentVotes,
            'title' => 'Contestant Details: ' . $contestant['name']
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Contestant Details',
            'breadcrumbs' => [
                ['title' => 'Contestants Management', 'url' => SUPERADMIN_URL . '/contestants'],
                ['title' => $contestant['name']]
            ]
        ]);
    }
    
    /**
     * Update contestant status (activate/deactivate)
     */
    public function updateContestantStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $contestantId = $_POST['contestant_id'] ?? null;
        $active = $_POST['active'] ?? null;
        
        if (!$contestantId || $active === null) {
            return $this->json(['success' => false, 'message' => 'Missing required fields'], 400);
        }
        
        try {
            $contestant = $this->contestantModel->find($contestantId);
            if (!$contestant) {
                return $this->json(['success' => false, 'message' => 'Contestant not found'], 404);
            }
            
            // Update contestant status
            $this->contestantModel->update($contestantId, [
                'active' => $active ? 1 : 0
            ]);
            
            // Log the action
            $this->auditModel->create([
                'user_id' => $this->session->getUserId(),
                'action' => 'contestant_status_updated',
                'details' => json_encode([
                    'contestant_id' => $contestantId,
                    'contestant_name' => $contestant['name'],
                    'old_status' => $contestant['active'],
                    'new_status' => $active ? 1 : 0
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            return $this->json([
                'success' => true,
                'message' => 'Contestant status updated successfully',
                'active' => $active ? 1 : 0
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }
    
    // ==================== HELPER METHODS ====================
    
    private function getEventTransactions($eventId, $limit = 20)
    {
        $sql = "
            SELECT tr.*, tr.msisdn as phone, v.quantity, c.name as contestant_name
            FROM transactions tr
            JOIN votes v ON tr.id = v.transaction_id
            JOIN contestants c ON v.contestant_id = c.id
            WHERE c.event_id = :event_id
            ORDER BY tr.created_at DESC
            LIMIT :limit
        ";
        
        return $this->transactionModel->getDatabase()->select($sql, [
            'event_id' => $eventId,
            'limit' => $limit
        ]);
    }
    
    private function getCategoryContestants($categoryId)
    {
        $sql = "
            SELECT c.*, cc.active as category_active,
                   COALESCE(SUM(v.quantity), 0) as total_votes
            FROM contestants c
            JOIN contestant_categories cc ON c.id = cc.contestant_id
            LEFT JOIN votes v ON c.id = v.contestant_id
            WHERE cc.category_id = :category_id
            GROUP BY c.id
            ORDER BY c.display_order ASC, c.name ASC
        ";
        
        return $this->contestantModel->getDatabase()->select($sql, ['category_id' => $categoryId]);
    }
    
    private function getContestantCategories($contestantId)
    {
        $sql = "
            SELECT cat.*, cc.active as contestant_active, cc.short_code
            FROM categories cat
            JOIN contestant_categories cc ON cat.id = cc.category_id
            WHERE cc.contestant_id = :contestant_id
            ORDER BY cat.display_order ASC, cat.name ASC
        ";
        
        return $this->categoryModel->getDatabase()->select($sql, ['contestant_id' => $contestantId]);
    }
    
    private function getContestantVotes($contestantId, $limit = 20)
    {
        $sql = "
            SELECT v.*, tr.amount, tr.status, tr.msisdn as phone, tr.created_at as transaction_date
            FROM votes v
            JOIN transactions tr ON v.transaction_id = tr.id
            WHERE v.contestant_id = :contestant_id
            ORDER BY tr.created_at DESC
            LIMIT :limit
        ";
        
        return $this->contestantModel->getDatabase()->select($sql, [
            'contestant_id' => $contestantId,
            'limit' => $limit
        ]);
    }
    
    // ==================== END CONTENT MANAGEMENT ====================
    
    /**
     * Close expired events automatically
     */
    public function closeExpiredEvents()
    {
        try {
            $currentDateTime = date('Y-m-d H:i:s');
            
            // Find events that should be closed (past end_date but not already closed)
            $sql = "
                SELECT id, name, end_date, status, admin_status
                FROM events 
                WHERE end_date < :current_time 
                AND status IN ('active', 'draft') 
                AND admin_status = 'approved'
            ";
            
            $expiredEvents = $this->eventModel->getDatabase()->select($sql, [
                'current_time' => $currentDateTime
            ]);
            
            $closedCount = 0;
            $errors = [];
            
            foreach ($expiredEvents as $event) {
                try {
                    // Update event status to closed
                    $this->eventModel->update($event['id'], [
                        'status' => 'closed',
                        'closed_at' => $currentDateTime
                    ]);
                    
                    // Log the action
                    $this->auditModel->create([
                        'user_id' => $this->session->getUserId(),
                        'action' => 'event_auto_closed',
                        'details' => json_encode([
                            'event_id' => $event['id'],
                            'event_name' => $event['name'],
                            'end_date' => $event['end_date'],
                            'closed_at' => $currentDateTime
                        ]),
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                    ]);
                    
                    $closedCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Failed to close event '{$event['name']}': " . $e->getMessage();
                }
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // AJAX request
                return $this->json([
                    'success' => true,
                    'message' => "Successfully closed {$closedCount} expired events",
                    'closed_count' => $closedCount,
                    'errors' => $errors
                ]);
            } else {
                // Direct access - redirect back with message
                $message = "Successfully closed {$closedCount} expired events";
                if (!empty($errors)) {
                    $message .= ". Errors: " . implode(', ', $errors);
                }
                $this->redirect(SUPERADMIN_URL . '/events', $message, 'success');
            }
            
        } catch (\Exception $e) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->json(['success' => false, 'message' => 'Error closing events: ' . $e->getMessage()], 500);
            } else {
                $this->redirect(SUPERADMIN_URL . '/events', 'Error closing events: ' . $e->getMessage(), 'error');
            }
        }
    }
    
    /**
     * Get events that should be closed
     */
    public function getExpiredEvents()
    {
        $currentDateTime = date('Y-m-d H:i:s');
        
        $sql = "
            SELECT e.id, e.name, e.end_date, e.status, e.admin_status,
                   t.name as tenant_name,
                   COUNT(DISTINCT c.id) as contestant_count,
                   COALESCE(SUM(v.quantity), 0) as total_votes
            FROM events e
            LEFT JOIN tenants t ON e.tenant_id = t.id
            LEFT JOIN contestants c ON e.id = c.event_id AND c.active = 1
            LEFT JOIN votes v ON c.id = v.contestant_id
            WHERE e.end_date < :current_time 
            AND e.status IN ('active', 'draft') 
            AND e.admin_status = 'approved'
            GROUP BY e.id
            ORDER BY e.end_date DESC
        ";
        
        return $this->eventModel->getDatabase()->select($sql, [
            'current_time' => $currentDateTime
        ]);
    }
    
    private function getPlatformAnalytics()
    {
        // Growth metrics
        $thisMonth = date('Y-m-01');
        $lastMonth = date('Y-m-01', strtotime('-1 month'));
        
        $analytics = [
            'tenant_growth' => $this->getTenantGrowth(),
            'revenue_growth' => $this->getRevenueGrowth(),
            'user_engagement' => $this->getUserEngagement(),
            'event_performance' => $this->getEventPerformance()
        ];
        
        return $analytics;
    }
    
    private function getSecurityMetrics()
    {
        $metrics = [];
        
        // Failed login attempts in last 24 hours
        $since24h = date('Y-m-d H:i:s', time() - 86400);
        $metrics['failed_logins'] = $this->auditModel->count([
            'action' => 'login_failed'
        ]);
        
        // Fraud events in last 7 days
        $since7d = date('Y-m-d H:i:s', time() - (7 * 86400));
        $metrics['fraud_events'] = $this->fraudModel->count();
        
        // Active risk blocks
        $riskBlockModel = new \SmartCast\Models\RiskBlock();
        $metrics['active_blocks'] = $riskBlockModel->count(['active' => 1]);
        
        return $metrics;
    }
    
    private function getRevenueAnalytics()
    {
        // Monthly revenue for the last 12 months
        $revenueByMonth = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $startDate = $month . '-01 00:00:00';
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
            
            $revenue = $this->revenueModel->getPlatformRevenue($startDate, $endDate);
            $revenueByMonth[] = [
                'month' => $month,
                'revenue' => $revenue['total_revenue'] ?? 0
            ];
        }
        
        return [
            'monthly_revenue' => $revenueByMonth,
            'total_revenue' => $this->revenueModel->getPlatformRevenue()
        ];
    }
    
    private function getTopTenants()
    {
        $sql = "
            SELECT t.*, 
                   COUNT(DISTINCT e.id) as event_count,
                   COALESCE(SUM(rs.amount), 0) as total_revenue
            FROM tenants t
            LEFT JOIN events e ON t.id = e.tenant_id
            LEFT JOIN transactions tr ON e.id = tr.event_id AND tr.status = 'success'
            LEFT JOIN revenue_shares rs ON tr.id = rs.transaction_id
            WHERE t.active = 1
            GROUP BY t.id
            ORDER BY total_revenue DESC
            LIMIT 10
        ";
        
        return $this->tenantModel->getDatabase()->select($sql);
    }
    
    private function getTenantGrowth()
    {
        // Tenant registrations by month for last 6 months
        $growth = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $startDate = $month . '-01 00:00:00';
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
            
            $count = $this->tenantModel->getDatabase()->selectOne(
                "SELECT COUNT(*) as count FROM tenants WHERE created_at BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate]
            );
            
            $growth[] = [
                'month' => $month,
                'count' => $count['count'] ?? 0
            ];
        }
        
        return $growth;
    }
    
    private function getRevenueGrowth()
    {
        // Similar to tenant growth but for revenue
        return $this->getRevenueAnalytics()['monthly_revenue'];
    }
    
    private function getUserEngagement()
    {
        // Active users in last 30 days
        $since30d = date('Y-m-d H:i:s', time() - (30 * 86400));
        
        $activeUsers = $this->userModel->getDatabase()->selectOne(
            "SELECT COUNT(DISTINCT user_id) as count FROM audit_logs WHERE created_at >= :since",
            ['since' => $since30d]
        );
        
        return [
            'active_users_30d' => $activeUsers['count'] ?? 0,
            'total_users' => $this->userModel->count()
        ];
    }
    
    private function getEventPerformance()
    {
        // Events created vs events with votes
        $totalEvents = $this->eventModel->count();
        
        $eventsWithVotes = $this->eventModel->getDatabase()->selectOne(
            "SELECT COUNT(DISTINCT event_id) as count FROM votes"
        );
        
        return [
            'total_events' => $totalEvents,
            'events_with_votes' => $eventsWithVotes['count'] ?? 0,
            'engagement_rate' => $totalEvents > 0 ? (($eventsWithVotes['count'] ?? 0) / $totalEvents) * 100 : 0
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
    
    // Platform Management Methods
    public function platformOverview()
    {
        $stats = $this->getPlatformStats();
        $analytics = $this->getPlatformAnalytics();
        
        $content = $this->renderView('superadmin/platform/overview', [
            'stats' => $stats,
            'analytics' => $analytics,
            'title' => 'Platform Overview'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Platform Overview',
            'breadcrumbs' => [
                ['title' => 'Platform', 'url' => SUPERADMIN_URL . '/platform'],
                ['title' => 'Overview']
            ]
        ]);
    }
    
    public function platformPerformance()
    {
        $performance = $this->getPerformanceMetrics();
        
        $content = $this->renderView('superadmin/platform/performance', [
            'performance' => $performance,
            'title' => 'Platform Performance'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Platform Performance',
            'breadcrumbs' => [
                ['title' => 'Platform', 'url' => SUPERADMIN_URL . '/platform'],
                ['title' => 'Performance']
            ]
        ]);
    }
    
    // Tenant Management Methods
    public function pendingTenants()
    {
        // Get tenants that are not verified (pending approval)
        $pendingTenants = $this->tenantModel->findAll(['verified' => 0], 'created_at DESC');
        
        $content = $this->renderView('superadmin/tenants/pending', [
            'pendingTenants' => $pendingTenants,
            'title' => 'Pending Tenant Approvals'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Pending Tenants',
            'breadcrumbs' => [
                ['title' => 'Tenants', 'url' => SUPERADMIN_URL . '/tenants'],
                ['title' => 'Pending Approval']
            ]
        ]);
    }
    
    public function suspendedTenants()
    {
        // Get tenants that are inactive (suspended)
        $suspendedTenants = $this->tenantModel->findAll(['active' => 0], 'updated_at DESC');
        
        $content = $this->renderView('superadmin/tenants/suspended', [
            'suspendedTenants' => $suspendedTenants,
            'title' => 'Suspended Tenants'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Suspended Tenants',
            'breadcrumbs' => [
                ['title' => 'Tenants', 'url' => SUPERADMIN_URL . '/tenants'],
                ['title' => 'Suspended']
            ]
        ]);
    }
    
    public function tenantPlans()
    {
        $planModel = new \SmartCast\Models\SubscriptionPlan();
        $feeRuleModel = new \SmartCast\Models\FeeRule();
        
        $plans = $planModel->getActivePlans();
        $feeRules = $feeRuleModel->findAll(['active' => 1], 'created_at DESC');
        
        // Add usage statistics to each plan
        foreach ($plans as &$plan) {
            $plan['usage_stats'] = $planModel->getPlanUsageStats($plan['id']);
            $plan['features'] = $planModel->getPlanFeatures($plan['id']);
            $plan['can_delete'] = $planModel->canDeletePlan($plan['id']);
        }
        
        $content = $this->renderView('superadmin/tenants/plans', [
            'plans' => $plans,
            'feeRules' => $feeRules,
            'title' => 'Subscription Plans'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Subscription Plans',
            'breadcrumbs' => [
                ['title' => 'Tenants', 'url' => SUPERADMIN_URL . '/tenants'],
                ['title' => 'Plans']
            ]
        ]);
    }
    
    public function createPlan()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $planModel = new \SmartCast\Models\SubscriptionPlan();
                $data = $this->sanitizeInput($_POST);
                
                // Prepare plan data
                $planData = [
                    'name' => $data['name'],
                    'slug' => strtolower(str_replace(' ', '-', $data['name'])),
                    'description' => $data['description'],
                    'price' => floatval($data['price']),
                    'billing_cycle' => $data['billing_cycle'],
                    'max_events' => !empty($data['max_events']) ? intval($data['max_events']) : null,
                    'max_contestants_per_event' => !empty($data['max_contestants_per_event']) ? intval($data['max_contestants_per_event']) : null,
                    'max_votes_per_event' => !empty($data['max_votes_per_event']) ? intval($data['max_votes_per_event']) : null,
                    'fee_rule_id' => !empty($data['fee_rule_id']) ? intval($data['fee_rule_id']) : null,
                    'is_popular' => isset($data['is_popular']) ? 1 : 0,
                    'trial_days' => intval($data['trial_days'] ?? 0),
                    'sort_order' => intval($data['sort_order'] ?? 0)
                ];
                
                // Prepare features
                $features = [];
                if (!empty($data['features'])) {
                    foreach ($data['features'] as $feature) {
                        if (!empty($feature['name'])) {
                            $features[] = [
                                'key' => strtolower(str_replace(' ', '_', $feature['name'])),
                                'name' => $feature['name'],
                                'value' => $feature['value'] ?? null,
                                'is_boolean' => isset($feature['is_boolean']) ? 1 : 0,
                                'sort_order' => intval($feature['sort_order'] ?? 0)
                            ];
                        }
                    }
                }
                
                $planId = $planModel->createPlanWithFeatures($planData, $features);
                
                $this->redirect(SUPERADMIN_URL . '/tenants/plans', 'Plan created successfully', 'success');
                
            } catch (\Exception $e) {
                $this->redirect(SUPERADMIN_URL . '/tenants/plans', 'Failed to create plan: ' . $e->getMessage(), 'error');
            }
        }
    }
    
    public function changeTenantPlan($tenantId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = $this->sanitizeInput($_POST);
                $newPlanId = intval($data['plan_id']);
                $changeReason = $data['change_reason'] ?? 'Changed by admin';
                
                $subscriptionModel = new \SmartCast\Models\TenantSubscription();
                $subscriptionModel->changeSubscription($tenantId, $newPlanId, $changeReason);
                
                // Log the action
                $this->auditModel->create([
                    'user_id' => $this->session->getUserId(),
                    'action' => 'tenant_plan_changed',
                    'details' => json_encode([
                        'tenant_id' => $tenantId,
                        'new_plan_id' => $newPlanId,
                        'reason' => $changeReason
                    ]),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                ]);
                
                $this->redirect(SUPERADMIN_URL . '/tenants', 'Tenant plan changed successfully', 'success');
                
            } catch (\Exception $e) {
                $this->redirect(SUPERADMIN_URL . '/tenants', 'Failed to change plan: ' . $e->getMessage(), 'error');
            }
        }
    }
    
    // User Management Methods
    public function platformAdmins()
    {
        $admins = $this->userModel->findAll(['role' => 'platform_admin'], 'created_at DESC');
        
        $content = $this->renderView('superadmin/users/admins', [
            'admins' => $admins,
            'title' => 'Platform Administrators'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Platform Admins',
            'breadcrumbs' => [
                ['title' => 'Users', 'url' => SUPERADMIN_URL . '/users'],
                ['title' => 'Admins']
            ]
        ]);
    }
    
    public function userActivity()
    {
        $activity = $this->getUserActivityData();
        
        $content = $this->renderView('superadmin/users/activity', [
            'activity' => $activity,
            'title' => 'User Activity Monitoring'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'User Activity',
            'breadcrumbs' => [
                ['title' => 'Users', 'url' => SUPERADMIN_URL . '/users'],
                ['title' => 'Activity']
            ]
        ]);
    }
    
    // Financial Management Methods
    public function platformRevenue()
    {
        $revenue = $this->getRevenueData();
        
        $content = $this->renderView('superadmin/financial/revenue', [
            'revenue' => $revenue,
            'title' => 'Platform Revenue'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Platform Revenue',
            'breadcrumbs' => [
                ['title' => 'Financial', 'url' => SUPERADMIN_URL . '/financial'],
                ['title' => 'Revenue']
            ]
        ]);
    }
    
    public function revenueDistribution()
    {
        $distribution = $this->getRevenueDistributionData();
        
        $content = $this->renderView('superadmin/financial/distribution', [
            'distribution' => $distribution,
            'title' => 'Revenue Distribution'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Revenue Distribution',
            'breadcrumbs' => [
                ['title' => 'Financial', 'url' => SUPERADMIN_URL . '/financial'],
                ['title' => 'Revenue Distribution']
            ]
        ]);
    }
    
    public function allTransactions()
    {
        $transactions = $this->getTransactionData();
        
        $content = $this->renderView('superadmin/financial/transactions', [
            'transactions' => $transactions,
            'title' => 'All Transactions'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'All Transactions',
            'breadcrumbs' => [
                ['title' => 'Financial', 'url' => SUPERADMIN_URL . '/financial'],
                ['title' => 'Transactions']
            ]
        ]);
    }
    
    public function platformPayouts()
    {
        $payouts = $this->getPayoutData();
        
        $content = $this->renderView('superadmin/financial/payouts', [
            'payouts' => $payouts,
            'title' => 'Payouts'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Payouts',
            'breadcrumbs' => [
                ['title' => 'Financial', 'url' => SUPERADMIN_URL . '/financial'],
                ['title' => 'Payouts']
            ]
        ]);
    }
    
    /**
     * Approve a payout request
     */
    public function approvePayout($payoutId)
    {
        try {
            $result = $this->payoutService->approvePayout($payoutId);
            
            if ($result['success']) {
                $this->auditModel->logAction(
                    $this->session->getUserId(),
                    'payout_approved',
                    'payout',
                    $payoutId,
                    "Payout approved: {$result['payout_id']}"
                );
                
                return $this->json([
                    'success' => true,
                    'message' => 'Payout approved successfully',
                    'payout_id' => $result['payout_id']
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'error' => $result['error']
                ], 400);
            }
            
        } catch (\Exception $e) {
            error_log('Payout approval error: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => 'Failed to approve payout'
            ], 500);
        }
    }
    
    /**
     * Reject a payout request
     */
    public function rejectPayout($payoutId)
    {
        try {
            $reason = $_POST['reason'] ?? 'No reason provided';
            $result = $this->payoutService->rejectPayout($payoutId, $reason);
            
            if ($result['success']) {
                $this->auditModel->logAction(
                    $this->session->getUserId(),
                    'payout_rejected',
                    'payout',
                    $payoutId,
                    "Payout rejected: {$reason}"
                );
                
                return $this->json([
                    'success' => true,
                    'message' => 'Payout rejected successfully'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'error' => $result['error']
                ], 400);
            }
            
        } catch (\Exception $e) {
            error_log('Payout rejection error: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => 'Failed to reject payout'
            ], 500);
        }
    }
    
    /**
     * Process multiple payouts at once
     */
    public function processBatchPayouts()
    {
        try {
            $payoutIds = $_POST['payout_ids'] ?? [];
            
            if (empty($payoutIds)) {
                return $this->json([
                    'success' => false,
                    'error' => 'No payouts selected'
                ], 400);
            }
            
            $results = [];
            $successCount = 0;
            $failCount = 0;
            
            foreach ($payoutIds as $payoutId) {
                $result = $this->payoutService->approvePayout($payoutId);
                $results[] = [
                    'payout_id' => $payoutId,
                    'success' => $result['success'],
                    'error' => $result['error'] ?? null
                ];
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }
            
            $this->auditModel->logAction(
                $this->session->getUserId(),
                'batch_payout_processing',
                'payout',
                null,
                "Processed {$successCount} payouts successfully, {$failCount} failed"
            );
            
            return $this->json([
                'success' => true,
                'message' => "Processed {$successCount} payouts successfully" . ($failCount > 0 ? ", {$failCount} failed" : ""),
                'results' => $results,
                'success_count' => $successCount,
                'fail_count' => $failCount
            ]);
            
        } catch (\Exception $e) {
            error_log('Batch payout processing error: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => 'Failed to process batch payouts'
            ], 500);
        }
    }
    
    /**
     * Get payout details for modal view
     */
    public function getPayoutDetails($payoutId)
    {
        try {
            $payout = $this->payoutModel->getPayoutWithDetails($payoutId);
            
            if (!$payout) {
                return $this->json([
                    'success' => false,
                    'error' => 'Payout not found'
                ], 404);
            }
            
            return $this->json([
                'success' => true,
                'payout' => $payout
            ]);
            
        } catch (\Exception $e) {
            error_log('Get payout details error: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => 'Failed to get payout details'
            ], 500);
        }
    }
    
    public function globalFeeRules()
    {
        $fees = $this->getFeeRulesData();
        
        // Get all tenants and plans for the selection dropdowns
        $tenants = $this->tenantModel->findAll([], 'name ASC');
        $planModel = new \SmartCast\Models\SubscriptionPlan();
        $plans = $planModel->findAll(['is_active' => 1], 'sort_order ASC, name ASC');
        
        $content = $this->renderView('superadmin/financial/fees', [
            'fees' => $fees,
            'tenants' => $tenants,
            'plans' => $plans,
            'title' => 'Fee Rules Management'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Global Fee Rules',
            'breadcrumbs' => [
                ['title' => 'Financial', 'url' => SUPERADMIN_URL . '/financial'],
                ['title' => 'Fees']
            ]
        ]);
    }
    
    public function getPlan($planId = null)
    {
        error_log("getPlan called with planId: " . ($planId ?? 'null'));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            $planId = intval($planId ?? 0);
            error_log("Parsed planId: " . $planId);
            
            if (!$planId) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Plan ID is required']);
                return;
            }
            
            $planModel = new \SmartCast\Models\SubscriptionPlan();
            $plan = $planModel->find($planId);
            
            if ($plan) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'plan' => $plan]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Plan not found']);
            }
            
        } catch (\Exception $e) {
            error_log('Plan fetch error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error fetching plan: ' . $e->getMessage()]);
        }
    }
    
    public function getPlanStats($planId = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            $planId = intval($planId ?? 0);
            
            if (!$planId) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Plan ID is required']);
                return;
            }
            
            $planModel = new \SmartCast\Models\SubscriptionPlan();
            $plan = $planModel->find($planId);
            
            if (!$plan) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Plan not found']);
                return;
            }
            
            // Get plan statistics
            $stats = $this->calculatePlanStats($planId);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'plan' => $plan, 'stats' => $stats]);
            
        } catch (\Exception $e) {
            error_log('Plan stats error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error fetching plan statistics: ' . $e->getMessage()]);
        }
    }
    
    public function deletePlan($planId = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(SUPERADMIN_URL . '/tenants/plans', 'Invalid request method', 'error');
            return;
        }
        
        try {
            $planId = intval($planId ?? 0);
            
            if (!$planId) {
                $this->redirect(SUPERADMIN_URL . '/tenants/plans', 'Plan ID is required', 'error');
                return;
            }
            
            $planModel = new \SmartCast\Models\SubscriptionPlan();
            
            // Check if plan can be deleted
            if (!$planModel->canDeletePlan($planId)) {
                $this->redirect(SUPERADMIN_URL . '/tenants/plans', 'Cannot delete plan: Has active subscriptions', 'error');
                return;
            }
            
            $deleted = $planModel->delete($planId);
            
            if ($deleted) {
                $this->redirect(SUPERADMIN_URL . '/tenants/plans', 'Plan deleted successfully!', 'success');
            } else {
                $this->redirect(SUPERADMIN_URL . '/tenants/plans', 'Failed to delete plan', 'error');
            }
            
        } catch (\Exception $e) {
            error_log('Plan deletion error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/tenants/plans', 'Error deleting plan: ' . $e->getMessage(), 'error');
        }
    }
    
    public function exportPlanStats($planId = null)
    {
        try {
            $planId = intval($planId ?? 0);
            
            if (!$planId) {
                $this->redirect(SUPERADMIN_URL . '/tenants/plans', 'Plan ID is required', 'error');
                return;
            }
            
            $planModel = new \SmartCast\Models\SubscriptionPlan();
            $plan = $planModel->find($planId);
            
            if (!$plan) {
                $this->redirect(SUPERADMIN_URL . '/tenants/plans', 'Plan not found', 'error');
                return;
            }
            
            $stats = $this->calculatePlanStats($planId);
            
            // Generate CSV export
            $filename = 'plan_stats_' . $plan['slug'] . '_' . date('Y-m-d') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($output, ['Plan Statistics Report']);
            fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
            fputcsv($output, []);
            fputcsv($output, ['Plan Name', $plan['name']]);
            fputcsv($output, ['Plan Price', '$' . number_format($plan['price'], 2)]);
            fputcsv($output, ['Billing Cycle', $plan['billing_cycle']]);
            fputcsv($output, []);
            fputcsv($output, ['Metric', 'Value']);
            fputcsv($output, ['Active Subscriptions', $stats['active_subscriptions']]);
            fputcsv($output, ['Monthly Revenue', '$' . number_format($stats['monthly_revenue'], 2)]);
            fputcsv($output, ['Total Events', $stats['total_events']]);
            fputcsv($output, ['Churn Rate', $stats['churn_rate'] . '%']);
            fputcsv($output, ['Avg Events per Tenant', $stats['avg_events_per_tenant']]);
            fputcsv($output, ['Total Votes', number_format($stats['total_votes'])]);
            
            fclose($output);
            
        } catch (\Exception $e) {
            error_log('Plan export error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/tenants/plans', 'Error exporting plan statistics: ' . $e->getMessage(), 'error');
        }
    }
    
    private function calculatePlanStats($planId)
    {
        try {
            // Get active subscriptions count
            $activeSubscriptionsSql = "
                SELECT COUNT(*) as count
                FROM tenant_subscriptions ts
                WHERE ts.plan_id = :plan_id
                AND ts.status = 'active'
            ";
            $activeSubscriptions = $this->tenantModel->getDatabase()->selectOne($activeSubscriptionsSql, ['plan_id' => $planId]);
            
            // Get monthly revenue
            $monthlyRevenueSql = "
                SELECT 
                    sp.price * COUNT(ts.id) as monthly_revenue
                FROM subscription_plans sp
                LEFT JOIN tenant_subscriptions ts ON sp.id = ts.plan_id AND ts.status = 'active'
                WHERE sp.id = :plan_id
                GROUP BY sp.id, sp.price
            ";
            $monthlyRevenue = $this->tenantModel->getDatabase()->selectOne($monthlyRevenueSql, ['plan_id' => $planId]);
            
            // Get total events created by tenants on this plan
            $totalEventsSql = "
                SELECT COUNT(e.id) as count
                FROM events e
                INNER JOIN tenants t ON e.tenant_id = t.id
                INNER JOIN tenant_subscriptions ts ON t.id = ts.tenant_id
                WHERE ts.plan_id = :plan_id
                AND ts.status = 'active'
            ";
            $totalEvents = $this->tenantModel->getDatabase()->selectOne($totalEventsSql, ['plan_id' => $planId]);
            
            // Get total votes
            $totalVotesSql = "
                SELECT COALESCE(SUM(v.quantity), 0) as count
                FROM votes v
                INNER JOIN events e ON v.event_id = e.id
                INNER JOIN tenants t ON e.tenant_id = t.id
                INNER JOIN tenant_subscriptions ts ON t.id = ts.tenant_id
                WHERE ts.plan_id = :plan_id
                AND ts.status = 'active'
            ";
            $totalVotes = $this->tenantModel->getDatabase()->selectOne($totalVotesSql, ['plan_id' => $planId]);
            
            // Get recent subscriptions
            $recentSubscriptionsSql = "
                SELECT 
                    ts.created_at,
                    ts.status,
                    t.name as tenant_name
                FROM tenant_subscriptions ts
                INNER JOIN tenants t ON ts.tenant_id = t.id
                WHERE ts.plan_id = :plan_id
                ORDER BY ts.created_at DESC
                LIMIT 10
            ";
            $recentSubscriptions = $this->tenantModel->getDatabase()->select($recentSubscriptionsSql, ['plan_id' => $planId]);
            
            $activeCount = $activeSubscriptions['count'] ?? 0;
            $eventsCount = $totalEvents['count'] ?? 0;
            
            return [
                'active_subscriptions' => $activeCount,
                'monthly_revenue' => $monthlyRevenue['monthly_revenue'] ?? 0,
                'total_events' => $eventsCount,
                'total_votes' => $totalVotes['count'] ?? 0,
                'churn_rate' => 0, // Would need historical data to calculate properly
                'avg_events_per_tenant' => $activeCount > 0 ? round($eventsCount / $activeCount, 1) : 0,
                'avg_contestants_per_event' => 0, // Would need additional query
                'revenue_per_subscription' => $activeCount > 0 ? round(($monthlyRevenue['monthly_revenue'] ?? 0) / $activeCount, 2) : 0,
                'recent_subscriptions' => $recentSubscriptions
            ];
            
        } catch (\Exception $e) {
            error_log('Plan stats calculation error: ' . $e->getMessage());
            return [
                'active_subscriptions' => 0,
                'monthly_revenue' => 0,
                'total_events' => 0,
                'total_votes' => 0,
                'churn_rate' => 0,
                'avg_events_per_tenant' => 0,
                'avg_contestants_per_event' => 0,
                'revenue_per_subscription' => 0,
                'recent_subscriptions' => []
            ];
        }
    }
    
    public function getFeeRulePlanAttachments()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            $ruleId = intval($_GET['rule_id'] ?? 0);
            
            if (!$ruleId) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Fee rule ID is required']);
                return;
            }
            
            // Get plans that use this fee rule
            $planModel = new \SmartCast\Models\SubscriptionPlan();
            $plans = $planModel->findAll(['fee_rule_id' => $ruleId], 'name ASC');
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'plans' => $plans]);
            
        } catch (\Exception $e) {
            error_log('Fee rule plan attachments error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error fetching plan attachments: ' . $e->getMessage()]);
        }
    }
    
    public function createFeeRule()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(SUPERADMIN_URL . '/financial/fees', 'Invalid request method', 'error');
            return;
        }
        
        try {
            $data = $this->sanitizeInput($_POST);
            
            // Validate required fields
            if (empty($data['name']) || empty($data['type']) || empty($data['rate'])) {
                $this->redirect(SUPERADMIN_URL . '/financial/fees', 'Please fill in all required fields', 'error');
                return;
            }
            
            $feeRuleModel = new \SmartCast\Models\FeeRule();
            
            // Determine rule scope and prepare data accordingly
            $eventId = null;
            if (isset($data['rule_scope']) && $data['rule_scope'] === 'event') {
                if (empty($data['event_id'])) {
                    $this->redirect(SUPERADMIN_URL . '/financial/fees', 'Please select an event for event-specific rules', 'error');
                    return;
                }
                $eventId = intval($data['event_id']);
            }
            
            // Prepare fee rule data
            $feeRuleData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'tenant_id' => null, // Always null - we use plan-based approach now
                'event_id' => $eventId, // null for plan/global rules, event_id for event-specific
                'rule_type' => $data['type'],
                'min_amount' => !empty($data['min_amount']) ? floatval($data['min_amount']) : null,
                'max_amount' => !empty($data['max_amount']) ? floatval($data['max_amount']) : null,
                'active' => isset($data['active']) ? 1 : 0
            ];
            
            // Set rate based on type
            if ($data['type'] === 'percentage') {
                $feeRuleData['percentage_rate'] = floatval($data['rate']);
                $feeRuleData['fixed_amount'] = null;
            } elseif ($data['type'] === 'fixed') {
                $feeRuleData['percentage_rate'] = null;
                $feeRuleData['fixed_amount'] = floatval($data['rate']);
            }
            
            // Create the fee rule
            $ruleId = $feeRuleModel->create($feeRuleData);
            
            if ($ruleId) {
                // If this is a plan-based rule, attach it to selected plans
                if (isset($data['rule_scope']) && $data['rule_scope'] === 'plan' && !empty($data['plan_ids'])) {
                    $planModel = new \SmartCast\Models\SubscriptionPlan();
                    $planIds = is_array($data['plan_ids']) ? $data['plan_ids'] : [$data['plan_ids']];
                    
                    foreach ($planIds as $planId) {
                        $planModel->update(intval($planId), ['fee_rule_id' => $ruleId]);
                    }
                    
                    $planCount = count($planIds);
                    $this->redirect(SUPERADMIN_URL . '/financial/fees', "Fee rule created and attached to {$planCount} plan(s) successfully!", 'success');
                } else {
                    $this->redirect(SUPERADMIN_URL . '/financial/fees', 'Fee rule created successfully!', 'success');
                }
            } else {
                $this->redirect(SUPERADMIN_URL . '/financial/fees', 'Failed to create fee rule', 'error');
            }
            
        } catch (\Exception $e) {
            error_log('Fee rule creation error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/financial/fees', 'Error creating fee rule: ' . $e->getMessage(), 'error');
        }
    }
    
    public function updateFeeRule()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            $data = $this->sanitizeInput($_POST);
            
            if (empty($data['rule_id'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Fee rule ID is required']);
                return;
            }
            
            $feeRuleModel = new \SmartCast\Models\FeeRule();
            $ruleId = intval($data['rule_id']);
            
            // Determine rule scope and prepare data accordingly
            $eventId = null;
            if (isset($data['rule_scope']) && $data['rule_scope'] === 'event') {
                if (empty($data['event_id'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Please select an event for event-specific rules']);
                    return;
                }
                $eventId = intval($data['event_id']);
            }
            
            // Prepare update data
            $updateData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'tenant_id' => null, // Always null - we use plan-based approach now
                'event_id' => $eventId, // null for plan/global rules, event_id for event-specific
                'rule_type' => $data['type'],
                'min_amount' => !empty($data['min_amount']) ? floatval($data['min_amount']) : null,
                'max_amount' => !empty($data['max_amount']) ? floatval($data['max_amount']) : null,
                'active' => isset($data['active']) ? 1 : 0
            ];
            
            // Set rate based on type
            if ($data['type'] === 'percentage') {
                $updateData['percentage_rate'] = floatval($data['rate']);
                $updateData['fixed_amount'] = null;
            } elseif ($data['type'] === 'fixed') {
                $updateData['percentage_rate'] = null;
                $updateData['fixed_amount'] = floatval($data['rate']);
            }
            
            $updated = $feeRuleModel->update($ruleId, $updateData);
            
            if ($updated) {
                // Handle plan attachments for plan-based rules
                if (isset($data['rule_scope']) && $data['rule_scope'] === 'plan') {
                    $planModel = new \SmartCast\Models\SubscriptionPlan();
                    
                    // First, remove this rule from all plans
                    $sql = "UPDATE subscription_plans SET fee_rule_id = NULL WHERE fee_rule_id = :rule_id";
                    $planModel->getDatabase()->query($sql, ['rule_id' => $ruleId]);
                    
                    // Then attach to selected plans
                    if (!empty($data['plan_ids'])) {
                        $planIds = is_array($data['plan_ids']) ? $data['plan_ids'] : [$data['plan_ids']];
                        
                        foreach ($planIds as $planId) {
                            $planModel->update(intval($planId), ['fee_rule_id' => $ruleId]);
                        }
                        
                        $planCount = count($planIds);
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => "Fee rule updated and attached to {$planCount} plan(s) successfully!"]);
                        return;
                    } else {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'Fee rule updated (not attached to any plans)']);
                        return;
                    }
                } else {
                    // For non-plan rules, remove from all plans
                    $planModel = new \SmartCast\Models\SubscriptionPlan();
                    $sql = "UPDATE subscription_plans SET fee_rule_id = NULL WHERE fee_rule_id = :rule_id";
                    $planModel->getDatabase()->query($sql, ['rule_id' => $ruleId]);
                    
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Fee rule updated successfully!']);
                    return;
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to update fee rule']);
                return;
            }
            
        } catch (\Exception $e) {
            error_log('Fee rule update error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error updating fee rule: ' . $e->getMessage()]);
            return;
        }
    }
    
    public function toggleFeeRule()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            $data = $this->sanitizeInput($_POST);
            
            if (empty($data['rule_id'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Fee rule ID is required']);
                return;
            }
            
            $feeRuleModel = new \SmartCast\Models\FeeRule();
            $ruleId = intval($data['rule_id']);
            
            // Get current rule
            $rule = $feeRuleModel->find($ruleId);
            if (!$rule) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Fee rule not found']);
                return;
            }
            
            // Toggle active status
            $newStatus = $rule['active'] ? 0 : 1;
            $updated = $feeRuleModel->update($ruleId, ['active' => $newStatus]);
            
            if ($updated) {
                $statusText = $newStatus ? 'activated' : 'deactivated';
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => "Fee rule {$statusText} successfully!"]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to update fee rule status']);
            }
            
        } catch (\Exception $e) {
            error_log('Fee rule toggle error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error updating fee rule: ' . $e->getMessage()]);
        }
    }
    
    public function deleteFeeRule()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            $data = $this->sanitizeInput($_POST);
            
            if (empty($data['rule_id'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Fee rule ID is required']);
                return;
            }
            
            $feeRuleModel = new \SmartCast\Models\FeeRule();
            $ruleId = intval($data['rule_id']);
            
            // Check if rule is being used
            $stats = $feeRuleModel->getFeeRuleStats($ruleId);
            if ($stats && $stats['usage_count'] > 0) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Cannot delete fee rule that has been used in transactions. Deactivate it instead.']);
                return;
            }
            
            $deleted = $feeRuleModel->delete($ruleId);
            
            if ($deleted) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Fee rule deleted successfully!']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to delete fee rule']);
            }
            
        } catch (\Exception $e) {
            error_log('Fee rule deletion error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error deleting fee rule: ' . $e->getMessage()]);
        }
    }
    
    public function getFeeRule()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            $ruleId = intval($_GET['rule_id'] ?? 0);
            
            if (!$ruleId) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Fee rule ID is required']);
                return;
            }
            
            $feeRuleModel = new \SmartCast\Models\FeeRule();
            $rule = $feeRuleModel->find($ruleId);
            
            if ($rule) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'rule' => $rule]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Fee rule not found']);
            }
            
        } catch (\Exception $e) {
            error_log('Fee rule fetch error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error fetching fee rule: ' . $e->getMessage()]);
        }
    }
    
    // Security Methods
    public function fraudDetection()
    {
        $fraud = $this->getFraudData();
        
        $content = $this->renderView('superadmin/security/fraud', [
            'fraud' => $fraud,
            'title' => 'Fraud Detection'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Fraud Detection',
            'breadcrumbs' => [
                ['title' => 'Security', 'url' => SUPERADMIN_URL . '/security'],
                ['title' => 'Fraud Detection']
            ]
        ]);
    }
    
    public function auditLogs()
    {
        $audit = $this->getAuditData();
        
        $content = $this->renderView('superadmin/security/audit', [
            'audit' => $audit,
            'title' => 'Audit Logs'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Audit Logs',
            'breadcrumbs' => [
                ['title' => 'Security', 'url' => SUPERADMIN_URL . '/security'],
                ['title' => 'Audit Logs']
            ]
        ]);
    }
    
    public function riskBlocks()
    {
        $blocks = $this->getRiskBlocksData();
        
        $content = $this->renderView('superadmin/security/blocks', [
            'blocks' => $blocks,
            'title' => 'Risk Blocks'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Risk Blocks',
            'breadcrumbs' => [
                ['title' => 'Security', 'url' => SUPERADMIN_URL . '/security'],
                ['title' => 'Risk Blocks']
            ]
        ]);
    }
    
    // System Administration Methods
    public function globalSettings()
    {
        $settings = $this->getGlobalSettings();
        
        // Load payment gateway configurations
        $paystack = $this->db->selectOne("SELECT * FROM payment_gateways WHERE provider = 'paystack'");
        $paystack_config = $paystack ? array_merge(
            json_decode($paystack['config'], true) ?: [],
            [
                'is_active' => $paystack['is_active'],
                'is_default' => $paystack['is_default'],
                'priority' => $paystack['priority']
            ]
        ) : [];
        
        $hubtel = $this->db->selectOne("SELECT * FROM payment_gateways WHERE provider = 'hubtel'");
        $hubtel_config = $hubtel ? array_merge(
            json_decode($hubtel['config'], true) ?: [],
            [
                'is_active' => $hubtel['is_active'],
                'is_default' => $hubtel['is_default'],
                'priority' => $hubtel['priority']
            ]
        ) : [];
        
        $flutterwave = $this->db->selectOne("SELECT * FROM payment_gateways WHERE provider = 'flutterwave'");
        $flutterwave_config = $flutterwave ? array_merge(
            json_decode($flutterwave['config'], true) ?: [],
            [
                'is_active' => $flutterwave['is_active'],
                'is_default' => $flutterwave['is_default'] ?? 0,
                'priority' => $flutterwave['priority']
            ]
        ) : [];
        
        $content = $this->renderView('superadmin/system/settings', [
            'settings' => $settings,
            'paystack_config' => $paystack_config,
            'hubtel_config' => $hubtel_config,
            'flutterwave_config' => $flutterwave_config,
            'title' => 'Global Settings'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Global Settings',
            'breadcrumbs' => [
                ['title' => 'System', 'url' => SUPERADMIN_URL . '/system'],
                ['title' => 'Settings']
            ]
        ]);
    }
    
    public function systemMaintenance()
    {
        $maintenance = $this->getMaintenanceData();
        
        $content = $this->renderView('superadmin/system/maintenance', [
            'maintenance' => $maintenance,
            'title' => 'System Maintenance'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'System Maintenance',
            'breadcrumbs' => [
                ['title' => 'System', 'url' => SUPERADMIN_URL . '/system'],
                ['title' => 'Maintenance']
            ]
        ]);
    }
    
    public function systemBackups()
    {
        $backups = $this->getBackupData();
        
        $content = $this->renderView('superadmin/system/backups', [
            'backups' => $backups,
            'title' => 'System Backups'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'System Backups',
            'breadcrumbs' => [
                ['title' => 'System', 'url' => SUPERADMIN_URL . '/system'],
                ['title' => 'Backups']
            ]
        ]);
    }
    
    public function systemLogs()
    {
        $logs = $this->getSystemLogsData();
        
        $content = $this->renderView('superadmin/system/logs', [
            'logs' => $logs,
            'title' => 'System Logs'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'System Logs',
            'breadcrumbs' => [
                ['title' => 'System', 'url' => SUPERADMIN_URL . '/system'],
                ['title' => 'Logs']
            ]
        ]);
    }
    
    // API Management Methods
    public function apiOverview()
    {
        $api = $this->getApiData();
        
        $content = $this->renderView('superadmin/api/overview', [
            'api' => $api,
            'title' => 'API Usage Overview'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'API Overview',
            'breadcrumbs' => [
                ['title' => 'API Management', 'url' => SUPERADMIN_URL . '/api'],
                ['title' => 'Overview']
            ]
        ]);
    }
    
    public function apiKeys()
    {
        $keys = $this->getApiKeysData();
        
        $content = $this->renderView('superadmin/api/keys', [
            'keys' => $keys,
            'title' => 'API Keys'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'API Keys',
            'breadcrumbs' => [
                ['title' => 'API Management', 'url' => SUPERADMIN_URL . '/api'],
                ['title' => 'Keys']
            ]
        ]);
    }
    
    public function apiWebhooks()
    {
        $webhooks = $this->getWebhooksData();
        
        $content = $this->renderView('superadmin/api/webhooks', [
            'webhooks' => $webhooks,
            'title' => 'Webhooks'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Webhooks',
            'breadcrumbs' => [
                ['title' => 'API Management', 'url' => SUPERADMIN_URL . '/api'],
                ['title' => 'Webhooks']
            ]
        ]);
    }
    
    // Reports Method
    public function platformReports()
    {
        $reports = $this->getReportsData();
        
        $content = $this->renderView('superadmin/reports', [
            'reports' => $reports,
            'title' => 'Platform Reports'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Platform Reports',
            'breadcrumbs' => [
                ['title' => 'Reports']
            ]
        ]);
    }
    
    /**
     * Update a subscription plan
     */
    public function updatePlan($planId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $planModel = new \SmartCast\Models\SubscriptionPlan();
                $data = $this->sanitizeInput($_POST);
                
                // Prepare plan data
                $planData = [
                    'name' => $data['name'],
                    'slug' => strtolower(str_replace(' ', '-', $data['name'])),
                    'description' => $data['description'],
                    'price' => floatval($data['price']),
                    'billing_cycle' => $data['billing_cycle'],
                    'max_events' => !empty($data['max_events']) ? intval($data['max_events']) : null,
                    'max_contestants_per_event' => !empty($data['max_contestants_per_event']) ? intval($data['max_contestants_per_event']) : null,
                    'max_votes_per_event' => !empty($data['max_votes_per_event']) ? intval($data['max_votes_per_event']) : null,
                    'fee_rule_id' => !empty($data['fee_rule_id']) ? intval($data['fee_rule_id']) : null,
                    'is_popular' => isset($data['is_popular']) ? 1 : 0,
                    'trial_days' => intval($data['trial_days'] ?? 0),
                    'sort_order' => intval($data['sort_order'] ?? 0)
                ];
                
                // Update plan and sync to all subscribers
                $planModel->updatePlanWithSubscriberSync($planId, $planData);
                
                // Log the action
                $this->auditModel->create([
                    'user_id' => $this->session->getUserId(),
                    'action' => 'plan_updated',
                    'details' => json_encode([
                        'plan_id' => $planId,
                        'plan_name' => $planData['name'],
                        'changes_applied_to_subscribers' => true
                    ]),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                ]);
                
                // Check if this is an AJAX request
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
                $isAjax = $isAjax || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Plan updated successfully and changes applied to all subscribers']);
                    return;
                } else {
                    $this->redirect(SUPERADMIN_URL . '/tenants/plans', 'Plan updated successfully and changes applied to all subscribers', 'success');
                }
                
            } catch (\Exception $e) {
                error_log('Plan update error: ' . $e->getMessage());
                
                // Check if this is an AJAX request
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
                $isAjax = $isAjax || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Failed to update plan: ' . $e->getMessage()]);
                    return;
                } else {
                    $this->redirect(SUPERADMIN_URL . '/tenants/plans', 'Failed to update plan: ' . $e->getMessage(), 'error');
                }
            }
        }
    }
    
    // Helper methods for data retrieval
    private function getPerformanceMetrics()
    {
        return [
            'cpu_usage' => 45,
            'memory_usage' => 62,
            'disk_usage' => 38,
            'response_time' => 120,
            'uptime' => 99.9
        ];
    }
    
    private function getSubscriptionPlans()
    {
        return [
            ['id' => 1, 'name' => 'Basic', 'monthly_price' => 29, 'subscriber_count' => 150],
            ['id' => 2, 'name' => 'Professional', 'monthly_price' => 99, 'subscriber_count' => 75],
            ['id' => 3, 'name' => 'Enterprise', 'monthly_price' => 299, 'subscriber_count' => 25]
        ];
    }
    
    private function getUserActivityData()
    {
        return [
            'active_users_today' => 1250,
            'total_sessions' => 3400,
            'avg_session_duration' => '12:34',
            'page_views' => 15600,
            'recent_activities' => []
        ];
    }
    
    private function getRevenueData()
    {
        // Get corrected platform revenue (including calculated fees for transactions without revenue_shares)
        $totalPlatformRevenue = $this->getCorrectedPlatformRevenue();
        $monthlyRevenue = $this->getCorrectedPlatformRevenue(
            date('Y-m-01 00:00:00'),
            date('Y-m-t 23:59:59')
        );
        
        // Get previous month revenue for growth calculation
        $lastMonthStart = date('Y-m-01 00:00:00', strtotime('first day of last month'));
        $lastMonthEnd = date('Y-m-t 23:59:59', strtotime('last day of last month'));
        $lastMonthRevenue = $this->getCorrectedPlatformRevenue($lastMonthStart, $lastMonthEnd);
        
        // Calculate growth rate
        $currentMonth = $monthlyRevenue['total_revenue'] ?? 0;
        $lastMonth = $lastMonthRevenue['total_revenue'] ?? 0;
        $growthRate = $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;
        
        // Get 12-month average
        $monthlyTrends = $this->getMonthlyRevenueTrends(12);
        $avgMonthly = count($monthlyTrends) > 0 ? array_sum(array_column($monthlyTrends, 'revenue')) / count($monthlyTrends) : 0;
        
        // Get tenant payouts
        $totalPayouts = $this->getTotalPayouts();
        
        return [
            'total_revenue' => $totalPlatformRevenue['total_revenue'] ?? 0,
            'monthly_revenue' => $currentMonth,
            'avg_monthly' => $avgMonthly,
            'growth_rate' => round($growthRate, 1),
            'total_payouts' => $totalPayouts,
            'net_profit' => ($totalPlatformRevenue['total_revenue'] ?? 0) - $totalPayouts,
            'active_tenants' => $totalPlatformRevenue['active_tenants'] ?? 0,
            'total_transactions' => $totalPlatformRevenue['total_transactions'] ?? 0,
            'monthly_trends' => $monthlyTrends,
            'top_tenants' => $this->getTopRevenueTenants(),
            'plan_breakdown' => $this->getRevenuePlanBreakdown(),
            'revenue_sources' => $this->getRevenueSources()
        ];
    }
    
    private function getCorrectedPlatformRevenue($startDate = null, $endDate = null)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_transactions,
                SUM(COALESCE(rs.amount, t.amount * COALESCE(fr.percentage_rate, 15) / 100)) as total_revenue,
                COUNT(DISTINCT t.tenant_id) as active_tenants,
                AVG(COALESCE(rs.amount, t.amount * COALESCE(fr.percentage_rate, 15) / 100)) as avg_revenue_per_transaction
            FROM transactions t
            LEFT JOIN revenue_shares rs ON t.id = rs.transaction_id
            INNER JOIN tenants ten ON t.tenant_id = ten.id
            LEFT JOIN subscription_plans sp ON ten.current_plan_id = sp.id
            LEFT JOIN fee_rules fr ON sp.fee_rule_id = fr.id
            WHERE t.status = 'success'
        ";
        
        $params = [];
        
        if ($startDate && $endDate) {
            $sql .= " AND t.created_at BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        return $this->tenantModel->getDatabase()->selectOne($sql, $params);
    }
    
    private function getTotalPayouts()
    {
        $sql = "SELECT COALESCE(SUM(total_paid), 0) as total_payouts FROM tenant_balances";
        $result = $this->tenantModel->getDatabase()->selectOne($sql);
        return $result['total_payouts'] ?? 0;
    }
    
    private function getTransactionData()
    {
        return [
            'total_count' => 5432,
            'total_volume' => 125000,
            'today_count' => 156,
            'success_rate' => 98.5,
            'list' => []
        ];
    }
    
    private function getPayoutData()
    {
        try {
            // Get pending payouts
            $pendingPayouts = $this->payoutModel->getPendingPayouts();
            
            // Get payout statistics
            $stats = $this->payoutModel->getPayoutStatistics();
            
            // Get recent payout history
            $history = $this->payoutModel->getRecentPayouts(50);
            
            // Calculate totals
            $totalPaid = $stats['total_paid'] ?? 0;
            $pendingAmount = $stats['pending_amount'] ?? 0;
            $thisMonth = $stats['this_month_count'] ?? 0;
            $avgPayout = $stats['avg_payout'] ?? 0;
            
            return [
                'total_paid' => $totalPaid,
                'pending_amount' => $pendingAmount,
                'this_month' => $thisMonth,
                'avg_payout' => $avgPayout,
                'pending' => $pendingPayouts,
                'history' => $history
            ];
            
        } catch (\Exception $e) {
            error_log('Super admin payout data error: ' . $e->getMessage());
            return [
                'total_paid' => 0,
                'pending_amount' => 0,
                'this_month' => 0,
                'avg_payout' => 0,
                'pending' => [],
                'history' => []
            ];
        }
    }
    
    private function getFeeRulesData()
    {
        // Get total fees collected from revenue_shares
        $totalCollectedSql = "
            SELECT COALESCE(SUM(rs.amount), 0) as total_collected
            FROM revenue_shares rs
            INNER JOIN transactions t ON rs.transaction_id = t.id
            WHERE t.status = 'success'
        ";
        $totalCollected = $this->tenantModel->getDatabase()->selectOne($totalCollectedSql);
        
        // Get monthly fees (current month)
        $monthlyFeesSql = "
            SELECT COALESCE(SUM(rs.amount), 0) as monthly_fees
            FROM revenue_shares rs
            INNER JOIN transactions t ON rs.transaction_id = t.id
            WHERE t.status = 'success'
            AND MONTH(rs.created_at) = MONTH(NOW())
            AND YEAR(rs.created_at) = YEAR(NOW())
        ";
        $monthlyFees = $this->tenantModel->getDatabase()->selectOne($monthlyFeesSql);
        
        // Get average fee rate
        $avgFeeRateSql = "
            SELECT AVG((rs.amount / t.amount) * 100) as avg_fee_rate
            FROM revenue_shares rs
            INNER JOIN transactions t ON rs.transaction_id = t.id
            WHERE t.status = 'success' AND t.amount > 0
        ";
        $avgFeeRate = $this->tenantModel->getDatabase()->selectOne($avgFeeRateSql);
        
        // Get active fee rules with usage statistics
        $activeRulesSql = "
            SELECT 
                fr.id,
                fr.rule_type,
                fr.percentage_rate,
                fr.fixed_amount,
                fr.tenant_id,
                fr.event_id,
                fr.created_at,
                fr.active,
                t.name as tenant_name,
                e.name as event_name,
                COUNT(rs.id) as usage_count,
                COALESCE(SUM(rs.amount), 0) as total_collected_rule,
                COALESCE(AVG(rs.amount), 0) as avg_fee_amount
            FROM fee_rules fr
            LEFT JOIN tenants t ON fr.tenant_id = t.id
            LEFT JOIN events e ON fr.event_id = e.id
            LEFT JOIN revenue_shares rs ON fr.id = rs.fee_rule_id
            WHERE fr.active = 1
            GROUP BY fr.id
            ORDER BY total_collected_rule DESC, fr.created_at DESC
        ";
        $activeRules = $this->tenantModel->getDatabase()->select($activeRulesSql);
        
        // Format active rules for the view
        $formattedRules = [];
        foreach ($activeRules as $rule) {
            $appliesTo = 'Global (All Tenants)';
            if ($rule['tenant_id'] && $rule['event_id']) {
                $appliesTo = 'Event: ' . $rule['event_name'];
            } elseif ($rule['tenant_id']) {
                $appliesTo = 'Tenant: ' . $rule['tenant_name'];
            }
            
            $rateDisplay = '';
            if ($rule['rule_type'] === 'percentage') {
                $rateDisplay = $rule['percentage_rate'] . '%';
            } elseif ($rule['rule_type'] === 'fixed') {
                $rateDisplay = '$' . number_format($rule['fixed_amount'], 2);
            } elseif ($rule['rule_type'] === 'blend') {
                $rateDisplay = $rule['percentage_rate'] . '% + $' . number_format($rule['fixed_amount'], 2);
            }
            
            $formattedRules[] = [
                'id' => $rule['id'],
                'name' => $this->generateRuleName($rule),
                'description' => $this->generateRuleDescription($rule),
                'type' => $rule['rule_type'],
                'rate' => $rule['rule_type'] === 'percentage' ? $rule['percentage_rate'] : $rule['fixed_amount'],
                'rate_display' => $rateDisplay,
                'applies_to' => $appliesTo,
                'conditions' => $this->generateRuleConditions($rule),
                'created_at' => $rule['created_at'],
                'usage_count' => $rule['usage_count'],
                'total_collected' => $rule['total_collected_rule'],
                'avg_fee_amount' => $rule['avg_fee_amount']
            ];
        }
        
        return [
            'total_collected' => $totalCollected['total_collected'] ?? 0,
            'monthly_fees' => $monthlyFees['monthly_fees'] ?? 0,
            'avg_fee_rate' => round($avgFeeRate['avg_fee_rate'] ?? 0, 1),
            'active_rules' => $formattedRules
        ];
    }
    
    private function generateRuleName($rule)
    {
        if ($rule['tenant_id'] && $rule['event_id']) {
            return 'Event-Specific Rule';
        } elseif ($rule['tenant_id']) {
            return 'Tenant-Specific Rule';
        } else {
            return 'Global Platform Rule';
        }
    }
    
    private function generateRuleDescription($rule)
    {
        $desc = ucfirst($rule['rule_type']) . ' fee rule';
        
        if ($rule['rule_type'] === 'percentage') {
            $desc .= ' (' . $rule['percentage_rate'] . '% of transaction amount)';
        } elseif ($rule['rule_type'] === 'fixed') {
            $desc .= ' ($' . number_format($rule['fixed_amount'], 2) . ' per transaction)';
        } elseif ($rule['rule_type'] === 'blend') {
            $desc .= ' (' . $rule['percentage_rate'] . '% + $' . number_format($rule['fixed_amount'], 2) . ')';
        }
        
        return $desc;
    }
    
    private function generateRuleConditions($rule)
    {
        $conditions = [];
        
        if ($rule['tenant_id']) {
            $conditions[] = 'Tenant: ' . $rule['tenant_name'];
        }
        
        if ($rule['event_id']) {
            $conditions[] = 'Event: ' . $rule['event_name'];
        }
        
        if ($rule['usage_count'] > 0) {
            $conditions[] = 'Used ' . $rule['usage_count'] . ' times';
        }
        
        return implode(', ', $conditions);
    }
    
    private function getFraudData()
    {
        return [
            'detected_today' => 5,
            'prevented_amount' => 2500,
            'detection_rate' => 94.5,
            'false_positive_rate' => 2.1,
            'active_alerts' => [],
            'rules' => [],
            'recent_events' => []
        ];
    }
    
    private function getAuditData()
    {
        return [
            'total_logs' => 15000,
            'today_logs' => 450,
            'critical_events' => 3,
            'unique_users' => 125,
            'logs' => []
        ];
    }
    
    private function getRiskBlocksData()
    {
        return [
            'active_blocks' => 15,
            'ip_blocks' => 8,
            'user_blocks' => 5,
            'blocked_attempts' => 45,
            'active' => [],
            'recent_activity' => []
        ];
    }
    
    private function getGlobalSettings()
    {
        return [
            'platform_name' => 'SmartCast',
            'platform_url' => 'https://smartcast.example.com',
            'default_timezone' => 'UTC',
            'allow_registration' => true,
            'maintenance_mode' => false
        ];
    }
    
    private function getMaintenanceData()
    {
        return [
            'last_backup' => '2024-01-15 02:00:00',
            'system_health' => 'good',
            'scheduled_tasks' => []
        ];
    }
    
    private function getBackupData()
    {
        return [
            'last_backup' => '2024-01-15 02:00:00',
            'backup_size' => '2.5 GB',
            'backups' => []
        ];
    }
    
    private function getSystemLogsData()
    {
        return [
            'error_count' => 12,
            'warning_count' => 45,
            'logs' => []
        ];
    }
    
    private function getApiData()
    {
        return [
            'total_requests' => 15000,
            'success_rate' => 98.5,
            'avg_response_time' => 120,
            'active_keys' => 25,
            'endpoints' => [],
            'top_consumers' => [],
            'popular_endpoints' => [],
            'recent_activity' => []
        ];
    }
    
    private function getApiKeysData()
    {
        return [
            'total_keys' => 25,
            'active_keys' => 22,
            'keys' => []
        ];
    }
    
    private function getWebhooksData()
    {
        return [
            'total_webhooks' => 8,
            'active_webhooks' => 6,
            'webhooks' => []
        ];
    }
    
    private function getReportsData()
    {
        return [
            'recent' => [],
            'scheduled' => []
        ];
    }
    
    private function getRevenueDistributionData()
    {
        // Get real-time revenue distribution analytics
        $totalRevenue = $this->revenueModel->getPlatformRevenue();
        $tenantBalances = $this->getTenantBalancesSummary();
        $recentDistributions = $this->getRecentRevenueDistributions();
        $distributionStats = $this->getDistributionStats();
        $tenantEarningsBreakdown = $this->getTenantEarningsBreakdown();
        
        return [
            'total_platform_revenue' => $distributionStats['total_platform_fees'] ?? 0,
            'total_tenant_earnings' => $distributionStats['total_tenant_earnings'] ?? 0,
            'pending_payouts' => $tenantBalances['total_available'] ?? 0,
            'completed_payouts' => $tenantBalances['total_paid'] ?? 0,
            'active_tenants' => $tenantBalances['tenant_count'] ?? 0,
            'distribution_rate' => $distributionStats['avg_distribution_rate'] ?? 0,
            'recent_distributions' => $recentDistributions,
            'tenant_balances' => $tenantBalances['balances'],
            'tenant_earnings_breakdown' => $tenantEarningsBreakdown,
            'fee_breakdown' => $this->getFeeBreakdown(),
            'distribution_trends' => $this->getDistributionTrends()
        ];
    }
    
    private function getTenantBalancesSummary()
    {
        $sql = "
            SELECT 
                SUM(available) as total_available,
                SUM(pending) as total_pending,
                SUM(total_earned) as total_earned,
                SUM(total_paid) as total_paid,
                COUNT(*) as tenant_count
            FROM tenant_balances
        ";
        
        $summary = $this->tenantModel->getDatabase()->selectOne($sql);
        
        // Get individual tenant balances
        $balancesSql = "
            SELECT tb.*, t.name as tenant_name, t.email as tenant_email
            FROM tenant_balances tb
            INNER JOIN tenants t ON tb.tenant_id = t.id
            WHERE t.active = 1
            ORDER BY tb.available DESC
            LIMIT 10
        ";
        
        $balances = $this->tenantModel->getDatabase()->select($balancesSql);
        
        return array_merge($summary, ['balances' => $balances]);
    }
    
    private function getRecentRevenueDistributions($limit = 20)
    {
        $sql = "
            SELECT 
                t.id,
                COALESCE(rs.amount, t.amount * COALESCE(fr.percentage_rate, 15) / 100) as platform_fee,
                t.created_at,
                t.amount as total_amount,
                (t.amount - COALESCE(rs.amount, t.amount * COALESCE(fr.percentage_rate, 15) / 100)) as tenant_amount,
                ten.name as tenant_name,
                e.name as event_name,
                c.name as contestant_name
            FROM transactions t
            INNER JOIN tenants ten ON t.tenant_id = ten.id
            INNER JOIN events e ON t.event_id = e.id
            INNER JOIN contestants c ON t.contestant_id = c.id
            LEFT JOIN revenue_shares rs ON rs.transaction_id = t.id
            LEFT JOIN subscription_plans sp ON ten.current_plan_id = sp.id
            LEFT JOIN fee_rules fr ON sp.fee_rule_id = fr.id
            WHERE t.status = 'success'
            ORDER BY t.created_at DESC
            LIMIT :limit
        ";
        
        return $this->tenantModel->getDatabase()->select($sql, ['limit' => $limit]);
    }
    
    private function getDistributionStats()
    {
        $sql = "
            SELECT 
                COUNT(*) as total_distributions,
                SUM(t.amount) as total_transaction_amount,
                SUM(COALESCE(rs.amount, t.amount * COALESCE(fr.percentage_rate, 15) / 100)) as total_platform_fees,
                SUM(t.amount - COALESCE(rs.amount, t.amount * COALESCE(fr.percentage_rate, 15) / 100)) as total_tenant_earnings,
                AVG(COALESCE((rs.amount / t.amount) * 100, fr.percentage_rate, 15)) as avg_distribution_rate
            FROM transactions t
            LEFT JOIN revenue_shares rs ON t.id = rs.transaction_id
            INNER JOIN tenants ten ON t.tenant_id = ten.id
            LEFT JOIN subscription_plans sp ON ten.current_plan_id = sp.id
            LEFT JOIN fee_rules fr ON sp.fee_rule_id = fr.id
            WHERE t.status = 'success'
        ";
        
        return $this->tenantModel->getDatabase()->selectOne($sql);
    }
    
    private function getFeeBreakdown()
    {
        $sql = "
            SELECT 
                fr.id,
                fr.rule_type,
                fr.percentage_rate,
                fr.fixed_amount,
                fr.name,
                COUNT(rs.id) as usage_count,
                COALESCE(SUM(rs.amount), 0) as total_collected,
                COALESCE(AVG(rs.amount), 0) as avg_fee_amount
            FROM fee_rules fr
            LEFT JOIN revenue_shares rs ON fr.id = rs.fee_rule_id
            WHERE fr.active = 1
            GROUP BY fr.id, fr.rule_type, fr.percentage_rate, fr.fixed_amount, fr.name
            HAVING usage_count > 0
            ORDER BY total_collected DESC
        ";
        
        return $this->tenantModel->getDatabase()->select($sql);
    }
    
    private function getDistributionTrends($days = 30)
    {
        $trends = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            
            $sql = "
                SELECT 
                    COALESCE(SUM(rs.amount), 0) as platform_fees,
                    COALESCE(SUM(t.amount - rs.amount), 0) as tenant_earnings,
                    COUNT(rs.id) as distribution_count
                FROM revenue_shares rs
                INNER JOIN transactions t ON rs.transaction_id = t.id
                WHERE DATE(rs.created_at) = :date
                AND t.status = 'success'
            ";
            
            $result = $this->tenantModel->getDatabase()->selectOne($sql, ['date' => $date]);
            
            $trends[] = [
                'date' => $date,
                'platform_fees' => floatval($result['platform_fees'] ?? 0),
                'tenant_earnings' => floatval($result['tenant_earnings'] ?? 0),
                'distribution_count' => intval($result['distribution_count'] ?? 0)
            ];
        }
        
        return $trends;
    }
    
    private function getMonthlyRevenueTrends($months = 12)
    {
        $trends = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = date('Y-m-01 00:00:00', strtotime("-{$i} months"));
            $monthEnd = date('Y-m-t 23:59:59', strtotime("-{$i} months"));
            
            $revenue = $this->getCorrectedPlatformRevenue($monthStart, $monthEnd);
            
            $trends[] = [
                'month' => date('M Y', strtotime($monthStart)),
                'month_short' => date('M', strtotime($monthStart)),
                'revenue' => floatval($revenue['total_revenue'] ?? 0),
                'transactions' => intval($revenue['total_transactions'] ?? 0)
            ];
        }
        
        return $trends;
    }
    
    private function getTopRevenueTenants($limit = 10)
    {
        $sql = "
            SELECT 
                t.id,
                t.name,
                t.email,
                COALESCE(sp.name, t.plan) as plan,
                COALESCE(SUM(CASE 
                    WHEN rs.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') 
                    THEN rs.amount 
                    ELSE 0 
                END), 0) as monthly_revenue,
                COALESCE(SUM(rs.amount), 0) as total_revenue
            FROM tenants t
            LEFT JOIN revenue_shares rs ON t.id = rs.tenant_id
            LEFT JOIN subscription_plans sp ON t.current_plan_id = sp.id
            WHERE t.active = 1
            GROUP BY t.id, t.name, t.email, sp.name, t.plan
            HAVING total_revenue > 0
            ORDER BY total_revenue DESC
            LIMIT :limit
        ";
        
        return $this->tenantModel->getDatabase()->select($sql, ['limit' => $limit]);
    }
    
    private function getRevenuePlanBreakdown()
    {
        $sql = "
            SELECT 
                sp.id,
                sp.name,
                COUNT(DISTINCT t.id) as subscriber_count,
                COALESCE(SUM(rs.amount), 0) as revenue
            FROM subscription_plans sp
            LEFT JOIN tenants t ON sp.id = t.current_plan_id AND t.active = 1
            LEFT JOIN revenue_shares rs ON t.id = rs.tenant_id
            WHERE sp.is_active = 1
            GROUP BY sp.id, sp.name
            ORDER BY revenue DESC
        ";
        
        $plans = $this->tenantModel->getDatabase()->select($sql);
        
        // Calculate total revenue for percentage
        $totalRevenue = array_sum(array_column($plans, 'revenue'));
        
        // Add percentage to each plan
        foreach ($plans as &$plan) {
            $plan['revenue'] = floatval($plan['revenue']);
            $plan['percentage'] = $totalRevenue > 0 ? ($plan['revenue'] / $totalRevenue) * 100 : 0;
        }
        
        return $plans;
    }
    
    private function getRevenueSources()
    {
        // Get revenue breakdown by source
        $sql = "
            SELECT 
                'Platform Fees' as source,
                COALESCE(SUM(rs.amount), 0) as amount
            FROM revenue_shares rs
            WHERE rs.revenue_type = 'platform_fee'
            
            UNION ALL
            
            SELECT 
                'Processing Fees' as source,
                COALESCE(SUM(rs.amount), 0) as amount
            FROM revenue_shares rs
            WHERE rs.revenue_type = 'processing_fee'
            
            UNION ALL
            
            SELECT 
                'Other' as source,
                COALESCE(SUM(rs.amount), 0) as amount
            FROM revenue_shares rs
            WHERE rs.revenue_type NOT IN ('platform_fee', 'processing_fee')
        ";
        
        $sources = $this->tenantModel->getDatabase()->select($sql);
        
        // Calculate total for percentages
        $total = array_sum(array_column($sources, 'amount'));
        
        foreach ($sources as &$source) {
            $source['amount'] = floatval($source['amount']);
            $source['percentage'] = $total > 0 ? ($source['amount'] / $total) * 100 : 0;
        }
        
        return array_filter($sources, function($source) {
            return $source['amount'] > 0;
        });
    }
    
    private function getTenantEarningsBreakdown()
    {
        $sql = "
            SELECT 
                t.id as tenant_id,
                t.name as tenant_name,
                COUNT(DISTINCT tr.id) as transaction_count,
                COALESCE(SUM(tr.amount), 0) as total_transaction_amount,
                SUM(COALESCE(rs.amount, tr.amount * COALESCE(fr.percentage_rate, 15) / 100)) as platform_fees,
                SUM(tr.amount - COALESCE(rs.amount, tr.amount * COALESCE(fr.percentage_rate, 15) / 100)) as tenant_earnings,
                AVG(COALESCE((rs.amount / tr.amount) * 100, fr.percentage_rate, 15)) as avg_fee_percentage
            FROM tenants t
            INNER JOIN transactions tr ON t.id = tr.tenant_id AND tr.status = 'success'
            LEFT JOIN revenue_shares rs ON tr.id = rs.transaction_id
            LEFT JOIN subscription_plans sp ON t.current_plan_id = sp.id
            LEFT JOIN fee_rules fr ON sp.fee_rule_id = fr.id
            WHERE t.active = 1
            GROUP BY t.id, t.name
            HAVING transaction_count > 0
            ORDER BY total_transaction_amount DESC
        ";
        
        return $this->tenantModel->getDatabase()->select($sql);
    }
    
    // ===== SMS GATEWAY MANAGEMENT =====

    /**
     * Show SMS gateways management page
     */
    public function smsGateways()
    {
        $gateways = $this->smsGateway->findAll();
        $gatewayStats = [];
        
        // Get statistics for each gateway
        foreach ($gateways as $gateway) {
            $stats = $this->smsGateway->getGatewayStats($gateway['id']);
            $gatewayStats[$gateway['id']] = $stats[0] ?? null;
        }
        
        // Get overall SMS statistics
        $smsStats = $this->smsLog->getStatistics();
        
        $content = $this->renderView('superadmin/sms/gateways', [
            'gateways' => $gateways,
            'gatewayStats' => $gatewayStats,
            'totalSms' => $smsStats['total_sms'] ?? 0,
            'successRate' => $smsStats['success_rate'] ?? 0,
            'title' => 'SMS Gateways'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'SMS Gateways',
            'breadcrumbs' => [
                ['title' => 'SMS Management', 'url' => SUPERADMIN_URL . '/sms'],
                ['title' => 'Gateways']
            ]
        ]);
    }

    /**
     * Show edit SMS gateway form
     */
    public function editSmsGateway($gatewayId)
    {
        $gateway = $this->smsGateway->find($gatewayId);
        if (!$gateway) {
            $this->redirect(SUPERADMIN_URL . '/sms/gateways', 'Gateway not found', 'error');
            return;
        }

        $content = $this->renderView('superadmin/sms/edit-gateway', [
            'gateway' => $gateway,
            'title' => 'Edit SMS Gateway'
        ]);

        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'Edit SMS Gateway',
            'breadcrumbs' => [
                ['title' => 'SMS Management', 'url' => SUPERADMIN_URL . '/sms'],
                ['title' => 'Gateways', 'url' => SUPERADMIN_URL . '/sms/gateways'],
                ['title' => 'Edit Gateway']
            ]
        ]);
    }

    /**
     * Update SMS gateway
     */
    public function updateSmsGateway($gatewayId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $gateway = $this->smsGateway->find($gatewayId);
                if (!$gateway) {
                    $this->json([
                        'success' => false,
                        'message' => 'Gateway not found'
                    ], 404);
                    return;
                }

                $data = [
                    'name' => $_POST['name'] ?? '',
                    'type' => $_POST['type'] ?? '',
                    'api_key' => $_POST['api_key'] ?? '',
                    'sender_id' => $_POST['sender_id'] ?? '',
                    'client_id' => $_POST['client_id'] ?? null,
                    'client_secret' => $_POST['client_secret'] ?? null,
                    'test_phone' => $_POST['test_phone'] ?? null,
                    'priority' => (int)($_POST['priority'] ?? 1),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];

                // Validate required fields
                $required = ['name', 'type', 'api_key', 'sender_id'];
                if ($data['type'] === 'hubtel') {
                    $required[] = 'client_id';
                    $required[] = 'client_secret';
                }

                foreach ($required as $field) {
                    if (empty($data[$field])) {
                        $this->json([
                            'success' => false,
                            'message' => "Field '{$field}' is required"
                        ], 400);
                        return;
                    }
                }

                $this->smsGateway->update($gatewayId, $data);

                $this->json([
                    'success' => true,
                    'message' => 'SMS gateway updated successfully'
                ]);

            } catch (\Exception $e) {
                $this->json([
                    'success' => false,
                    'message' => 'Error updating gateway: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Create new SMS gateway
     */
    public function createSmsGateway()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'type' => $_POST['type'] ?? '',
                    'api_key' => $_POST['api_key'] ?? '',
                    'sender_id' => $_POST['sender_id'] ?? '',
                    'client_id' => $_POST['client_id'] ?? null,
                    'client_secret' => $_POST['client_secret'] ?? null,
                    'test_phone' => $_POST['test_phone'] ?? null,
                    'priority' => (int)($_POST['priority'] ?? 1),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // Validate required fields
                $required = ['name', 'type', 'api_key', 'sender_id'];
                if ($data['type'] === 'hubtel') {
                    $required[] = 'client_id';
                    $required[] = 'client_secret';
                }
                
                foreach ($required as $field) {
                    if (empty($data[$field])) {
                        $this->json([
                            'success' => false,
                            'message' => "Field '{$field}' is required"
                        ], 400);
                        return;
                    }
                }
                
                $gatewayId = $this->smsGateway->create($data);
                
                $this->json([
                    'success' => true,
                    'message' => 'SMS gateway created successfully',
                    'gateway_id' => $gatewayId
                ]);
                
            } catch (\Exception $e) {
                $this->json([
                    'success' => false,
                    'message' => 'Error creating gateway: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Toggle SMS gateway status
     */
    public function toggleSmsGateway($gatewayId)
    {
        try {
            $result = $this->smsGateway->toggleStatus($gatewayId);
            
            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => 'Gateway status updated successfully'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Gateway not found'
                ], 404);
            }
            
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error updating gateway: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete SMS gateway
     */
    public function deleteSmsGateway($gatewayId)
    {
        try {
            $result = $this->smsGateway->delete($gatewayId);
            
            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => 'Gateway deleted successfully'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Gateway not found'
                ], 404);
            }
            
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error deleting gateway: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test SMS gateway
     */
    public function testSmsGateway($gatewayId)
    {
        try {
            $testPhone = $_POST['test_phone'] ?? null;
            $result = $this->smsService->testGateway($gatewayId, $testPhone);
            
            $this->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Test SMS sent successfully' : 'Test failed',
                'details' => $result
            ]);
            
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get SMS statistics
     */
    public function smsStatistics()
    {
        try {
            $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
            $dateTo = $_GET['date_to'] ?? date('Y-m-d');
            
            $stats = $this->smsService->getStatistics($dateFrom, $dateTo);
            $dailyStats = $this->smsLog->getDailyStats(30);
            $gatewayComparison = $this->smsLog->getGatewayComparison($dateFrom, $dateTo);
            
            $this->json([
                'success' => true,
                'data' => [
                    'overview' => $stats,
                    'daily_stats' => $dailyStats,
                    'gateway_comparison' => $gatewayComparison
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error fetching statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get critical alerts for dashboard
     */
    public function getCriticalAlerts()
    {
        try {
            // Mock critical alerts - replace with real implementation
            $alerts = [];

            // Check for system issues
            $systemAlerts = [];
            
            // Check database connection
            try {
                $this->db->selectOne("SELECT 1");
            } catch (\Exception $e) {
                $systemAlerts[] = [
                    'type' => 'error',
                    'message' => 'Database connection issue',
                    'time' => date('Y-m-d H:i:s')
                ];
            }

            // Check for failed SMS in last hour (if SMS tables exist)
            try {
                if ($this->smsLog) {
                    $failedSms = $this->smsLog->count([
                        'status' => 'failed',
                        'created_at' => ['>=', date('Y-m-d H:i:s', strtotime('-1 hour'))]
                    ]);
                    
                    if ($failedSms > 10) {
                        $systemAlerts[] = [
                            'type' => 'warning',
                            'message' => "High SMS failure rate: {$failedSms} failed in last hour",
                            'time' => date('Y-m-d H:i:s')
                        ];
                    }
                }
            } catch (\Exception $e) {
                // SMS tables might not exist yet or other error
            }

            $this->json([
                'success' => true,
                'alerts' => array_merge($alerts, $systemAlerts)
            ]);

        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error fetching alerts: ' . $e->getMessage(),
                'alerts' => []
            ], 500);
        }
    }
    
    /**
     * Delete a category
     */
    public function deleteCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $categoryId = $_POST['category_id'] ?? null;
        
        if (!$categoryId || !is_numeric($categoryId)) {
            return $this->json(['success' => false, 'message' => 'Invalid category ID'], 400);
        }
        
        try {
            $category = $this->categoryModel->find($categoryId);
            if (!$category) {
                return $this->json(['success' => false, 'message' => 'Category not found'], 404);
            }
            
            // Delete contestant associations first
            $this->categoryModel->getDatabase()->execute(
                "DELETE FROM contestant_categories WHERE category_id = :category_id",
                ['category_id' => $categoryId]
            );
            
            // Delete the category
            $this->categoryModel->delete($categoryId);
            
            // Log the action
            $this->auditModel->create([
                'user_id' => $this->session->getUserId(),
                'action' => 'category_deleted',
                'details' => json_encode([
                    'category_id' => $categoryId,
                    'category_name' => $category['name'],
                    'event_id' => $category['event_id']
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            return $this->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Toggle contestant status in category
     */
    public function toggleContestantInCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $contestantId = $_POST['contestant_id'] ?? null;
        $categoryId = $_POST['category_id'] ?? null;
        $active = $_POST['active'] ?? null;
        
        if (!$contestantId || !$categoryId || $active === null) {
            return $this->json(['success' => false, 'message' => 'Missing required fields'], 400);
        }
        
        try {
            // Update contestant category status
            $this->categoryModel->getDatabase()->execute(
                "UPDATE contestant_categories SET active = :active WHERE contestant_id = :contestant_id AND category_id = :category_id",
                [
                    'active' => $active ? 1 : 0,
                    'contestant_id' => $contestantId,
                    'category_id' => $categoryId
                ]
            );
            
            // Log the action
            $this->auditModel->create([
                'user_id' => $this->session->getUserId(),
                'action' => 'contestant_category_toggled',
                'details' => json_encode([
                    'contestant_id' => $contestantId,
                    'category_id' => $categoryId,
                    'active' => $active ? 1 : 0
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            return $this->json([
                'success' => true,
                'message' => 'Contestant category status updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete a contestant
     */
    public function deleteContestant()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $contestantId = $_POST['contestant_id'] ?? null;
        
        if (!$contestantId || !is_numeric($contestantId)) {
            return $this->json(['success' => false, 'message' => 'Invalid contestant ID'], 400);
        }
        
        try {
            $contestant = $this->contestantModel->find($contestantId);
            if (!$contestant) {
                return $this->json(['success' => false, 'message' => 'Contestant not found'], 404);
            }
            
            // Delete related records first
            $this->contestantModel->getDatabase()->execute(
                "DELETE FROM contestant_categories WHERE contestant_id = :contestant_id",
                ['contestant_id' => $contestantId]
            );
            
            $this->contestantModel->getDatabase()->execute(
                "DELETE FROM votes WHERE contestant_id = :contestant_id",
                ['contestant_id' => $contestantId]
            );
            
            // Delete the contestant
            $this->contestantModel->delete($contestantId);
            
            // Log the action
            $this->auditModel->create([
                'user_id' => $this->session->getUserId(),
                'action' => 'contestant_deleted',
                'details' => json_encode([
                    'contestant_id' => $contestantId,
                    'contestant_name' => $contestant['name'],
                    'event_id' => $contestant['event_id']
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            return $this->json([
                'success' => true,
                'message' => 'Contestant deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Save Paystack configuration
     */
    public function savePaystackConfig()
    {
        // Clean any previous output and set JSON header
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'Invalid JSON data', 'input' => $input]);
                exit;
            }
            
            // Build config JSON
            $config = [
                'public_key' => $data['public_key'] ?? '',
                'secret_key' => $data['secret_key'] ?? '',
                'webhook_secret' => $data['webhook_secret'] ?? '',
                'base_url' => 'https://api.paystack.co',
                'currency' => 'GHS'
            ];
            
            // Check if row exists
            $existing = $this->db->selectOne("SELECT id FROM payment_gateways WHERE provider = 'paystack'");
            
            if ($existing) {
                // Update existing row
                $sql = "UPDATE payment_gateways 
                        SET config = :config,
                            is_active = :is_active,
                            is_default = :is_default,
                            priority = :priority,
                            updated_at = NOW()
                        WHERE provider = 'paystack'";
                
                $this->db->query($sql, [
                    'config' => json_encode($config),
                    'is_active' => $data['is_active'] ?? 0,
                    'is_default' => $data['is_default'] ?? 0,
                    'priority' => $data['priority'] ?? 1
                ]);
            } else {
                // Insert new row
                $sql = "INSERT INTO payment_gateways 
                        (name, provider, config, supported_methods, is_active, is_default, priority, created_at, updated_at)
                        VALUES 
                        ('Paystack', 'paystack', :config, :methods, :is_active, :is_default, :priority, NOW(), NOW())";
                
                $this->db->query($sql, [
                    'config' => json_encode($config),
                    'methods' => json_encode(['mobile_money', 'card', 'bank_transfer']),
                    'is_active' => $data['is_active'] ?? 0,
                    'is_default' => $data['is_default'] ?? 0,
                    'priority' => $data['priority'] ?? 1
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Paystack configuration saved successfully'
            ]);
            exit;
            
        } catch (\Exception $e) {
            error_log("Paystack config save error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Failed to save configuration: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    /**
     * Save Hubtel configuration
     */
    public function saveHubtelConfig()
    {
        // Clean any previous output and set JSON header
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'Invalid JSON data', 'input' => $input]);
                exit;
            }
            
            // Build config JSON
            $config = [
                'client_id' => $data['client_id'] ?? '',
                'client_secret' => $data['client_secret'] ?? '',
                'merchant_account' => $data['merchant_account'] ?? '',
                'base_url' => 'https://rmp.hubtel.com',
                'status_check_url' => 'https://api-txnstatus.hubtel.com',
                'currency' => 'GHS',
                'ip_whitelist' => $data['ip_whitelist'] ?? []
            ];
            
            // Check if row exists
            $existing = $this->db->selectOne("SELECT id FROM payment_gateways WHERE provider = 'hubtel'");
            
            if ($existing) {
                // Update existing row
                $sql = "UPDATE payment_gateways 
                        SET config = :config,
                            is_active = :is_active,
                            is_default = :is_default,
                            priority = :priority,
                            updated_at = NOW()
                        WHERE provider = 'hubtel'";
                
                $this->db->query($sql, [
                    'config' => json_encode($config),
                    'is_active' => $data['is_active'] ?? 0,
                    'is_default' => $data['is_default'] ?? 0,
                    'priority' => $data['priority'] ?? 2
                ]);
            } else {
                // Insert new row
                $sql = "INSERT INTO payment_gateways 
                        (name, provider, config, supported_methods, is_active, is_default, priority, created_at, updated_at)
                        VALUES 
                        ('Hubtel', 'hubtel', :config, :methods, :is_active, :is_default, :priority, NOW(), NOW())";
                
                $this->db->query($sql, [
                    'config' => json_encode($config),
                    'methods' => json_encode(['mobile_money', 'card']),
                    'is_active' => $data['is_active'] ?? 0,
                    'is_default' => $data['is_default'] ?? 0,
                    'priority' => $data['priority'] ?? 2
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Hubtel configuration saved successfully'
            ]);
            exit;
            
        } catch (\Exception $e) {
            error_log("Hubtel config save error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Failed to save configuration: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    /**
     * Save Flutterwave configuration
     */
    public function saveFlutterwaveConfig()
    {
        // Clean any previous output and set JSON header
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'Invalid JSON data', 'input' => $input]);
                exit;
            }
            
            // Build config JSON
            $config = [
                'client_id' => $data['client_id'] ?? '',
                'client_secret' => $data['client_secret'] ?? '',
                'encryption_key' => $data['encryption_key'] ?? '',
                'webhook_secret' => $data['webhook_secret'] ?? '',
                'api_url' => $data['api_url'] ?? 'https://api.flutterwave.com',
                'sandbox' => $data['sandbox'] ?? true
            ];
            
            // Check if row exists
            $existing = $this->db->selectOne("SELECT id FROM payment_gateways WHERE provider = 'flutterwave'");
            
            if ($existing) {
                // Update existing row
                $sql = "UPDATE payment_gateways 
                        SET config = :config,
                            is_active = :is_active,
                            priority = :priority,
                            updated_at = NOW()
                        WHERE provider = 'flutterwave'";
                
                $this->db->query($sql, [
                    'config' => json_encode($config),
                    'is_active' => $data['is_active'] ?? 0,
                    'priority' => $data['priority'] ?? 3
                ]);
            } else {
                // Insert new row
                $sql = "INSERT INTO payment_gateways 
                        (name, provider, config, supported_methods, is_active, is_default, priority, created_at, updated_at)
                        VALUES 
                        ('Flutterwave', 'flutterwave', :config, :methods, :is_active, 0, :priority, NOW(), NOW())";
                
                $this->db->query($sql, [
                    'config' => json_encode($config),
                    'methods' => json_encode(['mobile_money', 'card', 'bank_transfer', 'ussd']),
                    'is_active' => $data['is_active'] ?? 0,
                    'priority' => $data['priority'] ?? 3
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Flutterwave configuration saved successfully'
            ]);
            exit;
            
        } catch (\Exception $e) {
            error_log("Flutterwave config save error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Failed to save configuration: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    /**
     * Test Paystack connection
     */
    public function testPaystackConnection()
    {
        error_reporting(0);
        ini_set('display_errors', 0);
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            $secretKey = $data['secret_key'] ?? '';
            
            if (empty($secretKey)) {
                echo json_encode(['success' => false, 'message' => 'Secret key is required']);
                exit;
            }
            
            // Test API call to Paystack
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://api.paystack.co/transaction/verify/invalid_ref',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $secretKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // If we get 404, it means the API key is valid (just the transaction doesn't exist)
            // If we get 401, the API key is invalid
            if ($httpCode === 404) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Paystack API credentials are valid!'
                ]);
            } elseif ($httpCode === 401) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid Paystack API credentials'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Unable to verify credentials. HTTP ' . $httpCode
                ]);
            }
            exit;
            
        } catch (\Exception $e) {
            error_log("Paystack test error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    /**
     * Test Hubtel connection
     */
    public function testHubtelConnection()
    {
        error_reporting(0);
        ini_set('display_errors', 0);
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            $clientId = $data['client_id'] ?? '';
            $clientSecret = $data['client_secret'] ?? '';
            
            if (empty($clientId) || empty($clientSecret)) {
                echo json_encode(['success' => false, 'message' => 'Client ID and Secret are required']);
                exit;
            }
            
            // Test API call to Hubtel (check if credentials work)
            $authString = base64_encode("{$clientId}:{$clientSecret}");
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://api-txnstatus.hubtel.com/transactions/test/status?clientReference=test',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Basic ' . $authString,
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // If we get 200 or 404, credentials are valid
            // If we get 401 or 403, credentials are invalid or IP not whitelisted
            if ($httpCode === 200 || $httpCode === 404) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Hubtel API credentials are valid!'
                ]);
            } elseif ($httpCode === 401) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid Hubtel API credentials'
                ]);
            } elseif ($httpCode === 403) {
                echo json_encode([
                    'success' => false,
                    'message' => 'IP address not whitelisted. Contact Hubtel support.'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Unable to verify credentials. HTTP ' . $httpCode
                ]);
            }
            exit;
            
        } catch (\Exception $e) {
            error_log("Hubtel test error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}
