<?php

namespace SmartCast\Controllers;

use SmartCast\Models\Tenant;
use SmartCast\Models\UssdSession;
use SmartCast\Helpers\UssdHelper;

/**
 * USSD Management Controller
 * Handles USSD code assignment and management for super admin and organizers
 */
class UssdManagementController extends BaseController
{
    private $tenantModel;
    private $ussdSessionModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->tenantModel = new Tenant();
        $this->ussdSessionModel = new UssdSession();
    }
    
    /**
     * Super Admin: USSD Code Management Dashboard
     */
    public function superAdminDashboard()
    {
        // Get all tenants with USSD info
        $tenants = $this->tenantModel->findAll([], 'created_at DESC');
        
        // Get USSD statistics
        $stats = $this->getUssdStatistics();
        
        // Get available USSD codes (01-99)
        $availableCodes = $this->getAvailableUssdCodes();
        
        $content = $this->renderView('superadmin/ussd/dashboard', [
            'tenants' => $tenants,
            'stats' => $stats,
            'availableCodes' => $availableCodes,
            'title' => 'USSD Management'
        ]);
        
        echo $this->renderLayout('superadmin_layout', $content, [
            'title' => 'USSD Management',
            'breadcrumbs' => [
                ['title' => 'System', 'url' => SUPERADMIN_URL . '/system'],
                ['title' => 'USSD Management']
            ]
        ]);
    }
    
    /**
     * Super Admin: Assign USSD Code to Tenant
     */
    public function assignUssdCode()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $tenantId = $_POST['tenant_id'] ?? null;
                $ussdCode = $_POST['ussd_code'] ?? null;
                $welcomeMessage = $_POST['welcome_message'] ?? null;
                
                if (!$tenantId || !$ussdCode) {
                    throw new \Exception('Tenant ID and USSD code are required');
                }
                
                // Validate USSD code format (1-999)
                if (!preg_match('/^\d{1,3}$/', $ussdCode) || $ussdCode < 1 || $ussdCode > 999) {
                    throw new \Exception('USSD code must be between 1 and 999');
                }
                
                // Check if code is already taken
                $existing = $this->tenantModel->findAll(['ussd_code' => $ussdCode], null, 1);
                if (!empty($existing) && $existing[0]['id'] != $tenantId) {
                    $fullCode = UssdHelper::formatUssdCode($ussdCode);
                    throw new \Exception("USSD code {$fullCode} is already assigned to another tenant");
                }
                
                // Update tenant
                $this->tenantModel->update($tenantId, [
                    'ussd_code' => $ussdCode,
                    'ussd_enabled' => 1,
                    'ussd_welcome_message' => $welcomeMessage
                ]);
                
                $fullCode = UssdHelper::formatUssdCode($ussdCode);
                $this->jsonResponse([
                    'success' => true,
                    'message' => "USSD code {$fullCode} assigned successfully"
                ]);
                
            } catch (\Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        }
    }
    
    /**
     * Super Admin: Revoke USSD Code from Tenant
     */
    public function revokeUssdCode()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $tenantId = $_POST['tenant_id'] ?? null;
                
                if (!$tenantId) {
                    throw new \Exception('Tenant ID is required');
                }
                
                // Update tenant
                $this->tenantModel->update($tenantId, [
                    'ussd_code' => null,
                    'ussd_enabled' => 0
                ]);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'USSD code revoked successfully'
                ]);
                
            } catch (\Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        }
    }
    
    /**
     * Super Admin: Toggle USSD Status
     */
    public function toggleUssdStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $tenantId = $_POST['tenant_id'] ?? null;
                $enabled = $_POST['enabled'] ?? 0;
                
                if (!$tenantId) {
                    throw new \Exception('Tenant ID is required');
                }
                
                $this->tenantModel->update($tenantId, [
                    'ussd_enabled' => $enabled ? 1 : 0
                ]);
                
                $status = $enabled ? 'enabled' : 'disabled';
                $this->jsonResponse([
                    'success' => true,
                    'message' => "USSD {$status} successfully"
                ]);
                
            } catch (\Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        }
    }
    
    /**
     * Organizer: USSD Settings Page
     */
    public function organizerSettings()
    {
        $tenantId = $this->session->get('tenant_id');
        if (!$tenantId) {
            $this->redirect(ORGANIZER_URL, 'Session error. Please login again.', 'error');
            return;
        }
        
        $tenant = $this->tenantModel->find($tenantId);
        
        // Get USSD statistics for this tenant
        $stats = $this->getTenantUssdStatistics($tenantId);
        
        $content = $this->renderView('organizer/settings/ussd', [
            'tenant' => $tenant,
            'stats' => $stats,
            'title' => 'USSD Settings'
        ]);
        
        echo $this->renderLayout('organizer_layout', $content, [
            'title' => 'USSD Settings',
            'breadcrumbs' => [
                ['title' => 'Settings', 'url' => ORGANIZER_URL . '/settings'],
                ['title' => 'USSD']
            ]
        ]);
    }
    
    /**
     * Organizer: Update USSD Settings
     */
    public function updateOrganizerSettings()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Get tenant ID from session
                if (!$this->session->isLoggedIn()) {
                    throw new \Exception('Not authenticated');
                }
                
                $tenantId = $this->session->get('tenant_id');
                if (!$tenantId) {
                    throw new \Exception('Tenant ID not found in session');
                }
                
                $tenant = $this->tenantModel->find($tenantId);
                if (!$tenant) {
                    throw new \Exception('Tenant not found');
                }
                
                // Only allow updating welcome message, not the code
                $welcomeMessage = $_POST['ussd_welcome_message'] ?? '';
                
                // Log for debugging
                error_log("USSD Update - Tenant ID: {$tenantId}");
                error_log("USSD Update - Welcome Message: {$welcomeMessage}");
                error_log("USSD Update - Current Message: " . ($tenant['ussd_welcome_message'] ?? 'NULL'));
                
                // Update tenant
                $updated = $this->tenantModel->update($tenantId, [
                    'ussd_welcome_message' => $welcomeMessage
                ]);
                
                error_log("USSD Update - Result: " . ($updated ? 'SUCCESS' : 'FAILED'));
                
                // Verify the update
                $updatedTenant = $this->tenantModel->find($tenantId);
                error_log("USSD Update - New Message: " . ($updatedTenant['ussd_welcome_message'] ?? 'NULL'));
                
                if ($updated !== false) {
                    $this->redirect(ORGANIZER_URL . '/settings/ussd', 'USSD settings updated successfully', 'success');
                } else {
                    throw new \Exception('Failed to update settings');
                }
                
            } catch (\Exception $e) {
                error_log('USSD Settings Update Error: ' . $e->getMessage());
                $this->redirect(ORGANIZER_URL . '/settings/ussd', 'Error: ' . $e->getMessage(), 'error');
            }
        }
    }
    
    /**
     * Get USSD Statistics
     */
    private function getUssdStatistics()
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT t.id) as total_tenants,
                COUNT(DISTINCT CASE WHEN t.ussd_code IS NOT NULL THEN t.id END) as assigned_tenants,
                COUNT(DISTINCT CASE WHEN t.ussd_enabled = 1 THEN t.id END) as active_tenants,
                COUNT(DISTINCT us.session_id) as total_sessions,
                COUNT(DISTINCT us.msisdn) as unique_users
            FROM tenants t
            LEFT JOIN ussd_sessions us ON us.tenant_id = t.id
        ";
        
        return $this->tenantModel->getDatabase()->selectOne($sql);
    }
    
    /**
     * Get Tenant-specific USSD Statistics
     */
    private function getTenantUssdStatistics($tenantId)
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT session_id) as total_sessions,
                COUNT(DISTINCT msisdn) as unique_users,
                COUNT(DISTINCT CASE WHEN state = 'success' THEN session_id END) as successful_votes,
                MAX(created_at) as last_session
            FROM ussd_sessions
            WHERE tenant_id = :tenant_id
        ";
        
        return $this->tenantModel->getDatabase()->selectOne($sql, ['tenant_id' => $tenantId]);
    }
    
    /**
     * Get Available USSD Codes
     */
    private function getAvailableUssdCodes()
    {
        // Get all assigned codes
        $assignedCodes = $this->tenantModel->getDatabase()->select(
            "SELECT ussd_code FROM tenants WHERE ussd_code IS NOT NULL"
        );
        
        $assigned = array_column($assignedCodes, 'ussd_code');
        
        // Generate available codes (1-999)
        $available = [];
        for ($i = 1; $i <= 999; $i++) {
            $code = (string)$i;
            if (!in_array($code, $assigned)) {
                $available[] = $code;
            }
        }
        
        return $available;
    }
    
    /**
     * JSON Response Helper
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        // Clear any previous output
        if (ob_get_length()) ob_clean();
        
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        
        echo json_encode($data);
        exit;
    }
    
    /**
     * Render View Helper
     */
    private function renderView($view, $data = [])
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../../views/' . $view . '.php';
        return ob_get_clean();
    }
    
    /**
     * Render Layout Helper
     */
    private function renderLayout($layout, $content, $data = [])
    {
        $data['content'] = $content;
        extract($data);
        include __DIR__ . '/../../views/layout/' . $layout . '.php';
    }
}
