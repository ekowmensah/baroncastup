<?php

namespace SmartCast\Controllers;

use SmartCast\Controllers\BaseController;
use SmartCast\Services\BulkSmsService;
use SmartCast\Models\SmsTemplate;
use SmartCast\Models\Event;
use SmartCast\Models\Category;

/**
 * Bulk SMS Controller
 */
class BulkSmsController extends BaseController
{
    private $bulkSmsService;
    private $templateModel;
    private $eventModel;
    private $categoryModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->requireRole('platform_admin'); // Use platform_admin role instead of requireSuperAdmin
        
        $this->bulkSmsService = new BulkSmsService();
        $this->templateModel = new SmsTemplate();
        $this->eventModel = new Event();
        $this->categoryModel = new Category();
    }
    
    /**
     * Show bulk SMS dashboard
     */
    public function index()
    {
        // Get active events
        $events = $this->eventModel->getActiveEvents();
        
        // Get SMS templates
        $templates = $this->templateModel->getActiveTemplates();
        
        // Get recent bulk SMS campaigns (you might want to create a campaigns table)
        $recentCampaigns = $this->getRecentCampaigns();
        
        // Render content
        ob_start();
        extract([
            'title' => 'Bulk SMS Management',
            'events' => $events,
            'templates' => $templates,
            'recent_campaigns' => $recentCampaigns
        ]);
        include __DIR__ . '/../../views/superadmin/bulk-sms/index.php';
        $content = ob_get_clean();
        
        // Render with layout
        $this->renderWithLayout($content, 'Bulk SMS Management');
    }
    
    /**
     * Show bulk SMS composer
     */
    public function compose()
    {
        $eventId = $_GET['event_id'] ?? null;
        if (!$eventId) {
            $this->redirect(SUPERADMIN_URL . '/bulk-sms', 'Please select an event first', 'error');
            return;
        }
        
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            $this->redirect(SUPERADMIN_URL . '/bulk-sms', 'Event not found', 'error');
            return;
        }
        
        // Get available recipient groups
        $groups = $this->bulkSmsService->getAvailableGroups($eventId);
        
        // Get categories for this event
        $categories = $this->categoryModel->getCategoriesByEvent($eventId);
        
        // Get SMS templates
        $templates = $this->templateModel->getActiveTemplates();
        
        // Render content
        ob_start();
        extract([
            'title' => 'Compose Bulk SMS - ' . $event['name'],
            'event' => $event,
            'groups' => $groups,
            'categories' => $categories,
            'templates' => $templates
        ]);
        include __DIR__ . '/../../views/superadmin/bulk-sms/compose.php';
        $content = ob_get_clean();
        
        // Render with layout
        $this->renderWithLayout($content, 'Compose Bulk SMS - ' . $event['name']);
    }
    
    /**
     * Preview recipients for bulk SMS
     */
    public function previewRecipients()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            $eventId = $_POST['event_id'] ?? null;
            $recipientType = $_POST['recipient_type'] ?? null;
            $groupType = $_POST['group_type'] ?? null;
            $categoryId = $_POST['category_id'] ?? null;
            $limit = min(100, intval($_POST['limit'] ?? 50)); // Max 100 for preview
            
            if (!$eventId || !$recipientType || !$groupType) {
                return $this->json(['success' => false, 'message' => 'Missing required parameters'], 400);
            }
            
            $options = [
                'limit' => $limit,
                'category_id' => $categoryId
            ];
            
            // Add specific filters based on group type
            if (isset($_POST['min_votes'])) $options['min_votes'] = intval($_POST['min_votes']);
            if (isset($_POST['max_votes'])) $options['max_votes'] = intval($_POST['max_votes']);
            if (isset($_POST['min_amount'])) $options['min_amount'] = floatval($_POST['min_amount']);
            if (isset($_POST['max_amount'])) $options['max_amount'] = floatval($_POST['max_amount']);
            if (isset($_POST['days_since'])) $options['days_since'] = intval($_POST['days_since']);
            
            if ($recipientType === 'nominees') {
                $recipients = $this->bulkSmsService->getNomineesByPerformance($eventId, $groupType, $options);
                $recipientData = array_map(function($nominee) {
                    return [
                        'id' => $nominee['id'],
                        'name' => $nominee['name'],
                        'phone' => $nominee['phone'] ?: 'No phone number',
                        'category' => $nominee['category_name'] ?: 'N/A',
                        'votes' => $nominee['total_votes'],
                        'position' => $nominee['position'],
                        'can_send' => !empty($nominee['phone'])
                    ];
                }, $recipients);
            } else {
                $recipients = $this->bulkSmsService->getVotersByPattern($eventId, $groupType, $options);
                $recipientData = array_map(function($voter) {
                    return [
                        'phone' => $voter['phone'],
                        'total_votes' => $voter['total_votes'],
                        'total_amount' => 'GH₵' . number_format($voter['total_amount'], 2),
                        'last_vote' => $voter['last_vote_date'],
                        'favorite_nominee' => $voter['favorite_nominee'],
                        'can_send' => true
                    ];
                }, $recipients);
            }
            
            $canSendCount = count(array_filter($recipientData, function($r) { return $r['can_send']; }));
            
            return $this->json([
                'success' => true,
                'recipients' => $recipientData,
                'total_count' => count($recipientData),
                'can_send_count' => $canSendCount,
                'cannot_send_count' => count($recipientData) - $canSendCount
            ]);
            
        } catch (\Exception $e) {
            error_log("Preview recipients error: " . $e->getMessage());
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Send bulk SMS
     */
    public function send()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            $eventId = $_POST['event_id'] ?? null;
            $recipientType = $_POST['recipient_type'] ?? null;
            $groupType = $_POST['group_type'] ?? null;
            $templateId = $_POST['template_id'] ?? null;
            $customMessage = $_POST['custom_message'] ?? null;
            $categoryId = $_POST['category_id'] ?? null;
            
            if (!$eventId || !$recipientType || !$groupType) {
                return $this->json(['success' => false, 'message' => 'Missing required parameters'], 400);
            }
            
            if (!$templateId && !$customMessage) {
                return $this->json(['success' => false, 'message' => 'Please select a template or enter a custom message'], 400);
            }
            
            // If custom message, create temporary template
            if ($customMessage && !$templateId) {
                $templateId = $this->templateModel->create([
                    'name' => 'Custom Message - ' . date('Y-m-d H:i:s'),
                    'type' => 'custom',
                    'template' => $customMessage,
                    'variables' => json_encode([]),
                    'is_active' => 0, // Mark as temporary
                    'tenant_id' => null
                ]);
            }
            
            $options = [
                'category_id' => $categoryId
            ];
            
            // Add specific filters
            if (isset($_POST['min_votes'])) $options['min_votes'] = intval($_POST['min_votes']);
            if (isset($_POST['max_votes'])) $options['max_votes'] = intval($_POST['max_votes']);
            if (isset($_POST['min_amount'])) $options['min_amount'] = floatval($_POST['min_amount']);
            if (isset($_POST['max_amount'])) $options['max_amount'] = floatval($_POST['max_amount']);
            if (isset($_POST['days_since'])) $options['days_since'] = intval($_POST['days_since']);
            if (isset($_POST['limit'])) $options['limit'] = min(1000, intval($_POST['limit'])); // Max 1000 per batch
            
            // Send bulk SMS
            if ($recipientType === 'nominees') {
                $result = $this->bulkSmsService->sendToNomineesByPerformance($eventId, $templateId, $groupType, $options);
            } else {
                $result = $this->bulkSmsService->sendToVotersByPattern($eventId, $templateId, $groupType, $options);
            }
            
            // Log the campaign
            $this->logBulkSmsCampaign([
                'event_id' => $eventId,
                'recipient_type' => $recipientType,
                'group_type' => $groupType,
                'template_id' => $templateId,
                'total_sent' => $result['summary']['total_sent'],
                'successful' => $result['summary']['successful'],
                'failed' => $result['summary']['failed'],
                'success_rate' => $result['summary']['success_rate']
            ]);
            
            return $this->json([
                'success' => true,
                'message' => 'Bulk SMS campaign completed',
                'result' => $result
            ]);
            
        } catch (\Exception $e) {
            error_log("Bulk SMS send error: " . $e->getMessage());
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get template preview
     */
    public function templatePreview()
    {
        $templateId = $_GET['template_id'] ?? null;
        $eventId = $_GET['event_id'] ?? null;
        
        if (!$templateId || !$eventId) {
            return $this->json(['success' => false, 'message' => 'Missing parameters'], 400);
        }
        
        try {
            $template = $this->templateModel->find($templateId);
            $event = $this->eventModel->find($eventId);
            
            if (!$template || !$event) {
                return $this->json(['success' => false, 'message' => 'Template or event not found'], 404);
            }
            
            // Sample variables for preview
            $sampleVariables = [
                'event_name' => $event['name'],
                'nominee_name' => 'John Doe',
                'category_name' => 'Best Actor',
                'vote_count' => '150',
                'amount' => 'GH₵25.50',
                'receipt_number' => 'ABC12345',
                'voting_url' => APP_URL . "/events/{$event['slug']}/vote",
                'current_position' => '3rd',
                'percentage' => '15.2',
                'voter_name' => 'Jane Smith',
                'total_votes_cast' => '5',
                'total_amount_spent' => 'GH₵12.50',
                'favorite_nominee' => 'John Doe',
                'last_vote_date' => date('M j, Y'),
                'start_date' => date('M j, Y'),
                'end_date' => date('M j, Y', strtotime('+30 days'))
            ];
            
            $previewMessage = $this->templateModel->processTemplate($templateId, $sampleVariables);
            $variables = $this->templateModel->getTemplateVariables($templateId);
            
            return $this->json([
                'success' => true,
                'template' => $template,
                'preview_message' => $previewMessage,
                'available_variables' => $variables,
                'sample_variables' => $sampleVariables
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Manage SMS templates
     */
    public function templates()
    {
        $templates = $this->templateModel->getActiveTemplates();
        
        // Render content
        ob_start();
        extract([
            'title' => 'SMS Templates',
            'templates' => $templates
        ]);
        include __DIR__ . '/../../views/superadmin/bulk-sms/templates.php';
        $content = ob_get_clean();
        
        // Render with layout
        $this->renderWithLayout($content, 'SMS Templates');
    }
    
    /**
     * Create or edit SMS template
     */
    public function saveTemplate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            $templateId = $_POST['template_id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $type = $_POST['type'] ?? 'custom';
            $template = $_POST['template'] ?? '';
            $variables = $_POST['variables'] ?? [];
            
            if (empty($name) || empty($template)) {
                return $this->json(['success' => false, 'message' => 'Name and template are required'], 400);
            }
            
            $data = [
                'name' => $name,
                'type' => $type,
                'template' => $template,
                'variables' => json_encode($variables),
                'is_active' => 1,
                'tenant_id' => null
            ];
            
            if ($templateId) {
                $this->templateModel->update($templateId, $data);
                $message = 'Template updated successfully';
            } else {
                $templateId = $this->templateModel->create($data);
                $message = 'Template created successfully';
            }
            
            return $this->json([
                'success' => true,
                'message' => $message,
                'template_id' => $templateId
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get recent campaigns (placeholder - you might want to create a campaigns table)
     */
    private function getRecentCampaigns()
    {
        // This is a placeholder. You might want to create a bulk_sms_campaigns table
        // to track campaign history properly
        return [];
    }
    
    /**
     * Log bulk SMS campaign (placeholder)
     */
    private function logBulkSmsCampaign($campaignData)
    {
        // Log to error log for now - you might want to create a campaigns table
        error_log("Bulk SMS Campaign: " . json_encode($campaignData));
    }
    
    /**
     * Render content with superadmin layout
     */
    private function renderWithLayout($content, $title)
    {
        // Extract variables for the layout
        extract(['title' => $title]);
        
        // Include the superadmin layout
        include __DIR__ . '/../../views/layout/superadmin_layout.php';
    }
}
