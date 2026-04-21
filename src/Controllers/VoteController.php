<?php

namespace SmartCast\Controllers;

use SmartCast\Models\Event;
use SmartCast\Models\Contestant;
use SmartCast\Models\Category;
use SmartCast\Models\VoteBundle;
use SmartCast\Models\Transaction;
use SmartCast\Models\Vote;
use SmartCast\Models\VoteReceipt;
use SmartCast\Models\VoteLedger;
use SmartCast\Models\AuditLog;
use SmartCast\Models\Coupon;
use SmartCast\Models\Referral;
use SmartCast\Models\RevenueShare;
use SmartCast\Models\RevenueTransaction;
use SmartCast\Models\TenantBalance;
use SmartCast\Services\PaymentService;
use SmartCast\Services\RevenueWebhookService;
use SmartCast\Services\VoteCompletionService;

/**
 * Vote Controller
 */
class VoteController extends BaseController
{
    private $eventModel;
    private $contestantModel;
    private $categoryModel;
    private $bundleModel;
    private $transactionModel;
    private $voteModel;
    private $receiptModel;
    private $ledgerModel;
    private $auditModel;
    private $revenueShareModel;
    private $revenueTransactionModel;
    private $tenantBalanceModel;
    private $webhookService;
    private $paymentService;
    private $voteCompletionService;
    
    public function __construct()
    {
        parent::__construct();
        $this->eventModel = new Event();
        $this->contestantModel = new Contestant();
        $this->categoryModel = new Category();
        $this->bundleModel = new VoteBundle();
        $this->transactionModel = new Transaction();
        $this->voteModel = new Vote();
        $this->receiptModel = new VoteReceipt();
        $this->ledgerModel = new VoteLedger();
        $this->auditModel = new AuditLog();
        $this->revenueShareModel = new RevenueShare();
        $this->revenueTransactionModel = new RevenueTransaction();
        $this->tenantBalanceModel = new TenantBalance();
        $this->webhookService = new RevenueWebhookService();
        $this->paymentService = new PaymentService();
        $this->voteCompletionService = new VoteCompletionService();
    }
    
    public function showVoting($eventSlug)
    {
        // Handle both slug and ID
        $event = $this->resolveEvent($eventSlug);
        
        if (!$event || !$this->eventModel->canVote($event['id'])) {
            $this->redirect('/events', 'Voting is not available for this event', 'error');
        }
        
        $contestants = $this->contestantModel->getContestantsByEvent($event['id']);
        $categories = $this->categoryModel->getCategoriesByEvent($event['id']);
        
        // Group contestants by category, preserving category display order
        $contestantsByCategory = [];
        
        // First, create the category structure in the correct order
        foreach ($categories as $category) {
            $contestantsByCategory[] = [
                'id' => $category['id'],
                'name' => $category['name'],
                'contestants' => []
            ];
        }
        
        // Then, add contestants to their respective categories
        $seenContestants = []; // Track contestants we've already added
        foreach ($contestants as $contestant) {
            $categoryId = $contestant['category_id'] ?? null;
            $contestantId = $contestant['id'];
            
            if ($categoryId) {
                // Find the category in our ordered array
                foreach ($contestantsByCategory as &$category) {
                    if ($category['id'] == $categoryId) {
                        // Create a unique key for this contestant-category combination
                        $uniqueKey = $contestantId . '_' . $categoryId;
                        
                        // Only add if we haven't seen this exact combination before
                        if (!isset($seenContestants[$uniqueKey])) {
                            $category['contestants'][] = $contestant;
                            $seenContestants[$uniqueKey] = true;
                        }
                        break;
                    }
                }
            }
        }
        
        $this->view('voting/select-nominee', [
            'event' => $event,
            'contestants' => $contestants,
            'categories' => $categories,
            'contestantsByCategory' => $contestantsByCategory,
            'title' => 'Select Nominee - ' . $event['name']
        ]);
    }
    
    public function showVoteForm($eventSlug, $contestantSlug)
    {
        $event = $this->resolveEvent($eventSlug);
        
        if (!$event || !$this->eventModel->canVote($event['id'])) {
            $this->redirect('/events', 'Voting is not available for this event', 'error');
        }
        
        $contestant = $this->resolveContestant($contestantSlug);
        
        if (!$contestant || $contestant['event_id'] != $event['id'] || !$contestant['active']) {
            // Generate event slug for redirect
            require_once __DIR__ . '/../Helpers/SlugHelper.php';
            $eventSlugForRedirect = \SmartCast\Helpers\SlugHelper::generateEventSlug($event);
            $this->redirect("/events/$eventSlugForRedirect/vote", 'Invalid contestant selected', 'error');
        }
        
        $bundles = $this->bundleModel->getBundlesByEvent($event['id'], $event['tenant_id']);
        
        // Create default bundles if none exist
        if (empty($bundles)) {
            $votePrice = $event['vote_price'] ?? 0.50;
            $eventId = $event['id'];
            
            // Create default vote bundles
            $defaultBundles = [
                ['name' => 'Single Vote', 'votes' => 1, 'price' => $votePrice],
                ['name' => 'Vote Pack (5)', 'votes' => 5, 'price' => $votePrice * 5 * 0.9], // 10% discount
                ['name' => 'Vote Pack (10)', 'votes' => 10, 'price' => $votePrice * 10 * 0.8], // 20% discount
                ['name' => 'Vote Pack (25)', 'votes' => 25, 'price' => $votePrice * 25 * 0.7], // 30% discount
            ];
            
            foreach ($defaultBundles as $bundleData) {
                $this->bundleModel->create([
                    'event_id' => $eventId,
                    'name' => $bundleData['name'],
                    'votes' => $bundleData['votes'],
                    'price' => $bundleData['price'],
                    'active' => 1
                ]);
            }
            
            // Reload bundles after creation
            $bundles = $this->bundleModel->getBundlesByEvent($eventId, $event['tenant_id']);
        }
        
        // Get category information if provided, or from contestant
        $categoryId = $_GET['category'] ?? $_GET['category_id'] ?? $contestant['category_id'] ?? null;
        $category = null;
        if ($categoryId) {
            $category = $this->categoryModel->find($categoryId);
        }
        
        // If no category found, try to auto-detect from contestant's categories
        if (!$category) {
            $contestantCategoryModel = new \SmartCast\Models\ContestantCategory();
            $categories = $contestantCategoryModel->getCategoriesByContestant($contestantId);
            
            if (count($categories) === 1) {
                // Only one category - use it automatically
                $category = $categories[0];
                $categoryId = $category['category_id'];
                error_log("Auto-detected single category for contestant {$contestantId}: {$categoryId}");
            } elseif (count($categories) > 1) {
                // Multiple categories - use the first one as default
                // In a production system, you might want to show a category selection page
                $category = $categories[0];
                $categoryId = $category['category_id'];
                error_log("Multiple categories found for contestant {$contestantId}, using first: {$categoryId}");
            }
        }
        
        // Debug logging
        if (APP_DEBUG) {
            error_log("Vote Form Debug - Category ID: " . ($categoryId ?? 'null') . ", Category found: " . ($category ? 'yes' : 'no'));
            if ($category) {
                error_log("Category data: " . print_r($category, true));
            }
        }
        
        $this->view('voting/vote-form', [
            'event' => $event,
            'contestant' => $contestant,
            'bundles' => $bundles,
            'category' => $category,
            'vote_price' => $event['vote_price'] ?? 0.50,
            'title' => 'Vote for ' . $contestant['name']
        ]);
    }
    
    /**
     * Vote by shortcode - Short URL format
     * URL: /vote/{shortcode} (e.g., /vote/JB-232)
     */
    public function voteByShortcode($shortcode)
    {
        // Look up contestant by voting shortcode
        $db = \SmartCast\Core\Database::getInstance();
        
        // Query to find contestant by shortcode (case-insensitive)
        $sql = "
            SELECT 
                c.id as contestant_id,
                c.name as contestant_name,
                c.contestant_code,
                c.event_id,
                e.code as event_code,
                e.name as event_name,
                cc.category_id,
                cc.short_code as voting_shortcode
            FROM contestant_categories cc
            INNER JOIN contestants c ON cc.contestant_id = c.id
            INNER JOIN events e ON c.event_id = e.id
            WHERE UPPER(cc.short_code) = UPPER(:shortcode)
            AND c.active = 1
            AND e.status IN ('published', 'active')
            LIMIT 1
        ";
        
        error_log("Short URL Debug - Looking up shortcode: {$shortcode}");
        error_log("Short URL Debug - SQL: " . $sql);
        
        try {
            $stmt = $db->query($sql, ['shortcode' => $shortcode]);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            error_log("Short URL Debug - Query result count: " . count($result));
            
            if (empty($result)) {
                error_log("Short URL Debug - No results found for shortcode: {$shortcode}");
                $this->redirect('/events', 'Invalid voting code: ' . htmlspecialchars($shortcode), 'error');
                return;
            }
        } catch (\Exception $e) {
            error_log("Short URL Debug - Query error: " . $e->getMessage());
            $this->redirect('/events', 'Database error', 'error');
            return;
        }
        
        $data = $result[0];
        
        // Debug logging
        error_log("Short URL Debug - Shortcode: {$shortcode}");
        error_log("Short URL Debug - Data: " . json_encode($data));
        
        // Generate slugs for redirect
        require_once __DIR__ . '/../Helpers/SlugHelper.php';
        
        // Use event code or generate slug from name
        $eventSlug = $data['event_code'] ?: \SmartCast\Helpers\SlugHelper::generateSlug($data['event_name']);
        
        // Generate contestant slug from name and ID
        $contestantSlug = \SmartCast\Helpers\SlugHelper::generateSlug($data['contestant_name']) . '-' . $data['contestant_id'];
        
        // Redirect to the full vote form with category parameter
        $redirectUrl = "/events/{$eventSlug}/vote/{$contestantSlug}?category={$data['category_id']}";
        
        error_log("Short URL Debug - Event Slug: {$eventSlug}");
        error_log("Short URL Debug - Contestant Slug: {$contestantSlug}");
        error_log("Short URL Debug - Redirect URL: {$redirectUrl}");
        
        $this->redirect($redirectUrl);
    }
    
    public function processVote($eventSlug = null)
    {
        // Resolve event from slug or ID (for standard voting)
        $event = null;
        if ($eventSlug) {
            $event = $this->resolveEvent($eventSlug);
        }
        
        // Handle fallback from POST data (for direct/shortcode voting)
        if (!$event && isset($_POST['event_id'])) {
            $event = $this->eventModel->find($_POST['event_id']);
        }
        
        if (!$event || !$this->eventModel->canVote($event['id'])) {
            return $this->json(['success' => false, 'message' => 'Voting not available'], 400);
        }
        
        $eventId = $event['id'];
        $data = $this->sanitizeInput($_POST);
        
        // Get category from URL parameter if available
        $categoryId = $_GET['category'] ?? $data['category_id'] ?? null;
        if ($categoryId) {
            $data['category_id'] = $categoryId;
        }
        
        // Auto-detect category if missing
        if (empty($data['category_id']) && !empty($data['contestant_id'])) {
            $contestantCategoryModel = new \SmartCast\Models\ContestantCategory();
            $categories = $contestantCategoryModel->getCategoriesByContestant($data['contestant_id']);
            
            if (!empty($categories)) {
                // Use the first category as default
                $data['category_id'] = $categories[0]['category_id'];
                error_log("Auto-detected category for vote processing: contestant_id={$data['contestant_id']}, category_id={$data['category_id']}");
            }
        }
        
        // Validate input - bundle_id is optional for custom votes
        $validationRules = [
            'contestant_id' => ['required' => true, 'numeric' => true],
            'category_id' => ['required' => true, 'numeric' => true]
        ];
        
        // Phone number only required for mobile money payments
        $paymentMethod = $data['payment_method'] ?? 'mobile_money';
        if ($paymentMethod === 'mobile_money') {
            $validationRules['msisdn'] = ['required' => true, 'min' => 10];
        }
        
        // If it's not a custom vote, require bundle_id
        if (empty($data['vote_method']) || $data['vote_method'] !== 'custom') {
            $validationRules['bundle_id'] = ['required' => true, 'numeric' => true];
        }
        
        $errors = $this->validateInput($data, $validationRules);
        
        if (!empty($errors)) {
            return $this->json([
                'success' => false, 
                'message' => 'Validation failed',
                'errors' => $errors,
                'received_data' => array_keys($data) // Debug: show what data was received
            ], 400);
        }
        
        // Verify contestant, bundle, and category
        $contestant = $this->contestantModel->find($data['contestant_id']);
        $category = $this->categoryModel->find($data['category_id']);
        
        // Debug logging
        if (APP_DEBUG) {
            error_log("Process Vote Debug - Category ID from form: " . ($data['category_id'] ?? 'null'));
            error_log("Category found: " . ($category ? 'yes' : 'no'));
            error_log("Contestant category_id: " . ($contestant['category_id'] ?? 'not set'));
            if ($category) {
                error_log("Category data: " . print_r($category, true));
            }
            if ($contestant) {
                error_log("Contestant data keys: " . implode(', ', array_keys($contestant)));
            }
        }
        
        if (!$contestant || $contestant['event_id'] != $eventId || !$contestant['active']) {
            return $this->json(['success' => false, 'message' => 'Invalid contestant'], 400);
        }
        
        // Handle custom votes vs package votes
        if (!empty($data['vote_method']) && $data['vote_method'] === 'custom') {
            // For custom votes, we'll use the smallest existing bundle as reference
            // but calculate the price based on actual vote count
            $customVotes = intval($data['custom_votes'] ?? 1);
            if ($customVotes < 1 || $customVotes > 10000) {
                return $this->json(['success' => false, 'message' => 'Invalid vote count (1-10000)'], 400);
            }
            
            // Get any existing bundle for this event to use as a reference
            $bundles = $this->bundleModel->getBundlesByEvent($eventId, $event['tenant_id']);
            if (empty($bundles)) {
                // Create a default bundle for custom votes if none exist
                $votePrice = $event['vote_price'] ?? 0.50;
                $defaultBundleId = $this->bundleModel->create([
                    'event_id' => $eventId,
                    'name' => 'Single Vote',
                    'votes' => 1,
                    'price' => $votePrice,
                    'active' => 1
                ]);
                
                $referenceBundle = ['id' => $defaultBundleId];
            } else {
                $referenceBundle = $bundles[0];
            }
            
            $votePrice = $event['vote_price'] ?? 0.50;
            $bundle = [
                'id' => $referenceBundle['id'], // Use existing bundle ID to satisfy foreign key
                'votes' => $customVotes, // Override with custom vote count
                'price' => $customVotes * $votePrice, // Calculate custom price
                'name' => 'Custom Votes (' . $customVotes . ' votes)',
                'event_id' => $eventId,
                'active' => true
            ];
        } else {
            // Regular bundle validation
            $bundle = $this->bundleModel->find($data['bundle_id']);
            if (!$bundle || $bundle['event_id'] != $eventId || !$bundle['active']) {
                return $this->json(['success' => false, 'message' => 'Invalid vote bundle'], 400);
            }
        }
        
        if (!$category) {
            return $this->json([
                'success' => false, 
                'message' => 'Category not found',
                'debug' => ['category_id' => $data['category_id']]
            ], 400);
        }
        
        if ($category['event_id'] != $eventId) {
            return $this->json([
                'success' => false, 
                'message' => 'Category does not belong to this event',
                'debug' => ['category_event_id' => $category['event_id'], 'expected_event_id' => $eventId]
            ], 400);
        }
        
        // Check active status only if the field exists
        if (isset($category['active']) && !$category['active']) {
            return $this->json(['success' => false, 'message' => 'Category is not active'], 400);
        }
        
        // Verify contestant is in the selected category using the many-to-many relationship
        $contestantInCategory = $this->contestantModel->isContestantInCategory($data['contestant_id'], $data['category_id']);
        
        if (!$contestantInCategory) {
            return $this->json([
                'success' => false, 
                'message' => 'Contestant not in selected category',
                'debug' => [
                    'contestant_id' => $data['contestant_id'],
                    'selected_category_id' => $data['category_id']
                ]
            ], 400);
        }
        
        try {
            // Start transaction
            $this->transactionModel->getDatabase()->beginTransaction();
            
            // Extract votes from bundle for response
            $votes = $bundle['votes'] ?? 1;
            
            // Create pending transaction
            $transactionData = [
                'tenant_id' => $event['tenant_id'],
                'event_id' => $eventId,
                'contestant_id' => $data['contestant_id'],
                'category_id' => $data['category_id'], // Add category context
                'bundle_id' => $bundle['id'] ?? null, // Handle virtual bundles
                'amount' => $bundle['price'],
                'msisdn' => $data['msisdn'],
                'status' => 'pending', // Set as pending until payment confirms
                'provider' => 'momo',
                'coupon_code' => $data['coupon_code'] ?? null,
                'referral_code' => $data['referral_code'] ?? null
            ];
            
            $transactionId = $this->transactionModel->createTransaction($transactionData);
            
            // Get payment method (mobile_money, card, or flutterwave)
            $paymentMethod = $data['payment_method'] ?? 'mobile_money';
            
            // Determine currency based on payment method and country
            $currency = 'GHS'; // Default to Ghana Cedis
            if ($paymentMethod === 'flutterwave' && !empty($data['country'])) {
                // Map country code to currency
                $currencyMap = [
                    'GH' => 'GHS', 'NG' => 'NGN', 'KE' => 'KES', 'UG' => 'UGX',
                    'RW' => 'RWF', 'TZ' => 'TZS', 'ZM' => 'ZMW', 'ZA' => 'ZAR'
                ];
                $currency = $currencyMap[$data['country']] ?? 'GHS';
            }
            
            // Prepare common payment data
            $eventSlug = $event['slug'] ?? $event['id'];
            $paymentData = [
                'amount' => $bundle['price'],
                'currency' => $currency,
                'description' => "Vote for {$contestant['name']} - {$bundle['votes']} vote(s)",
                'callback_url' => APP_URL . "/api/payment/callback/{$transactionId}",
                'return_url' => APP_URL . "/api/payment/callback/{$transactionId}",
                'cancellation_url' => APP_URL . "/voting/{$eventSlug}",
                'email' => $data['email'] ?? 'voter@smartcast.com',
                'customer_name' => $data['customer_name'] ?? 'SmartCast Voter',
                'tenant_id' => $event['tenant_id'],
                'voting_transaction_id' => $transactionId,
                'related_id' => $transactionId,
                'reference' => 'VOTE_' . $transactionId . '_' . time(),
                'metadata' => [
                    'transaction_id' => $transactionId,
                    'event_id' => $eventId,
                    'contestant_id' => $data['contestant_id'],
                    'category_id' => $data['category_id'],
                    'votes' => $bundle['votes']
                ]
            ];
            
            // Initialize payment based on method
            if ($paymentMethod === 'card') {
                // Card payment
                $paymentData['phone'] = $data['msisdn'] ?? '';
                $paymentResult = $this->paymentService->initializeCardPayment($paymentData);
            } elseif ($paymentMethod === 'flutterwave') {
                // Flutterwave payment (international - supports multiple payment methods)
                $paymentData['country'] = $data['country'] ?? 'GH';
                $paymentData['name'] = $data['customer_name'] ?? 'SmartCast Voter';
                $paymentData['email'] = $data['email'] ?? 'voter@smartcast.com';
                $paymentData['phone'] = $data['msisdn'] ?? '0000000000';
                
                // Flutterwave will handle payment method selection on their checkout page
                // We don't need to specify payment method or network
                
                // Add callback URL for redirect-based payments
                $eventSlug = $event['slug'] ?? $event['id'];
                $paymentData['callback_url'] = APP_URL . '/events/' . $eventSlug . '/vote/callback';
                
                // Use Flutterwave gateway
                $paymentResult = $this->paymentService->initializeMobileMoneyPayment($paymentData);
            } else {
                // Mobile money payment (Ghana - default)
                $paymentData['phone'] = $data['msisdn'];
                $paymentResult = $this->paymentService->initializeMobileMoneyPayment($paymentData);
            }
            
            if (!$paymentResult['success']) {
                // Log the full error for debugging
                error_log("Payment initiation failed: " . json_encode($paymentResult));
                throw new \Exception('Payment initiation failed: ' . ($paymentResult['message'] ?? 'Unknown error'));
            }
            
            // Update transaction with payment reference (if available)
            $paymentReference = $paymentResult['payment_reference'] ?? $paymentResult['gateway_reference'] ?? null;
            if ($paymentReference) {
                $this->transactionModel->update($transactionId, [
                    'provider_reference' => $paymentReference
                ]);
            }
            
            $this->transactionModel->getDatabase()->commit();
            
            return $this->json([
                'success' => true,
                'payment_initiated' => true,
                'transaction_id' => $transactionId,
                'payment_reference' => $paymentReference,
                'payment_url' => $paymentResult['payment_url'] ?? null,
                'provider' => $paymentResult['provider'] ?? null,
                'message' => $paymentResult['message'],
                'status_check_url' => APP_URL . "/api/payment/status/{$transactionId}",
                'expires_at' => $paymentResult['expires_at'] ?? null,
                'receipt' => $paymentReference ?? $transactionId,
                'votes_cast' => $votes,
                'contestant_name' => $contestant['name']
            ]);
            
        } catch (\Exception $e) {
            $this->transactionModel->getDatabase()->rollback();
            
            return $this->json([
                'success' => false,
                'message' => 'Vote failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function checkPaymentStatus($transactionId)
    {
        // Basic validation
        if (empty($transactionId) || !is_numeric($transactionId)) {
            return $this->json([
                'success' => false, 
                'message' => 'Invalid transaction ID',
                'transaction_id' => $transactionId
            ], 400);
        }
        
        try {
            $transaction = $this->transactionModel->find($transactionId);
            
            if (!$transaction) {
                return $this->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }
            
            // Debug logging
            if (APP_DEBUG) {
                error_log("Checking payment status for transaction: " . $transactionId);
                error_log("Transaction data: " . print_r($transaction, true));
            }
            
            // If transaction is already successful, return success immediately
            if ($transaction['status'] === 'success') {
                error_log("Transaction already successful, returning success status: " . $transactionId);
                
                // Get receipt if available
                $receiptShortCode = null;
                try {
                    $receipt = $this->receiptModel->getReceiptByTransaction($transactionId);
                    if ($receipt) {
                        $receiptShortCode = $receipt['short_code'];
                    }
                } catch (\Exception $e) {
                    error_log("Error getting receipt: " . $e->getMessage());
                }
                
                return $this->json([
                    'success' => true,
                    'transaction_id' => $transactionId,
                    'payment_status' => 'success',
                    'message' => 'Payment completed successfully',
                    'receipt_number' => $receiptShortCode,
                    'amount' => $transaction['amount'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
            
            // Check if provider_reference exists
            if (empty($transaction['provider_reference'])) {
                // If no provider reference, the payment initiation likely failed
                // Update transaction status and return failed status
                try {
                    $this->transactionModel->update($transactionId, [
                        'status' => 'failed',
                        'failure_reason' => 'Payment initiation failed - no provider reference'
                    ]);
                } catch (\Exception $updateError) {
                    error_log("Failed to update transaction status: " . $updateError->getMessage());
                }
                
                return $this->json([
                    'success' => true, 
                    'transaction_id' => $transactionId,
                    'payment_status' => 'failed',
                    'message' => 'Payment initiation failed. Please try again.',
                    'amount' => $transaction['amount'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
            
            // Check payment status with MoMo service
            try {
                $paymentStatus = $this->paymentService->checkPaymentStatus($transactionId);
            } catch (\Exception $e) {
                error_log("MoMo service error: " . $e->getMessage());
                return $this->json([
                    'success' => false,
                    'message' => 'Payment service unavailable',
                    'transaction_id' => $transactionId,
                    'payment_status' => 'pending'
                ], 500);
            }
            
            // Debug payment status
            if (APP_DEBUG) {
                error_log("Payment status: " . $paymentStatus['status']);
                error_log("Transaction status: " . $transaction['status']);
                error_log("Payment status data: " . print_r($paymentStatus, true));
            }
            
            // Update transaction status based on payment result
            if ($paymentStatus['status'] === 'success' && $transaction['status'] === 'pending') {
                // Payment successful - process the vote
                error_log("Processing successful payment for transaction: " . $transactionId);
                try {
                    $this->processSuccessfulPayment($transaction, $paymentStatus);
                    error_log("Vote processing completed successfully for transaction: " . $transactionId);
                } catch (\Exception $e) {
                    error_log("Vote processing error: " . $e->getMessage());
                    error_log("Vote processing stack trace: " . $e->getTraceAsString());
                    
                    // Try to update transaction status to success even if vote processing fails
                    try {
                        $this->transactionModel->update($transactionId, [
                            'status' => 'success',
                            'provider_reference' => $paymentStatus['receipt_number'] ?? $transaction['provider_reference']
                        ]);
                    } catch (\Exception $updateError) {
                        error_log("Failed to update transaction status: " . $updateError->getMessage());
                    }
                    
                    // Get receipt short_code if available
                    $receiptShortCode = null;
                    try {
                        $receipt = $this->receiptModel->getReceiptByTransaction($transactionId);
                        if ($receipt) {
                            $receiptShortCode = $receipt['short_code'];
                        }
                    } catch (\Exception $receiptError) {
                        error_log("Error getting receipt for transaction {$transactionId}: " . $receiptError->getMessage());
                    }
                    
                    // Return success for payment but note processing issue
                    return $this->json([
                        'success' => true,
                        'transaction_id' => $transactionId,
                        'payment_status' => 'success',
                        'message' => 'Payment successful, vote processing failed: ' . $e->getMessage(),
                        'receipt_number' => $receiptShortCode ?? $paymentStatus['receipt_number'] ?? null,
                        'amount' => $paymentStatus['amount'],
                        'timestamp' => $paymentStatus['timestamp']
                    ]);
                }
            } elseif (in_array($paymentStatus['status'], ['failed', 'expired'])) {
                // Payment failed - update transaction status
                try {
                    $this->transactionModel->update($transactionId, [
                        'status' => $paymentStatus['status'],
                        'failure_reason' => $paymentStatus['message'] ?? 'Payment failed'
                    ]);
                } catch (\Exception $updateError) {
                    error_log("Failed to update transaction status: " . $updateError->getMessage());
                }
            }
            
            // Get receipt short_code if available
            $receiptShortCode = null;
            try {
                $receipt = $this->receiptModel->getReceiptByTransaction($transactionId);
                if ($receipt) {
                    $receiptShortCode = $receipt['short_code'];
                }
            } catch (\Exception $e) {
                error_log("Error getting receipt for transaction {$transactionId}: " . $e->getMessage());
            }
            
            return $this->json([
                'success' => true,
                'transaction_id' => $transactionId,
                'payment_status' => $paymentStatus['status'],
                'message' => $paymentStatus['message'],
                'receipt_number' => $receiptShortCode ?? $paymentStatus['receipt_number'] ?? null,
                'amount' => $transaction['amount'], // Use actual transaction amount
                'timestamp' => $paymentStatus['timestamp']
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Status check failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function processSuccessfulPayment($transaction, $paymentStatus)
    {
        try {
            error_log("Starting vote processing for transaction: " . $transaction['id']);
            $this->transactionModel->getDatabase()->beginTransaction();
            
            // Update transaction status
            error_log("Updating transaction status to success");
            $updateData = [
                'status' => 'success',
                'provider_reference' => $paymentStatus['receipt_number'] ?? $transaction['provider_reference']
            ];
            
            // Add phone number if provided in payment details (for card payments)
            if (!empty($paymentStatus['phone_number']) && empty($transaction['msisdn'])) {
                $updateData['msisdn'] = $paymentStatus['phone_number'];
                error_log("Adding phone number to transaction: " . $paymentStatus['phone_number']);
            }
            
            $this->transactionModel->update($transaction['id'], $updateData);
            
            // Get vote count
            $voteCount = $this->getVoteCountFromTransaction($transaction);
            error_log("Vote count calculated: " . $voteCount);
            
            if ($voteCount <= 0) {
                throw new \Exception("Invalid vote count: " . $voteCount);
            }
            
            // Cast votes
            error_log("Casting votes - Count: " . $voteCount);
            $voteId = $this->voteModel->castVote(
                $transaction['id'],
                $transaction['tenant_id'],
                $transaction['event_id'],
                $transaction['contestant_id'],
                $transaction['category_id'],
                $voteCount
            );
            error_log("Vote cast successfully with ID: " . $voteId);
            
            // Create vote ledger entry
            $this->ledgerModel->createLedgerEntry(
                $voteId,
                $transaction['id'],
                $transaction['tenant_id'],
                $transaction['event_id'],
                $transaction['contestant_id'],
                $this->getVoteCountFromTransaction($transaction)
            );
            
            // Generate receipt
            try {
                error_log("Generating receipt for transaction: " . $transaction['id']);
                $receipt = $this->receiptModel->generateReceipt($transaction['id']);
                error_log("Receipt generated successfully: " . print_r($receipt, true));
            } catch (\Exception $e) {
                error_log("Receipt generation failed: " . $e->getMessage());
                error_log("Receipt generation stack trace: " . $e->getTraceAsString());
                // Don't throw the exception - continue without receipt for now
                $receipt = null;
            }
            
            // ✅ INSTANT REVENUE DISTRIBUTION - NEW!
            error_log("Processing revenue distribution for transaction: " . $transaction['id']);
            $this->processRevenueDistribution($transaction);
            
            // ✅ SEND SMS NOTIFICATION - NEW!
            error_log("Sending SMS notification for transaction: " . $transaction['id']);
            try {
                $smsResult = $this->voteCompletionService->processVoteCompletion($transaction['id'], [
                    'phone' => $this->extractPhoneFromTransaction($transaction, $paymentStatus)
                ]);
                error_log("SMS notification result: " . json_encode($smsResult));
            } catch (\Exception $e) {
                error_log("SMS notification failed: " . $e->getMessage());
                error_log("SMS notification stack trace: " . $e->getTraceAsString());
                // Don't throw - SMS failure shouldn't break the vote process
            }
            
            // Log the vote
            $this->auditModel->logVoteCast(
                $transaction['id'], 
                $transaction['event_id'], 
                $transaction['contestant_id'], 
                $transaction['amount']
            );
            
            $this->transactionModel->getDatabase()->commit();
            
            return true;
            
        } catch (\Exception $e) {
            $this->transactionModel->getDatabase()->rollback();
            throw $e;
        }
    }
    
    /**
     * Extract phone number from transaction or payment status
     */
    private function extractPhoneFromTransaction($transaction, $paymentStatus = [])
    {
        // Try to get phone from various sources
        $phone = null;
        
        // From payment status (webhook payload)
        if (!empty($paymentStatus['phone'])) {
            $phone = $paymentStatus['phone'];
        }
        
        // From transaction msisdn field (primary source for votes)
        if (!$phone && !empty($transaction['msisdn'])) {
            $phone = $transaction['msisdn'];
        }
        
        // From transaction metadata
        if (!$phone && !empty($transaction['metadata'])) {
            $metadata = is_string($transaction['metadata']) ? 
                json_decode($transaction['metadata'], true) : 
                $transaction['metadata'];
                
            if (isset($metadata['phone'])) {
                $phone = $metadata['phone'];
            }
        }
        
        // From transaction phone field (if exists)
        if (!$phone && !empty($transaction['phone'])) {
            $phone = $transaction['phone'];
        }
        
        // From payment gateway response
        if (!$phone && !empty($transaction['gateway_response'])) {
            $response = is_string($transaction['gateway_response']) ? 
                json_decode($transaction['gateway_response'], true) : 
                $transaction['gateway_response'];
                
            if (isset($response['phone']) || isset($response['mobile'])) {
                $phone = $response['phone'] ?? $response['mobile'];
            }
        }
        
        // Default fallback - you might want to get this from user session or form data
        if (!$phone) {
            error_log("Warning: Phone number not found in transaction data for SMS notification");
            error_log("Transaction data: " . json_encode($transaction));
            // You could return a default test number or throw an exception
            return null;
        }
        
        return $phone;
    }

    private function getVoteCountFromTransaction($transaction)
    {
        error_log("Getting vote count for transaction: " . print_r($transaction, true));
        
        // If we have a bundle_id, always use bundle votes (handles discounted packages)
        if ($transaction['bundle_id']) {
            $bundle = $this->bundleModel->find($transaction['bundle_id']);
            error_log("Bundle found: " . print_r($bundle, true));
            $bundleVotes = $bundle['votes'] ?? 1;
            
            // Check if the transaction amount matches the bundle price (true bundle purchase)
            if (abs($transaction['amount'] - $bundle['price']) < 0.01) {
                error_log("Bundle purchase detected - using bundle votes: $bundleVotes");
                return $bundleVotes;
            } else {
                // Amount doesn't match bundle price - this is a custom vote using bundle as reference
                $event = $this->eventModel->find($transaction['event_id']);
                $votePrice = $event['vote_price'] ?? 0.50;
                $calculatedVotes = (int) ($transaction['amount'] / $votePrice);
                
                error_log("Custom vote with bundle reference - calculated: $calculatedVotes, bundle: $bundleVotes. Using calculated.");
                error_log("Transaction amount: {$transaction['amount']}, Bundle price: {$bundle['price']}, Vote price: $votePrice");
                return $calculatedVotes;
            }
        }
        
        // No bundle_id, calculate from amount
        $event = $this->eventModel->find($transaction['event_id']);
        $votePrice = $event['vote_price'] ?? 0.50;
        $calculatedVotes = (int) ($transaction['amount'] / $votePrice);
        
        error_log("No bundle - using calculated votes: $calculatedVotes");
        error_log("Event vote price: $votePrice, Transaction amount: {$transaction['amount']}");
        return $calculatedVotes;
    }
    
    /**
     * Process instant revenue distribution when a vote is successfully paid
     */
    private function processRevenueDistribution($transaction)
    {
        try {
            // Create revenue share record (this calculates platform fee)
            $revenueShare = $this->revenueShareModel->calculateAndCreateShare(
                $transaction['id'],
                $transaction['amount'],
                $transaction['tenant_id'],
                $transaction['event_id']  // Pass event ID for event-specific fee rules
            );
            
            if ($revenueShare) {
                error_log("Revenue share created: Platform fee = " . $revenueShare['amount']);
                
                // Calculate tenant's net amount (total - platform fee)
                $tenantNetAmount = $transaction['amount'] - $revenueShare['amount'];
                
                error_log("Tenant net amount: $tenantNetAmount (from {$transaction['amount']} - {$revenueShare['amount']})");
                
                // ✅ CREATE REVENUE TRANSACTION RECORD FOR REPORTS
                try {
                    $this->revenueTransactionModel->createRevenueTransaction(
                        $transaction['id'],
                        $transaction['tenant_id'],
                        $transaction['event_id'],
                        $transaction['amount']
                    );
                    error_log("Revenue transaction record created for reports");
                } catch (\Exception $e) {
                    error_log("Revenue transaction creation failed: " . $e->getMessage());
                    // Don't fail the whole process
                }
                
                // Update tenant balance immediately
                $this->tenantBalanceModel->addEarnings($transaction['tenant_id'], $tenantNetAmount);
                
                error_log("Revenue distribution completed successfully");
                
                $revenueBreakdown = [
                    'platform_fee' => $revenueShare['amount'],
                    'tenant_amount' => $tenantNetAmount,
                    'total_amount' => $transaction['amount']
                ];
                
                // 🚀 Send real-time webhook notifications
                $this->sendRevenueWebhooks($transaction, $revenueBreakdown);
                
                return $revenueBreakdown;
            } else {
                error_log("No revenue share created - no applicable fee rules");
                
                // ✅ CREATE REVENUE TRANSACTION RECORD (no fees scenario)
                try {
                    $this->revenueTransactionModel->createRevenueTransaction(
                        $transaction['id'],
                        $transaction['tenant_id'],
                        $transaction['event_id'],
                        $transaction['amount']
                    );
                    error_log("Revenue transaction record created (no fees)");
                } catch (\Exception $e) {
                    error_log("Revenue transaction creation failed: " . $e->getMessage());
                }
                
                // If no fee rules, tenant gets full amount
                $this->tenantBalanceModel->addEarnings($transaction['tenant_id'], $transaction['amount']);
                
                return [
                    'platform_fee' => 0,
                    'tenant_amount' => $transaction['amount'],
                    'total_amount' => $transaction['amount']
                ];
            }
            
        } catch (\Exception $e) {
            error_log("Revenue distribution failed: " . $e->getMessage());
            throw new \Exception("Revenue distribution failed: " . $e->getMessage());
        }
    }
    
    /**
     * Send webhook notifications for revenue distribution
     */
    private function sendRevenueWebhooks($transaction, $revenueBreakdown)
    {
        try {
            // Get additional transaction details
            $event = $this->eventModel->find($transaction['event_id']);
            $contestant = $this->contestantModel->find($transaction['contestant_id']);
            
            $transactionData = array_merge($transaction, [
                'event_name' => $event['name'] ?? 'Unknown Event',
                'contestant_name' => $contestant['name'] ?? 'Unknown Contestant',
                'vote_count' => $this->getVoteCountFromTransaction($transaction)
            ]);
            
            // Send tenant revenue notification
            $this->webhookService->sendRevenueEarned(
                $transaction['tenant_id'],
                $transactionData,
                $revenueBreakdown
            );
            
            // Send platform revenue notification
            $this->webhookService->sendPlatformRevenue(
                $transactionData,
                $revenueBreakdown
            );
            
            error_log("Revenue webhooks sent successfully");
            
        } catch (\Exception $e) {
            error_log("Failed to send revenue webhooks: " . $e->getMessage());
            // Don't throw - webhooks are not critical for core functionality
        }
    }
    
    /**
     * Resolve event by slug or ID
     */
    private function resolveEvent($eventSlug)
    {
        // First try to find by code (slug)
        $event = $this->eventModel->findByCode($eventSlug);
        
        // If not found and it's numeric, try by ID
        if (!$event && is_numeric($eventSlug)) {
            $event = $this->eventModel->find($eventSlug);
        }
        
        return $event;
    }
    
    /**
     * Resolve contestant by slug or ID
     */
    private function resolveContestant($contestantSlug)
    {
        // Extract ID from slug (format: name-id)
        require_once __DIR__ . '/../Helpers/SlugHelper.php';
        $id = \SmartCast\Helpers\SlugHelper::extractIdFromSlug($contestantSlug);
        
        if ($id) {
            return $this->contestantModel->find($id);
        }
        
        // If not a slug format and numeric, try direct ID
        if (is_numeric($contestantSlug)) {
            return $this->contestantModel->find($contestantSlug);
        }
        
        return null;
    }
    
    public function testPaymentStatus($transactionId)
    {
        // Debug endpoint to test payment status directly
        $transaction = $this->transactionModel->find($transactionId);
        
        if (!$transaction) {
            return $this->json(['error' => 'Transaction not found'], 404);
        }
        
        // Force a successful payment status for testing
        $testPaymentStatus = [
            'status' => 'success',
            'receipt_number' => 'TEST_' . time(),
            'amount' => $transaction['amount'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->processSuccessfulPayment($transaction, $testPaymentStatus);
            return $this->json([
                'success' => true,
                'message' => 'Vote processed successfully',
                'transaction' => $transaction,
                'test_payment_status' => $testPaymentStatus
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    public function handlePaymentCallback($transactionId)
    {
        try {
            error_log("Payment callback received for transaction: " . $transactionId);
            
            // Get transaction
            $transaction = $this->transactionModel->find($transactionId);
            if (!$transaction) {
                error_log("Transaction not found: " . $transactionId);
                return $this->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }
            
            // Get callback data - handle both POST (webhook) and GET (redirect) callbacks
            $callbackData = [];
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Webhook callback
                $callbackData = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            } else {
                // GET redirect callback from Paystack
                $callbackData = $_GET;
                
                // For GET callbacks, we need to verify the payment with Paystack
                if (isset($_GET['trxref']) || isset($_GET['reference'])) {
                    $reference = $_GET['reference'] ?? $_GET['trxref'];
                    error_log("GET callback received with reference: " . $reference);
                    
                    // Verify payment with Paystack API
                    try {
                        $paymentService = new \SmartCast\Services\PaymentService();
                        $verificationResult = $paymentService->verifyPaymentAndProcessVote($reference);
                        
                        if ($verificationResult['success']) {
                            // Close popup and notify parent window of success
                            echo $this->generatePopupCloseScript([
                                'success' => true,
                                'status' => 'success',
                                'message' => 'Payment completed successfully!',
                                'transaction_id' => $transactionId,
                                'receipt_number' => $verificationResult['receipt_number'] ?? null,
                                'amount' => $verificationResult['amount'] ?? null,
                                'votes_cast' => $verificationResult['votes_cast'] ?? null
                            ]);
                            return;
                        } else {
                            // Close popup and notify parent window of failure
                            echo $this->generatePopupCloseScript([
                                'success' => false,
                                'status' => 'failed',
                                'message' => 'Payment verification failed',
                                'transaction_id' => $transactionId
                            ]);
                            return;
                        }
                    } catch (\Exception $e) {
                        error_log("Payment verification error: " . $e->getMessage());
                        echo $this->generatePopupCloseScript([
                            'success' => false,
                            'status' => 'error',
                            'message' => 'Payment verification error: ' . $e->getMessage(),
                            'transaction_id' => $transactionId
                        ]);
                        return;
                    }
                }
            }
            
            error_log("Callback data received: " . json_encode($callbackData));
            error_log("GET parameters: " . json_encode($_GET));
            error_log("POST data: " . json_encode($_POST));
            
            // Check if this is a Hubtel callback (has ResponseCode field or checkout ID from Hubtel)
            $isHubtelCallback = isset($callbackData['ResponseCode']) || 
                               isset($callbackData['status']) || 
                               isset($callbackData['Data']) ||
                               !empty($_GET['status']) ||
                               !empty($_GET['checkoutId']) ||
                               !empty($_GET['checkoutid']); // Hubtel card payments use query params
            
            if ($isHubtelCallback) {
                error_log("Hubtel callback detected for transaction: " . $transactionId);
                error_log("Hubtel status from GET: " . ($_GET['status'] ?? 'not set'));
                
                // Check if transaction has already been processed successfully
                if ($transaction['status'] === 'success') {
                    error_log("Hubtel callback ignored - transaction already processed: " . $transactionId);
                    
                    // Redirect to success page for card payments (if coming from Hubtel redirect)
                    if (!empty($_GET['checkoutId']) || !empty($_GET['checkoutid']) || !empty($_GET['status'])) {
                        $this->redirect("/payment/status/{$transactionId}");
                        return;
                    }
                    
                    return $this->json(['success' => true, 'message' => 'Payment already processed']);
                }
                
                // Handle Hubtel Checkout (card payment) callback - uses query parameters
                $checkoutId = $_GET['checkoutId'] ?? $_GET['checkoutid'] ?? null;
                
                if (!empty($checkoutId)) {
                    error_log("Hubtel card payment callback - CheckoutId: {$checkoutId}");
                    
                    // Hubtel doesn't send status in callback, just checkout ID
                    // We'll verify the payment status with Hubtel API
                    try {
                        $verificationResult = $this->paymentService->verifyPaymentByVotingTransactionId($transactionId);
                        
                        error_log("Hubtel payment verification result: " . json_encode($verificationResult));
                        
                        if ($verificationResult['success'] && $verificationResult['status'] === 'success') {
                        $paymentDetails = [
                            'status' => 'success',
                            'receipt_number' => $checkoutId ?? 'HUBTEL_CARD_' . time(),
                            'amount' => $transaction['amount']
                        ];
                        
                        $this->processSuccessfulPayment($transaction, $paymentDetails);
                        error_log("Hubtel card payment processed successfully for transaction: " . $transactionId);
                        
                            // Redirect to payment status page with View Receipt and Vote Again buttons
                            $this->redirect("/payment/status/{$transactionId}");
                            return;
                        } elseif ($verificationResult['status'] === 'pending') {
                            // Payment still pending - redirect to status page
                            error_log("Hubtel card payment still pending for transaction: " . $transactionId);
                            $this->redirect("/payment/status/{$transactionId}");
                            return;
                        } else {
                            // Payment failed
                            error_log("Hubtel card payment failed for transaction: " . $transactionId);
                            
                            $this->transactionModel->update($transactionId, [
                                'status' => 'failed',
                                'failure_reason' => $verificationResult['message'] ?? 'Payment verification failed'
                            ]);
                            
                            // Redirect to status page to show failure
                            $this->redirect("/payment/status/{$transactionId}");
                            return;
                        }
                    } catch (\Exception $e) {
                        error_log("Hubtel card payment verification error: " . $e->getMessage());
                        
                        $this->transactionModel->update($transactionId, [
                            'status' => 'failed',
                            'failure_reason' => 'Payment verification error: ' . $e->getMessage()
                        ]);
                        
                        // Redirect to status page to show error
                        $this->redirect("/payment/status/{$transactionId}");
                        return;
                    }
                }
                
                // Handle Hubtel Direct Receive Money (mobile money) callback - uses ResponseCode
                if (isset($callbackData['ResponseCode'])) {
                    if ($callbackData['ResponseCode'] === '0000') {
                        $paymentStatus = 'success';
                        $paymentDetails = [
                            'status' => 'success',
                            'receipt_number' => $callbackData['Data']['TransactionId'] ?? $callbackData['Data']['ClientReference'] ?? 'HUBTEL_' . time(),
                            'amount' => $callbackData['Data']['Amount'] ?? $transaction['amount'],
                            'payment_date' => $callbackData['Data']['PaymentDate'] ?? null,
                            'phone_number' => $callbackData['Data']['CustomerPhoneNumber'] ?? null
                        ];
                        
                        error_log("Hubtel mobile money payment approved at: " . ($callbackData['Data']['PaymentDate'] ?? 'unknown'));
                        $this->processSuccessfulPayment($transaction, $paymentDetails);
                        error_log("Hubtel mobile money payment processed successfully for transaction: " . $transactionId);
                        
                        return $this->json(['success' => true, 'message' => 'Payment processed successfully']);
                    } elseif ($callbackData['ResponseCode'] === '0001') {
                        // Pending - user hasn't approved yet
                        error_log("Hubtel mobile money payment pending for transaction: " . $transactionId);
                        return $this->json(['success' => true, 'message' => 'Payment pending approval']);
                    } else {
                        // Hubtel payment failed
                        error_log("Hubtel mobile money payment failed for transaction: " . $transactionId . " - ResponseCode: " . $callbackData['ResponseCode']);
                        
                        $this->transactionModel->update($transactionId, [
                            'status' => 'failed',
                            'failure_reason' => $callbackData['Message'] ?? 'Payment failed'
                        ]);
                        
                        return $this->json(['success' => true, 'message' => 'Payment failure recorded']);
                    }
                }
            }
            
            // For non-Hubtel callbacks (like Paystack), verify signature
            // Skip this if PaymentService doesn't have verifyCallback method
            if (method_exists($this->paymentService, 'verifyCallback')) {
                $verification = $this->paymentService->verifyCallback($callbackData);
                
                if (!$verification['valid']) {
                    error_log("Invalid callback signature for transaction: " . $transactionId);
                    return $this->json(['success' => false, 'message' => 'Invalid callback'], 400);
                }
            } else {
                error_log("PaymentService::verifyCallback() not available, skipping signature verification");
            }
            
            // For other payment gateways, check payment status
            $paymentStatus = $callbackData['status'] ?? 'unknown';
            error_log("Payment status from callback: " . $paymentStatus);
            
            if ($paymentStatus === 'success' || $paymentStatus === 'completed') {
                // Payment successful - process the vote
                error_log("Processing successful payment for transaction: " . $transactionId);
                
                $paymentDetails = [
                    'status' => 'success',
                    'receipt_number' => $callbackData['receipt_number'] ?? $callbackData['reference'] ?? 'CALLBACK_' . time(),
                    'amount' => $callbackData['amount'] ?? $transaction['amount']
                ];
                
                $this->processSuccessfulPayment($transaction, $paymentDetails);
                error_log("Vote processing completed successfully for transaction: " . $transactionId);
                
                return $this->json(['success' => true, 'message' => 'Payment processed successfully']);
                
            } elseif ($paymentStatus === 'failed' || $paymentStatus === 'cancelled') {
                // Payment failed
                error_log("Payment failed for transaction: " . $transactionId);
                
                $this->transactionModel->update($transactionId, [
                    'status' => 'failed',
                    'failure_reason' => $callbackData['reason'] ?? 'Payment failed'
                ]);
                
                return $this->json(['success' => true, 'message' => 'Payment failure recorded']);
                
            } else {
                error_log("Unknown payment status for transaction: " . $transactionId . " - Status: " . $paymentStatus);
                return $this->json(['success' => false, 'message' => 'Unknown payment status'], 400);
            }
            
        } catch (\Exception $e) {
            error_log("Payment callback error for transaction " . $transactionId . ": " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->json([
                'success' => false, 
                'message' => 'Callback processing failed',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function verifyReceipt()
    {
        $shortCode = $_GET['code'] ?? '';
        
        if (empty($shortCode)) {
            return $this->json(['success' => false, 'message' => 'Receipt code required'], 400);
        }
        
        $receipt = $this->receiptModel->findByShortCode($shortCode);
        
        if (!$receipt) {
            return $this->json(['success' => false, 'message' => 'Receipt not found'], 404);
        }
        
        return $this->json([
            'success' => true,
            'receipt' => $receipt
        ]);
    }

    /**
     * Get payment receipt by transaction ID
     */
    public function getPaymentReceipt($transactionId)
    {
        try {
            // Get transaction details
            $transaction = $this->transactionModel->find($transactionId);
            
            if (!$transaction) {
                return $this->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }
            
            // Get receipt details by transaction ID
            $receipt = $this->receiptModel->getReceiptByTransaction($transactionId);
            
            if (!$receipt) {
                return $this->json([
                    'success' => false,
                    'message' => 'Receipt not found for this transaction'
                ], 404);
            }
            
            // Get additional transaction details
            $contestant = $this->contestantModel->find($transaction['contestant_id']);
            $event = $this->eventModel->find($transaction['event_id']);
            $category = null;
            
            if ($transaction['category_id']) {
                $category = $this->categoryModel->find($transaction['category_id']);
            }
            
            // Prepare receipt data
            $receiptData = [
                'success' => true,
                'receipt' => [
                    'id' => $receipt['id'],
                    'short_code' => $receipt['short_code'],
                    'public_hash' => $receipt['public_hash'],
                    'transaction_id' => $transaction['id'],
                    'amount' => $transaction['amount'],
                    'status' => $transaction['status'],
                    'created_at' => $receipt['created_at'],
                    'transaction' => [
                        'id' => $transaction['id'],
                        'amount' => $transaction['amount'],
                        'status' => $transaction['status'],
                        'provider_reference' => $transaction['provider_reference'],
                        'msisdn' => $transaction['msisdn'],
                        'created_at' => $transaction['created_at']
                    ],
                    'event' => [
                        'id' => $event['id'],
                        'name' => $event['name']
                    ],
                    'contestant' => [
                        'id' => $contestant['id'],
                        'name' => $contestant['name']
                    ],
                    'category' => $category ? [
                        'id' => $category['id'],
                        'name' => $category['name']
                    ] : null
                ]
            ];
            
            return $this->json($receiptData);
            
        } catch (\Exception $e) {
            error_log('Receipt API error: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Receipt not found'], 404);
        }
    }

    /**
     * Show payment receipt page (HTML)
     */
    public function showPaymentReceipt($transactionId)
    {
        try {
            // Get transaction details
            $transaction = $this->transactionModel->find($transactionId);
            
            if (!$transaction) {
                $this->redirect(APP_URL . '/vote-shortcode', 'Transaction not found', 'error');
                return;
            }
            
            // Get receipt details
            $receipt = $this->receiptModel->getReceiptByTransaction($transactionId);
            
            if (!$receipt) {
                $this->redirect(APP_URL . '/vote-shortcode', 'Receipt not found for this transaction', 'error');
                return;
            }
            
            // Get additional transaction details
            $contestant = $this->contestantModel->find($transaction['contestant_id']);
            $event = $this->eventModel->find($transaction['event_id']);
            $category = null;
            
            if ($transaction['category_id']) {
                $category = $this->categoryModel->find($transaction['category_id']);
            }
            
            // Show receipt page
            $this->view('payment/receipt', [
                'receipt' => $receipt,
                'transaction' => $transaction,
                'event' => $event,
                'contestant' => $contestant,
                'category' => $category,
                'title' => 'Payment Receipt'
            ]);
            
        } catch (\Exception $e) {
            error_log('Show payment receipt error: ' . $e->getMessage());
            $this->redirect(APP_URL . '/vote-shortcode', 'Error loading receipt: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Show receipt verification page
     */
    public function showReceiptVerification()
    {
        $this->view('payment/verify-receipt', [
            'title' => 'Verify Receipt - ' . APP_NAME
        ]);
    }

    /**
     * Process receipt verification
     */
    public function processReceiptVerification()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(APP_URL . '/verify-receipt', 'Invalid request method', 'error');
            return;
        }

        try {
            $receiptCode = strtoupper(trim($_POST['receipt_code'] ?? ''));
            
            // Validation
            if (empty($receiptCode)) {
                $this->view('payment/verify-receipt', [
                    'title' => 'Verify Receipt - ' . APP_NAME,
                    'error' => 'Please enter a receipt code',
                    'receipt_code' => $receiptCode
                ]);
                return;
            }

            // Validate receipt code format (8 alphanumeric characters)
            if (!preg_match('/^[A-Z0-9]{8}$/', $receiptCode)) {
                $this->view('payment/verify-receipt', [
                    'title' => 'Verify Receipt - ' . APP_NAME,
                    'error' => 'Invalid receipt code format. Receipt codes are 8 characters long and contain only letters and numbers.',
                    'receipt_code' => $receiptCode
                ]);
                return;
            }

            // Find receipt by short code
            $receipt = $this->receiptModel->findByShortCode($receiptCode);
            
            if (!$receipt) {
                $this->view('payment/verify-receipt', [
                    'title' => 'Verify Receipt - ' . APP_NAME,
                    'error' => 'Receipt not found. Please check the receipt code and try again.',
                    'receipt_code' => $receiptCode
                ]);
                return;
            }

            // Get transaction details
            $transaction = $this->transactionModel->find($receipt['transaction_id']);
            
            if (!$transaction) {
                $this->view('payment/verify-receipt', [
                    'title' => 'Verify Receipt - ' . APP_NAME,
                    'error' => 'Transaction data not found for this receipt.',
                    'receipt_code' => $receiptCode
                ]);
                return;
            }

            // Get related data
            $event = $this->eventModel->find($transaction['event_id']);
            $contestant = $this->contestantModel->find($transaction['contestant_id']);
            $category = null;
            
            if ($transaction['category_id']) {
                $category = $this->categoryModel->find($transaction['category_id']);
            }

            // Verify receipt integrity using hash
            $expectedHash = $this->receiptModel->generatePublicHash($transaction['id'], $receiptCode);
            $isValid = hash_equals($receipt['public_hash'], $expectedHash);
            
            // If verification fails with new method, try legacy verification
            if (!$isValid) {
                error_log("Primary hash verification failed for receipt {$receiptCode}, trying legacy verification");
                
                // For receipts created before the hash fix, we'll verify based on:
                // 1. Receipt exists in database
                // 2. Transaction ID matches
                // 3. Receipt code matches
                // 4. Transaction is successful
                $legacyValid = (
                    !empty($receipt) && 
                    $receipt['transaction_id'] == $transaction['id'] && 
                    $receipt['short_code'] === $receiptCode &&
                    $transaction['status'] === 'success'
                );
                
                if ($legacyValid) {
                    error_log("Legacy verification passed for receipt {$receiptCode}");
                    $isValid = true;
                    
                    // Optionally update the hash to new format for future verifications
                    try {
                        $this->receiptModel->update($receipt['id'], [
                            'public_hash' => $expectedHash
                        ]);
                        error_log("Updated receipt {$receiptCode} hash to new format");
                    } catch (\Exception $e) {
                        error_log("Failed to update receipt hash: " . $e->getMessage());
                    }
                } else {
                    error_log("Legacy verification also failed for receipt {$receiptCode}");
                }
            }

            // Show verification result
            $this->view('payment/verify-receipt', [
                'title' => 'Receipt Verification Result - ' . APP_NAME,
                'receipt' => $receipt,
                'transaction' => $transaction,
                'event' => $event,
                'contestant' => $contestant,
                'category' => $category,
                'is_valid' => $isValid,
                'receipt_code' => $receiptCode
            ]);

        } catch (\Exception $e) {
            error_log('Receipt verification error: ' . $e->getMessage());
            $this->view('payment/verify-receipt', [
                'title' => 'Verify Receipt - ' . APP_NAME,
                'error' => 'An error occurred while verifying the receipt. Please try again.',
                'receipt_code' => $_POST['receipt_code'] ?? ''
            ]);
        }
    }

    public function simulatePaymentCallback($transactionId)
    {
        try {
            error_log("Simulating payment callback for transaction: " . $transactionId);
            
            // Check if transaction exists first
            $transaction = $this->transactionModel->find($transactionId);
            if (!$transaction) {
                return $this->json(['success' => false, 'message' => 'Transaction not found: ' . $transactionId], 404);
            }
            
            error_log("Transaction found: " . json_encode($transaction));
            
            // Test endpoint to simulate a successful payment callback
            $simulatedCallback = [
                'status' => 'success',
                'transaction_id' => $transactionId,
                'receipt_number' => 'SIM_' . time(),
                'amount' => $transaction['amount'], // Use actual transaction amount
                'reference' => 'VOTE_' . $transactionId,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            error_log("Simulated callback data: " . json_encode($simulatedCallback));
            
            // Simulate the callback by calling our handler
            $_POST = $simulatedCallback;
            return $this->handlePaymentCallback($transactionId);
            
        } catch (\Exception $e) {
            error_log("Simulation error: " . $e->getMessage());
            return $this->json([
                'success' => false, 
                'message' => 'Simulation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show shortcode voting page
     */
    public function showShortcodeVoting()
    {
        $this->view('voting/shortcode', [
            'title' => 'Vote by Shortcode - SmartCast'
        ]);
    }

    /**
     * Show direct voting page with parameters
     */
    public function showDirectVoting()
    {
        // Get parameters from URL
        $contestantId = $_GET['contestant_id'] ?? null;
        $categoryId = $_GET['category_id'] ?? null;
        $eventId = $_GET['event_id'] ?? null;
        $source = $_GET['source'] ?? 'direct';

        if (!$contestantId || !$categoryId || !$eventId) {
            $this->redirect(APP_URL . '/vote-shortcode', 'Invalid voting parameters', 'error');
            return;
        }

        try {
            // Get contestant details
            $contestant = $this->contestantModel->find($contestantId);
            if (!$contestant) {
                $this->redirect(APP_URL . '/vote-shortcode', 'Contestant not found', 'error');
                return;
            }

            // Get event details
            $event = $this->eventModel->find($eventId);
            if (!$event) {
                $this->redirect(APP_URL . '/vote-shortcode', 'Event not found', 'error');
                return;
            }

            // Get category details
            $category = $this->categoryModel->find($categoryId);
            if (!$category) {
                $this->redirect(APP_URL . '/vote-shortcode', 'Category not found', 'error');
                return;
            }

            // Check if voting is allowed
            if (!$this->isVotingAllowed($event)) {
                $this->redirect(APP_URL . '/vote-shortcode', 'Voting is not currently available for this event', 'error');
                return;
            }

            // Get vote bundles
            $bundles = $this->bundleModel->findAll(['active' => 1]);

            // Get contestant category info for shortcode
            $contestantCategoryModel = new \SmartCast\Models\ContestantCategory();
            $contestantCategories = $contestantCategoryModel->findAll([
                'contestant_id' => $contestantId,
                'category_id' => $categoryId
            ]);
            $contestantCategory = !empty($contestantCategories) ? $contestantCategories[0] : null;

            $this->view('voting/direct', [
                'event' => $event,
                'contestant' => $contestant,
                'category' => $category,
                'contestantCategory' => $contestantCategory,
                'bundles' => $bundles,
                'source' => $source,
                'title' => 'Vote for ' . $contestant['name'] . ' - ' . $event['name']
            ]);

        } catch (\Exception $e) {
            error_log('Direct voting error: ' . $e->getMessage());
            $this->redirect(APP_URL . '/vote-shortcode', 'An error occurred while loading the voting page', 'error');
        }
    }

    /**
     * Check if voting is allowed for an event
     */
    private function isVotingAllowed($event)
    {
        // Check if event is active
        if ($event['status'] !== 'active') {
            return false;
        }

        // Check if event is within voting period
        $now = time();
        $startTime = strtotime($event['start_date']);
        $endTime = strtotime($event['end_date']);

        if ($now < $startTime || $now > $endTime) {
            return false;
        }

        return true;
    }

    /**
     * Show payment status page
     */
    public function showPaymentStatus($transactionId)
    {
        try {
            // Get transaction details
            $transaction = $this->transactionModel->find($transactionId);
            
            if (!$transaction) {
                $this->redirect(APP_URL . '/vote-shortcode', 'Transaction not found', 'error');
                return;
            }

            // Get related data
            $event = $this->eventModel->find($transaction['event_id']);
            $contestant = $this->contestantModel->find($transaction['contestant_id']);
            $category = null;
            
            if ($transaction['category_id']) {
                $category = $this->categoryModel->find($transaction['category_id']);
            }

            // Get voter information from session if available (for shortcode voting)
            $voterInfo = $_SESSION["transaction_{$transactionId}_voter_info"] ?? null;
            
            // Get vote information from votes table
            $voteInfo = null;
            try {
                $voteInfo = $this->transactionModel->getDatabase()->selectOne("
                    SELECT quantity, created_at 
                    FROM votes 
                    WHERE transaction_id = :transaction_id
                ", ['transaction_id' => $transactionId]);
            } catch (\Exception $e) {
                error_log("Could not get vote info: " . $e->getMessage());
            }
            
            // Get bundle information for vote quantity fallback
            $bundle = null;
            if ($transaction['bundle_id']) {
                $bundle = $this->bundleModel->find($transaction['bundle_id']);
            }
            
            // Merge voter info into transaction for display
            if ($voterInfo) {
                $transaction['voter_name'] = $voterInfo['voter_name'];
                $transaction['voter_email'] = $voterInfo['voter_email'];
                $transaction['vote_quantity'] = $voterInfo['vote_quantity'];
                $transaction['source'] = $voterInfo['source'];
            } else {
                // For normal voting, try to get voter name from transaction or use phone
                if (!empty($transaction['msisdn'])) {
                    $transaction['voter_name'] = 'Voter (' . substr($transaction['msisdn'], -4) . ')';
                } else {
                    $transaction['voter_name'] = 'Voter';
                }
                $transaction['source'] = 'normal';
            }
            
            // Set vote quantity from votes table, bundle, or default to 1
            if ($voteInfo && $voteInfo['quantity']) {
                $transaction['vote_quantity'] = $voteInfo['quantity'];
            } elseif ($bundle && $bundle['votes']) {
                $transaction['vote_quantity'] = $bundle['votes'];
            } elseif (!isset($transaction['vote_quantity'])) {
                $transaction['vote_quantity'] = 1; // Default fallback
            }

            $this->view('payment/status', [
                'transaction' => $transaction,
                'event' => $event,
                'contestant' => $contestant,
                'category' => $category,
                'title' => 'Payment Status'
            ]);

        } catch (\Exception $e) {
            error_log('Payment status error: ' . $e->getMessage());
            $this->redirect(APP_URL . '/vote-shortcode', 'An error occurred while loading payment status', 'error');
        }
    }

    // processDirectVote method removed - now using unified processVote method for both standard and direct voting
    
    /**
     * Generate script to close popup and communicate with parent window
     */
    private function generatePopupCloseScript($data)
    {
        $jsonData = json_encode($data);
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Payment Complete</title>
        </head>
        <body>
            <div style='text-align: center; padding: 20px; font-family: Arial, sans-serif;'>
                <h3>" . ($data['success'] ? '✅ Payment Successful!' : '❌ Payment Failed') . "</h3>
                <p>" . htmlspecialchars($data['message']) . "</p>
                <p><small>This window will close automatically...</small></p>
            </div>
            <script>
                // Send result to parent window
                if (window.opener && !window.opener.closed) {
                    window.opener.postMessage({
                        type: 'PAYMENT_COMPLETE',
                        data: {$jsonData}
                    }, '*');
                }
                
                // Close popup after a short delay
                setTimeout(function() {
                    window.close();
                }, 2000);
            </script>
        </body>
        </html>";
    }
    
    /**
     * Handle Flutterwave webhook
     */
    public function flutterwaveWebhook()
    {
        try {
            $payload = file_get_contents('php://input');
            $signature = $_SERVER['HTTP_VERIF_HASH'] ?? '';
            
            // Log webhook for debugging
            error_log("Flutterwave Webhook Received: " . $payload);
            
            // Process webhook through PaymentService
            $result = $this->paymentService->processWebhook('flutterwave', $payload, $signature);
            
            if ($result['success']) {
                http_response_code(200);
                echo json_encode(['status' => 'success']);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => $result['message'] ?? 'Webhook processing failed']);
            }
            
        } catch (\Exception $e) {
            error_log("Flutterwave Webhook Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle Paystack webhook
     */
    public function paystackWebhook()
    {
        try {
            $payload = file_get_contents('php://input');
            $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
            
            error_log("Paystack Webhook Received: " . $payload);
            
            $result = $this->paymentService->processWebhook('paystack', $payload, $signature);
            
            if ($result['success']) {
                http_response_code(200);
                echo json_encode(['status' => 'success']);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => $result['message'] ?? 'Webhook processing failed']);
            }
            
        } catch (\Exception $e) {
            error_log("Paystack Webhook Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle Hubtel webhook
     */
    public function hubtelWebhook()
    {
        try {
            $payload = file_get_contents('php://input');
            
            error_log("Hubtel Webhook Received: " . $payload);
            
            $result = $this->paymentService->processWebhook('hubtel', $payload, null);
            
            if ($result['success']) {
                http_response_code(200);
                echo json_encode(['status' => 'success']);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => $result['message'] ?? 'Webhook processing failed']);
            }
            
        } catch (\Exception $e) {
            error_log("Hubtel Webhook Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

}
