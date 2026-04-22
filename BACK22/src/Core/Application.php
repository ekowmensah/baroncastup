<?php

namespace SmartCast\Core;

use SmartCast\Core\Router;
use SmartCast\Core\Database;
use SmartCast\Core\Session;

/**
 * Main Application Class
 */
class Application
{
    private $router;
    private $database;
    private $session;
    
    public function __construct()
    {
        $this->initializeDatabase();
        $this->initializeSession();
        $this->initializeRouter();
        $this->setupRoutes();
    }
    
    private function initializeDatabase()
    {
        $this->database = new Database();
    }
    
    private function initializeSession()
    {
        $this->session = new Session();
    }
    
    private function initializeRouter()
    {
        $this->router = new Router();
    }
    
    private function setupRoutes()
    {
        // Public routes
        $this->router->get('/', 'HomeController@index');
        $this->router->get('/about', 'HomeController@about');
        $this->router->get('/pricing', 'AuthController@showPricing');
        $this->router->get('/login', 'AuthController@showLogin');
        $this->router->post('/login', 'AuthController@login');
        $this->router->get('/register', 'AuthController@showRegister');
        $this->router->post('/register', 'AuthController@register');
        $this->router->post('/logout', 'AuthController@logout');
        
        // Event routes
        $this->router->get('/events', 'EventController@index');
        $this->router->get('/events/{eventSlug}/nominate', 'EventController@showNominationForm');
        $this->router->post('/events/{eventSlug}/nominate', 'EventController@submitNomination');
        $this->router->get('/events/{eventSlug}', 'EventController@show');
        // Voting routes - Support both slug and ID
        $this->router->get('/events/{eventSlug}/vote', 'VoteController@showVoting'); // Nominee selection page
        $this->router->get('/events/{eventSlug}/vote/{contestantSlug}', 'VoteController@showVoteForm'); // Vote form for specific nominee
        $this->router->post('/events/{eventSlug}/vote/process', 'VoteController@processVote'); // Process the vote
        
        // Short URL voting - Direct to contestant by voting shortcode
        $this->router->get('/vote/{shortcode}', 'VoteController@voteByShortcode'); // e.g., /vote/JB-232
        
        // Shortcode voting - now uses same processing as standard voting
        $this->router->get('/vote-shortcode', 'VoteController@showShortcodeVoting'); // Shortcode voting page
        $this->router->get('/vote', 'VoteController@showDirectVoting'); // Direct voting with parameters
        $this->router->post('/vote/process', 'VoteController@processVote'); // Process direct vote using same logic as standard voting
        
        // OTP routes for payment verification
        $this->router->post('/api/otp/send-payment-otp', 'OtpController@sendPaymentOtp'); // Send OTP for payment
        $this->router->post('/api/otp/verify-payment-otp', 'OtpController@verifyPaymentOtp'); // Verify OTP for payment
        
        // Payment routes
        $this->router->get('/payment/status/{transactionId}', 'VoteController@showPaymentStatus'); // Payment status page
        $this->router->get('/api/payment/status/{transactionId}', 'VoteController@checkPaymentStatus'); // Check payment status
        $this->router->get('/api/payment/test/{transactionId}', 'VoteController@testPaymentStatus'); // Test payment processing
        $this->router->post('/api/payment/callback/{transactionId}', 'VoteController@handlePaymentCallback'); // Handle payment callbacks
        $this->router->get('/api/payment/callback/{transactionId}', 'VoteController@handlePaymentCallback'); // Handle GET payment callbacks from Paystack
        $this->router->get('/api/payment/simulate/{transactionId}', 'VoteController@simulatePaymentCallback'); // Simulate payment callback for testing
        $this->router->get('/api/payment/receipt/{transactionId}', 'VoteController@getPaymentReceipt'); // Get payment receipt
        $this->router->get('/payment/receipt/{transactionId}', 'VoteController@showPaymentReceipt'); // Show payment receipt page
        $this->router->get('/verify-receipt', 'VoteController@showReceiptVerification'); // Show receipt verification page
        
        // Payment Gateway Webhooks
        $this->router->post('/api/payment/webhook/paystack', 'VoteController@paystackWebhook'); // Paystack webhook
        $this->router->post('/api/payment/webhook/hubtel', 'VoteController@hubtelWebhook'); // Hubtel webhook
        $this->router->post('/api/payment/webhook/flutterwave', 'VoteController@flutterwaveWebhook'); // Flutterwave webhook
        
        // USSD routes
        $this->router->post('/api/ussd/callback', 'UssdController@handleRequest'); // Handle USSD requests from Hubtel (Service Interaction + Service Fulfillment)
        $this->router->get('/api/ussd/callback', 'UssdController@handleRequest'); // Handle USSD requests (GET fallback)
        $this->router->post('/api/ussd/fulfillment', 'UssdController@handleServiceFulfillment'); // Explicit Hubtel Service Fulfillment URL
        $this->router->post('/api/ussd/fulfilment', 'UssdController@handleServiceFulfillment'); // Alternate spelling for Service Fulfilment URL
        $this->router->post('/verify-receipt', 'VoteController@processReceiptVerification'); // Process receipt verification
        
        // Organizer routes (protected)
        $this->router->group(['middleware' => 'auth'], function($router) {
            $router->get('/organizer', 'OrganizerController@dashboard');
        });
        
        $this->router->group(['prefix' => '/organizer', 'middleware' => 'auth'], function($router) {
            $router->get('/', 'OrganizerController@dashboard');
            $router->get('/subscribe', 'OrganizerController@subscribeToPlan');
            $router->post('/subscribe', 'OrganizerController@subscribeToPlan');
            $router->get('/switch-plan', 'OrganizerController@switchPlan');
            $router->post('/switch-plan', 'OrganizerController@switchPlan');
            
            // Events - Specific routes MUST come before parameterized routes
            $router->get('/events', 'OrganizerController@events');
            $router->get('/events/create', 'OrganizerController@createEvent');
            $router->get('/events/wizard', 'OrganizerController@createEventWizard');
            $router->get('/events/{id}/shortcode-demo', 'OrganizerController@shortcodeDemo');
            $router->get('/shortcode-stats', 'OrganizerController@shortcodeStats');
            $router->get('/migrate-shortcodes', 'OrganizerController@migrateShortcodes');
            $router->post('/migrate-shortcodes', 'OrganizerController@migrateShortcodes');
            $router->get('/events/{id}/categories', 'OrganizerController@getEventCategories');
            $router->post('/events', 'OrganizerController@storeEventWizard'); // Redirect old route to wizard
            $router->post('/events/wizard', 'OrganizerController@storeEventWizard');
            // Parameterized routes MUST come last
            $router->get('/events/{id}/nominations', 'OrganizerController@nominations');
            $router->get('/events/{id}', 'OrganizerController@showEvent');
            $router->get('/events/{id}/preview', 'OrganizerController@previewEvent');
            $router->get('/events/{id}/edit', 'OrganizerController@editEvent');
            $router->post('/events/{id}/edit', 'OrganizerController@editEvent');
            $router->get('/events/{id}/export-pdf', 'OrganizerController@exportEventPDF');
            $router->post('/events/{id}/publish', 'OrganizerController@publishEvent');
            $router->post('/events/{id}/toggle-results', 'OrganizerController@toggleResults');
            $router->post('/events/{id}/toggle-nominations', 'OrganizerController@toggleNominations');
            $router->post('/events/{id}/update-status', 'OrganizerController@updateEventStatus');
            
            // Contestants
            $router->get('/nominations', 'OrganizerController@nominations');
            $router->post('/nominations/{id}/update', 'OrganizerController@updateNomination');
            $router->post('/nominations/{id}/approve', 'OrganizerController@approveNomination');
            $router->post('/nominations/{id}/revoke', 'OrganizerController@revokeNomination');
            $router->post('/nominations/{id}/reject', 'OrganizerController@rejectNomination');
            $router->get('/contestants', 'OrganizerController@contestants');
            $router->get('/contestants/create', 'OrganizerController@createContestant');
            $router->post('/contestants', 'OrganizerController@storeContestant');
            $router->get('/contestants/{id}', 'OrganizerController@showContestant');
            $router->get('/contestants/{id}/edit', 'OrganizerController@editContestant');
            $router->post('/contestants/{id}/edit', 'OrganizerController@updateContestant');
            $router->get('/contestants/{id}/stats', 'OrganizerController@contestantStats');
            
            // Payout routes
            $router->get('/payouts', 'PayoutController@dashboard');
            $router->get('/payouts/request', 'PayoutController@requestPayout');
            $router->post('/payouts/request', 'PayoutController@requestPayout');
            $router->get('/payouts/history', 'PayoutController@history');
            $router->get('/payouts/details/{id}', 'PayoutController@details');
            $router->get('/payouts/methods', 'PayoutController@methods');
            $router->post('/payouts/methods', 'PayoutController@methods');
            $router->get('/payouts/add-method', 'PayoutController@addMethod');
            $router->post('/payouts/add-method', 'PayoutController@addMethod');
            $router->get('/payouts/settings', 'PayoutController@settings');
            $router->post('/payouts/settings', 'PayoutController@settings');
            $router->post('/payouts/{id}/cancel', 'PayoutController@cancelPayout');
            $router->get('/payouts/{id}/receipt', 'PayoutController@downloadReceipt');
            
            // Payout API routes
            $router->get('/api/payouts/balance', 'PayoutController@apiGetBalance');
            $router->post('/api/payouts/calculate-fees', 'PayoutController@apiCalculateFees');
            
            // Categories
            $router->get('/categories', 'OrganizerController@categories');
            $router->post('/categories', 'OrganizerController@storeCategory');
            $router->get('/categories/{id}', 'OrganizerController@showCategory');
            $router->post('/categories/{id}', 'OrganizerController@updateCategory');
            $router->delete('/categories/{id}', 'OrganizerController@deleteCategory');
            
            // API endpoints for organizer
            $router->get('/api/events/{id}/categories', 'OrganizerController@getEventCategories');
            $router->post('/api/categories/reorder', 'OrganizerController@reorderCategories');
            $router->post('/api/contestants/reorder', 'OrganizerController@reorderContestants');
            
            // Voting
            $router->get('/voting/live', 'OrganizerController@liveResults');
            $router->get('/voting/analytics', 'OrganizerController@votingAnalytics');
            $router->get('/voting/receipts', 'OrganizerController@votingReceipts');
            
            // Financial
            $router->get('/financial/overview', 'OrganizerController@financialOverview');
            $router->get('/financial/revenue', 'OrganizerController@revenueDashboard');
            $router->get('/financial/transactions', 'OrganizerController@transactions');
            $router->get('/financial/payouts', 'OrganizerController@payouts');
            $router->get('/financial/bundles', 'OrganizerController@bundles');
            $router->post('/financial/bundles/create', 'OrganizerController@createBundle');
            $router->post('/financial/bundles/{id}/update', 'OrganizerController@updateBundle');
            $router->post('/financial/bundles/{id}/delete', 'OrganizerController@deleteBundle');
            
            // Marketing
            $router->get('/marketing/coupons', 'OrganizerController@coupons');
            $router->get('/marketing/referrals', 'OrganizerController@referrals');
            $router->get('/marketing/campaigns', 'OrganizerController@campaigns');
            
            // Reports
            $router->get('/reports', 'OrganizerController@reports');
            
            // Settings
            $router->get('/settings/organization', 'OrganizerController@organizationSettings');
            $router->post('/settings/organization', 'OrganizerController@updateOrganizationSettings');
            $router->get('/settings/users', 'OrganizerController@userSettings');
            $router->post('/settings/users', 'OrganizerController@updateUserSettings');
            $router->get('/settings/integrations', 'OrganizerController@integrationSettings');
            $router->post('/settings/integrations', 'OrganizerController@updateIntegrationSettings');
            $router->get('/settings/security', 'OrganizerController@securitySettings');
            $router->post('/settings/security', 'OrganizerController@updateSecuritySettings');
            $router->get('/settings/ussd', 'UssdManagementController@organizerSettings');
            $router->post('/settings/ussd/update', 'UssdManagementController@updateOrganizerSettings');
        });
        
        // Admin routes (protected)
        $this->router->group(['prefix' => '/admin', 'middleware' => 'auth'], function($router) {
            $router->get('/', 'AdminController@dashboard');
            $router->get('/events', 'AdminController@events');
            $router->get('/events/create', 'AdminController@createEvent');
            $router->post('/events', 'AdminController@storeEvent');
            $router->get('/events/{id}/edit', 'AdminController@editEvent');
            $router->put('/events/{id}', 'AdminController@updateEvent');
            $router->delete('/events/{id}', 'AdminController@deleteEvent');
            
            $router->get('/contestants', 'AdminController@contestants');
            $router->get('/contestants/create', 'AdminController@createContestant');
            $router->post('/contestants', 'AdminController@storeContestant');
            
            $router->get('/categories', 'AdminController@categories');
            $router->post('/categories', 'AdminController@storeCategory');
            
            $router->get('/users', 'AdminController@users');
            $router->get('/reports', 'AdminController@reports');
        });
        
        // Super Admin routes (protected)
        $this->router->group(['middleware' => 'auth'], function($router) {
            $router->get('/superadmin', 'SuperAdminController@dashboard');
        });
        
        $this->router->group(['prefix' => '/superadmin', 'middleware' => 'auth'], function($router) {
            $router->get('/', 'SuperAdminController@dashboard');
            
            // Platform Management
            $router->get('/platform/overview', 'SuperAdminController@platformOverview');
            $router->get('/platform/analytics', 'SuperAdminController@platformAnalytics');
            $router->get('/platform/performance', 'SuperAdminController@platformPerformance');
            $router->get('/tenants', 'SuperAdminController@tenants');
            $router->get('/tenants/pending', 'SuperAdminController@pendingTenants');
            $router->get('/tenants/suspended', 'SuperAdminController@suspendedTenants');
            $router->get('/tenants/plans', 'SuperAdminController@tenantPlans');
            $router->post('/tenants/plans/create', 'SuperAdminController@createPlan');
            $router->post('/tenants/plans/{id}/update', 'SuperAdminController@updatePlan');
            $router->get('/tenants/plans/{id}/get', 'SuperAdminController@getPlan');
            $router->get('/tenants/plans/{id}/stats', 'SuperAdminController@getPlanStats');
            $router->get('/tenants/plans/{id}/export', 'SuperAdminController@exportPlanStats');
            $router->post('/tenants/plans/{id}/delete', 'SuperAdminController@deletePlan');
            $router->post('/tenants/{id}/change-plan', 'SuperAdminController@changeTenantPlan');
            $router->post('/tenants/{id}/reactivate', 'SuperAdminController@reactivateTenant');
            $router->post('/tenants/{id}/login-as', 'SuperAdminController@loginAsTenant');
            $router->post('/return-to-superadmin', 'SuperAdminController@returnToSuperAdmin');
            // Tenant Approval System
            $router->post('/tenants/approve', 'SuperAdminController@approveTenant');
            $router->post('/tenants/reject', 'SuperAdminController@rejectTenant');
            
            // User Management
            $router->get('/users', 'SuperAdminController@users');
            $router->get('/users/admins', 'SuperAdminController@platformAdmins');
            $router->get('/users/activity', 'SuperAdminController@userActivity');
            
            // Content Management
            $router->get('/events', 'SuperAdminController@events');
            $router->get('/events/{id}', 'SuperAdminController@showEvent');
            $router->post('/events/update-status', 'SuperAdminController@updateEventStatus');
            $router->get('/events/close-expired', 'SuperAdminController@closeExpiredEvents');
            $router->post('/events/close-expired', 'SuperAdminController@closeExpiredEvents');
            
            $router->get('/categories', 'SuperAdminController@categories');
            $router->get('/categories/{id}', 'SuperAdminController@showCategory');
            $router->post('/categories/delete', 'SuperAdminController@deleteCategory');
            $router->post('/categories/toggle-contestant', 'SuperAdminController@toggleContestantInCategory');
            
            $router->get('/contestants', 'SuperAdminController@contestants');
            $router->get('/contestants/{id}', 'SuperAdminController@showContestant');
            $router->post('/contestants/update-status', 'SuperAdminController@updateContestantStatus');
            $router->post('/contestants/delete', 'SuperAdminController@deleteContestant');
            
            // Financial Management
            $router->get('/financial/overview', 'SuperAdminController@financialOverview');
            $router->get('/financial/revenue', 'SuperAdminController@platformRevenue');
            $router->get('/financial/distribution', 'SuperAdminController@revenueDistribution');
            $router->get('/financial/transactions', 'SuperAdminController@allTransactions');
            $router->get('/financial/payouts', 'SuperAdminController@platformPayouts');
            $router->post('/financial/payouts/{id}/approve', 'SuperAdminController@approvePayout');
            $router->post('/financial/payouts/{id}/reject', 'SuperAdminController@rejectPayout');
            $router->post('/financial/payouts/batch-process', 'SuperAdminController@processBatchPayouts');
            $router->get('/financial/payouts/{id}/details', 'SuperAdminController@getPayoutDetails');
            
            // New Comprehensive Payout Management System
            $router->get('/payouts', 'SuperAdmin\\PayoutController@index');
            $router->get('/payouts/pending', 'SuperAdmin\\PayoutController@pending');
            $router->get('/payouts/approve/{id}', 'SuperAdmin\\PayoutController@approve');
            $router->get('/payouts/reject/{id}', 'SuperAdmin\\PayoutController@reject');
            $router->post('/payouts/process-approval/{id}', 'SuperAdmin\\PayoutController@processApproval');
            $router->post('/payouts/process-rejection/{id}', 'SuperAdmin\\PayoutController@processRejection');
            $router->get('/payouts/reverse-processed/{id}', 'SuperAdmin\\PayoutController@reverseProcessed');
            $router->post('/payouts/reverse-processed/{id}', 'SuperAdmin\\PayoutController@processReverseProcessed');
            $router->get('/payouts/reverse-approved/{id}', 'SuperAdmin\\PayoutController@reverseApproved');
            $router->post('/payouts/reverse-approved/{id}', 'SuperAdmin\\PayoutController@processReverseApproved');
            $router->post('/payouts/recalculate-fees/{id}', 'SuperAdmin\\PayoutController@recalculateFees');
            $router->get('/payouts/debug/{id}', 'SuperAdmin\\PayoutController@debugPayoutMethods');
            $router->post('/payouts/bulk-approve', 'SuperAdmin\\PayoutController@bulkApprove');
            
            // USSD Management
            $router->get('/ussd', 'UssdManagementController@superAdminDashboard');
            $router->post('/ussd/assign', 'UssdManagementController@assignUssdCode');
            $router->post('/ussd/revoke', 'UssdManagementController@revokeUssdCode');
            $router->post('/ussd/toggle', 'UssdManagementController@toggleUssdStatus');
            $router->post('/payouts/process/{id}', 'SuperAdmin\\PayoutController@process');
            $router->get('/payouts/details/{id}', 'SuperAdmin\\PayoutController@details');
            $router->get('/payouts/{id}/receipt', 'SuperAdmin\\PayoutController@downloadReceipt');
            $router->get('/payouts/history', 'SuperAdmin\\PayoutController@history');
            $router->get('/api/payouts/stats', 'SuperAdmin\\PayoutController@apiStats');
            $router->get('/financial/fees', 'SuperAdminController@globalFeeRules');
            $router->post('/financial/fees/create', 'SuperAdminController@createFeeRule');
            $router->post('/financial/fees/update', 'SuperAdminController@updateFeeRule');
            $router->post('/financial/fees/toggle', 'SuperAdminController@toggleFeeRule');
            $router->post('/financial/fees/delete', 'SuperAdminController@deleteFeeRule');
            $router->get('/financial/fees/get', 'SuperAdminController@getFeeRule');
            $router->get('/financial/fees/plan-attachments', 'SuperAdminController@getFeeRulePlanAttachments');
            
            // Security & Compliance
            $router->get('/security/overview', 'SuperAdminController@securityOverview');
            $router->get('/security/fraud', 'SuperAdminController@fraudDetection');
            $router->get('/security/audit', 'SuperAdminController@auditLogs');
            $router->get('/security/blocks', 'SuperAdminController@riskBlocks');
            
            // System Administration
            $router->get('/system/settings', 'SuperAdminController@globalSettings');
            $router->get('/system/maintenance', 'SuperAdminController@systemMaintenance');
            $router->get('/system/backups', 'SuperAdminController@systemBackups');
            $router->get('/system/logs', 'SuperAdminController@systemLogs');
            
            // Payment Gateway API
            $router->post('/api/payment-gateways/paystack', 'SuperAdminController@savePaystackConfig');
            $router->post('/api/payment-gateways/hubtel', 'SuperAdminController@saveHubtelConfig');
            $router->post('/api/payment-gateways/flutterwave', 'SuperAdminController@saveFlutterwaveConfig');
            $router->post('/api/payment-gateways/test/paystack', 'SuperAdminController@testPaystackConnection');
            $router->post('/api/payment-gateways/test/hubtel', 'SuperAdminController@testHubtelConnection');
            
            // API Management
            $router->get('/api/overview', 'SuperAdminController@apiOverview');
            $router->get('/api/keys', 'SuperAdminController@apiKeys');
            $router->get('/api/webhooks', 'SuperAdminController@apiWebhooks');
            
            // SMS Gateway Management
            $router->get('/sms/gateways', 'SuperAdminController@smsGateways');
            $router->post('/sms/gateways', 'SuperAdminController@createSmsGateway');
            $router->get('/sms/gateways/{id}/edit', 'SuperAdminController@editSmsGateway');
            $router->post('/sms/gateways/{id}/update', 'SuperAdminController@updateSmsGateway');
            $router->post('/sms/gateways/{id}/toggle', 'SuperAdminController@toggleSmsGateway');
            $router->delete('/sms/gateways/{id}', 'SuperAdminController@deleteSmsGateway');
            $router->post('/sms/gateways/{id}/test', 'SuperAdminController@testSmsGateway');
            $router->get('/sms/statistics', 'SuperAdminController@smsStatistics');
            
            // Bulk SMS routes
            $router->get('/bulk-sms', 'BulkSmsController@index');
            $router->get('/bulk-sms/compose', 'BulkSmsController@compose');
            $router->post('/bulk-sms/preview-recipients', 'BulkSmsController@previewRecipients');
            $router->post('/bulk-sms/send', 'BulkSmsController@send');
            $router->get('/bulk-sms/template-preview', 'BulkSmsController@templatePreview');
            $router->get('/bulk-sms/templates', 'BulkSmsController@templates');
            $router->post('/bulk-sms/templates/save', 'BulkSmsController@saveTemplate');
            
            // Reports
            $router->get('/reports', 'SuperAdminController@platformReports');
            
            $router->get('/alerts/critical', 'SuperAdminController@getCriticalAlerts');
        });
        
        // API routes
        $this->router->group(['prefix' => '/api'], function($router) {
            $router->get('/events', 'Api\\EventController@index');
            $router->get('/events/{id}', 'Api\\EventController@show');
            $router->get('/events/{id}/results', 'Api\\EventController@results');
            $router->get('/events/search', 'Api\\EventController@search');
            
            $router->post('/vote', 'Api\\VoteController@vote');
            $router->get('/vote/verify', 'Api\\VoteController@verifyReceipt');
            $router->get('/vote/bundles', 'Api\\VoteController@getBundles');
            $router->get('/vote/contestants', 'Api\\VoteController@getContestants');
            
            // SMS API routes
            $router->post('/sms/vote-confirmation', 'SmsController@sendVoteConfirmation');
            
            // Shortcode API routes
            $router->get('/shortcode-test', 'Api\\ShortcodeController@test');
            $router->post('/shortcode-lookup', 'Api\\ShortcodeController@lookup');
            $router->post('/shortcode-validate', 'Api\\ShortcodeController@validateShortcode');
            $router->get('/events/{id}/shortcodes', 'Api\\ShortcodeController@getEventShortcodes');
            
            // Shortcode Generator API routes (for event wizard)
            $router->post('/shortcode/generate-preview', 'Api\\ShortcodeGeneratorController@generatePreview');
            $router->post('/shortcode/validate-availability', 'Api\\ShortcodeGeneratorController@validateShortcode');
        });
    }
    
    public function run()
    {
        try {
            $this->router->dispatch();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    private function handleException(\Exception $e)
    {
        if (APP_DEBUG) {
            echo '<h1>Error</h1>';
            echo '<p>' . $e->getMessage() . '</p>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        } else {
            http_response_code(500);
            echo '<h1>Internal Server Error</h1>';
        }
    }
    
    public function getDatabase()
    {
        return $this->database;
    }
    
    public function getSession()
    {
        return $this->session;
    }
}
