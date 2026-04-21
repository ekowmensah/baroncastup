<?php

namespace SmartCast\Controllers;

use SmartCast\Models\User;
use SmartCast\Models\Tenant;
use SmartCast\Models\TenantSetting;
use SmartCast\Models\AuditLog;
use SmartCast\Models\SubscriptionPlan;
use SmartCast\Models\TenantSubscription;

/**
 * Authentication Controller
 */
class AuthController extends BaseController
{
    private $userModel;
    private $tenantModel;
    private $auditModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->tenantModel = new Tenant();
        $this->auditModel = new AuditLog();
    }
    
    public function showLogin()
    {
        if ($this->session->isLoggedIn()) {
            $this->redirect('/admin');
        }
        
        // Get flash messages
        $flashMessages = [
            'success' => $this->session->flash('success'),
            'info' => $this->session->flash('info'),
            'warning' => $this->session->flash('warning'),
            'error' => $this->session->flash('error')
        ];
        
        $this->view('auth/login', ['flashMessages' => $flashMessages]);
    }
    
    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validate input
        $errors = $this->validateInput($_POST, [
            'email' => ['required' => true, 'email' => true],
            'password' => ['required' => true, 'min' => 6]
        ]);
        
        if (!empty($errors)) {
            $this->view('auth/login', ['errors' => $errors, 'email' => $email]);
            return;
        }
        
        // Find user by email
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
            // Log failed login attempt
            $this->auditModel->logFailedLogin($email, $_SERVER['REMOTE_ADDR']);
            
            $this->view('auth/login', [
                'error' => 'Invalid email or password',
                'email' => $email
            ]);
            return;
        }
        
        if (!$user['active']) {
            $this->view('auth/login', [
                'error' => 'Your account has been deactivated',
                'email' => $email
            ]);
            return;
        }
        
        // Check if tenant is verified (approved by SuperAdmin)
        if ($user['tenant_id'] && $user['role'] !== 'platform_admin') {
            $tenant = $this->tenantModel->find($user['tenant_id']);
            if (!$tenant || !$tenant['verified']) {
                $this->view('auth/login', [
                    'error' => 'Your organization is pending approval. Please wait for admin verification or contact support.',
                    'email' => $email,
                    'pending_approval' => true
                ]);
                return;
            }
        }
        
        // Login successful
        $this->session->set('user_id', $user['id']);
        $this->session->set('tenant_id', $user['tenant_id']);
        $this->session->set('user_role', $user['role']);
        $this->session->set('user_email', $user['email']);
        $this->session->regenerate();
        
        // Update last login
        $this->userModel->updateLastLogin($user['id']);
        
        // Log successful login
        $this->auditModel->logLogin($user['id'], $email);
        
        // Redirect based on role
        switch ($user['role']) {
            case 'platform_admin':
                $this->redirect(SUPERADMIN_URL);
                break;
            case 'owner':
            case 'manager':
                $this->redirect(ORGANIZER_URL);
                break;
            case 'staff':
            default:
                $this->redirect(ADMIN_URL);
                break;
        }
    }
    
    public function showRegister()
    {
        if ($this->session->isLoggedIn()) {
            $this->redirect('/admin');
        }
        
        // Get available subscription plans for display
        $planModel = new SubscriptionPlan();
        $plans = $planModel->getPlansForPricing();
        
        // Check if a plan was pre-selected from pricing page
        $selectedPlanId = $_GET['plan'] ?? null;
        
        $this->view('auth/register', [
            'plans' => $plans,
            'selectedPlanId' => $selectedPlanId
        ]);
    }
    
    public function register()
    {
        $data = $this->sanitizeInput($_POST);
        
        // Validate input
        $errors = $this->validateInput($data, [
            'organization' => ['required' => true, 'min' => 3, 'max' => 255],
            'email' => ['required' => true, 'email' => true],
            'phone' => ['required' => true, 'min' => 10, 'max' => 15],
            'password' => ['required' => true, 'min' => 8],
            'confirm_password' => ['required' => true],
            'plan_id' => ['required' => true]
        ]);
        
        // Check password confirmation
        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        // Check if email already exists (both user and tenant)
        if ($this->userModel->findByEmail($data['email'])) {
            $errors['email'] = 'Email already exists';
        }
        
        // Check if tenant email already exists
        $existingTenant = $this->tenantModel->findAll(['email' => $data['email']], null, 1);
        if (!empty($existingTenant)) {
            $errors['email'] = 'An organization with this email already exists';
        }
        
        // Check if tenant phone already exists
        if (!empty($data['phone'])) {
            $existingTenantPhone = $this->tenantModel->findAll(['phone' => $data['phone']], null, 1);
            if (!empty($existingTenantPhone)) {
                $errors['phone'] = 'An organization with this phone number already exists';
            }
        }
        
        // Validate phone number format (Ghana/Nigeria)
        if (isset($data['phone']) && !empty($data['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $data['phone']); // Remove non-numeric characters
            
            // Check for valid Ghana/Nigeria phone formats
            if (!preg_match('/^(233|234|0)[0-9]{9,10}$/', $phone)) {
                $errors['phone'] = 'Please enter a valid Ghana or Nigeria phone number';
            }
        }
        
        // Validate selected plan
        $planModel = new SubscriptionPlan();
        $selectedPlan = $planModel->find($data['plan_id']);
        if (!$selectedPlan || !$selectedPlan['is_active']) {
            $errors['plan_id'] = 'Invalid subscription plan selected';
        }
        
        if (!empty($errors)) {
            // Get plans again for the form
            $plans = $planModel->getPlansForPricing();
            
            $this->view('auth/register', [
                'errors' => $errors,
                'data' => $data,
                'plans' => $plans
            ]);
            return;
        }
        
        try {
            // Create tenant first
            $tenantData = [
                'name' => $data['organization'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'plan' => $selectedPlan['slug'],
                'current_plan_id' => $selectedPlan['id'],
                'subscription_status' => 'trial',
                'trial_ends_at' => $selectedPlan['trial_days'] > 0 ? 
                    date('Y-m-d H:i:s', strtotime('+' . $selectedPlan['trial_days'] . ' days')) : null,
                'active' => 1,
                'verified' => 0,
                'ussd_code' => '999', // Shared USSD code for all tenants (*711*999#)
                'ussd_enabled' => 0 // Disabled by default, can be enabled later
            ];
            
            $tenantId = $this->tenantModel->create($tenantData);
            
            // Create user
            $userId = $this->userModel->createUser([
                'tenant_id' => $tenantId,
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'owner'
            ]);
            
            // Create subscription for the tenant
            $subscriptionModel = new TenantSubscription();
            $subscriptionModel->createSubscription($tenantId, $selectedPlan['id'], $selectedPlan['billing_cycle']);
            
            // Initialize default tenant settings
            $settingsModel = new TenantSetting();
            $settingsModel->initializeDefaultSettings($tenantId);
            
            // Log registration
            $this->auditModel->create([
                'user_id' => $userId,
                'action' => 'user_registered',
                'details' => json_encode([
                    'tenant_id' => $tenantId,
                    'plan_id' => $selectedPlan['id'],
                    'plan_name' => $selectedPlan['name']
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            // Send super admin notification about new tenant registration
            try {
                // Use factory to get the best available email service
                $emailService = \SmartCast\Services\EmailServiceFactory::create();
                
                $tenantNotificationData = [
                    'name' => $data['organization'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'plan' => $selectedPlan['name'],
                    'tenant_id' => $tenantId,
                    'user_id' => $userId
                ];
                
                $emailResult = $emailService->sendNewTenantNotificationToSuperAdmin($tenantNotificationData);
                
                if ($emailResult['success']) {
                    error_log("Super admin notification sent successfully for new tenant: " . $data['organization']);
                } else {
                    error_log("Failed to send super admin notification for new tenant: " . $emailResult['error']);
                }
            } catch (\Exception $e) {
                error_log("Error sending super admin notification: " . $e->getMessage());
                // Don't fail the registration if email fails
            }
            
            $message = 'Registration successful! Your account has been created and is pending administrative approval. ';
            if ($selectedPlan['trial_days'] > 0) {
                $message .= 'Once approved, you will receive ' . $selectedPlan['trial_days'] . ' days of free trial access. ';
            }
            $message .= 'You will be notified via email when your account has been approved and is ready for use.';
            
            $this->redirect('/login', $message, 'success');
            
        } catch (\Exception $e) {
            // Get plans again for error display
            $plans = $planModel->getPlansForPricing();
            $this->view('auth/register', [
                'error' => 'Registration failed: ' . $e->getMessage(),
                'data' => $data,
                'plans' => $plans
            ]);
        }
    }
    
    public function showPricing()
    {
        // Get available subscription plans for display
        $planModel = new SubscriptionPlan();
        $plans = $planModel->getPlansForPricing();
        
        $this->view('pricing', [
            'plans' => $plans
        ]);
    }
    
    public function logout()
    {
        $userId = $this->session->getUserId();
        
        if ($userId) {
            // Verify user exists before logging logout
            $user = $this->userModel->find($userId);
            if ($user) {
                // Log logout only if user exists
                $this->auditModel->logLogout($userId);
            }
        }
        
        $this->session->destroy();
        $this->redirect('/login', 'You have been logged out successfully', 'success');
    }
}
