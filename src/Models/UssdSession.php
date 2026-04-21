<?php

namespace SmartCast\Models;

/**
 * USSD Session Model
 */
class UssdSession extends BaseModel
{
    protected $table = 'ussd_sessions';
    protected $fillable = [
        'session_id', 'msisdn', 'state', 'data'
    ];
    
    // USSD States
    const STATE_WELCOME = 'welcome';
    const STATE_MAIN_MENU = 'main_menu';
    const STATE_ENTER_SHORTCODE = 'enter_shortcode';
    const STATE_SELECT_EVENT = 'select_event';
    const STATE_SELECT_CATEGORY = 'select_category';
    const STATE_SELECT_CONTESTANT = 'select_contestant';
    const STATE_SELECT_VOTE_TYPE = 'select_vote_type';
    const STATE_SELECT_BUNDLE = 'select_bundle';
    const STATE_ENTER_CUSTOM_VOTES = 'enter_custom_votes';
    const STATE_CONFIRM_VOTE = 'confirm_vote';
    const STATE_PAYMENT = 'payment';
    const STATE_SUCCESS = 'success';
    const STATE_ERROR = 'error';
    const STATE_ENDED = 'ended';
    
    public function createSession($sessionId, $msisdn, $initialState = self::STATE_WELCOME, $initialData = [])
    {
        // Clean up old sessions for this MSISDN
        $this->cleanupOldSessions($msisdn);
        
        return $this->create([
            'session_id' => $sessionId,
            'msisdn' => $msisdn,
            'state' => $initialState,
            'data' => json_encode($initialData)
        ]);
    }
    
    public function getSession($sessionId)
    {
        $session = $this->findAll(['session_id' => $sessionId], null, 1);
        
        if (empty($session)) {
            return null;
        }
        
        $session = $session[0];
        $session['data'] = json_decode($session['data'], true) ?: [];
        
        return $session;
    }
    
    public function updateSession($sessionId, $newState, $data = null)
    {
        $updateData = ['state' => $newState];
        
        if ($data !== null) {
            $session = $this->getSession($sessionId);
            if ($session) {
                $sessionData = array_merge($session['data'], $data);
                $updateData['data'] = json_encode($sessionData);
            }
        }
        
        return $this->db->update(
            $this->table,
            $updateData,
            'session_id = :session_id',
            ['session_id' => $sessionId]
        );
    }
    
    public function setSessionData($sessionId, $key, $value)
    {
        $session = $this->getSession($sessionId);
        if (!$session) {
            return false;
        }
        
        $sessionData = $session['data'];
        $sessionData[$key] = $value;
        
        return $this->db->update(
            $this->table,
            ['data' => json_encode($sessionData)],
            'session_id = :session_id',
            ['session_id' => $sessionId]
        );
    }
    
    /**
     * Update session columns (tenant_id, service_code, etc.)
     */
    public function updateSessionColumns($sessionId, $columns)
    {
        return $this->db->update(
            $this->table,
            $columns,
            'session_id = :session_id',
            ['session_id' => $sessionId]
        );
    }
    
    public function getSessionData($sessionId, $key = null)
    {
        $session = $this->getSession($sessionId);
        if (!$session) {
            return null;
        }
        
        if ($key === null) {
            return $session['data'];
        }
        
        return $session['data'][$key] ?? null;
    }
    
    public function endSession($sessionId)
    {
        return $this->updateSession($sessionId, self::STATE_ENDED);
    }
    
    public function deleteSession($sessionId)
    {
        return $this->db->delete(
            $this->table,
            'session_id = :session_id',
            ['session_id' => $sessionId]
        );
    }
    
    public function cleanupOldSessions($msisdn, $olderThanMinutes = 30)
    {
        $cutoffTime = date('Y-m-d H:i:s', time() - ($olderThanMinutes * 60));
        
        return $this->db->delete(
            $this->table,
            'msisdn = :msisdn AND updated_at < :cutoff_time',
            [
                'msisdn' => $msisdn,
                'cutoff_time' => $cutoffTime
            ]
        );
    }
    
    public function getActiveSessionByMsisdn($msisdn)
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE msisdn = :msisdn 
            AND state NOT IN (:ended_state, :success_state)
            ORDER BY updated_at DESC 
            LIMIT 1
        ";
        
        $session = $this->db->selectOne($sql, [
            'msisdn' => $msisdn,
            'ended_state' => self::STATE_ENDED,
            'success_state' => self::STATE_SUCCESS
        ]);
        
        if ($session) {
            $session['data'] = json_decode($session['data'], true) ?: [];
        }
        
        return $session;
    }
    
    public function processUssdInput($sessionId, $input)
    {
        $session = $this->getSession($sessionId);
        if (!$session) {
            return $this->createErrorResponse('Session not found');
        }
        
        switch ($session['state']) {
            case self::STATE_WELCOME:
                return $this->handleWelcomeState($sessionId, $input);
            
            case self::STATE_MAIN_MENU:
                return $this->handleMainMenuState($sessionId, $input);
            
            case self::STATE_ENTER_SHORTCODE:
                return $this->handleEnterShortcodeState($sessionId, $input);
                
            case self::STATE_SELECT_EVENT:
                return $this->handleSelectEventState($sessionId, $input);
                
            case self::STATE_SELECT_CATEGORY:
                return $this->handleSelectCategoryState($sessionId, $input);
                
            case self::STATE_SELECT_CONTESTANT:
                return $this->handleSelectContestantState($sessionId, $input);
                
            case self::STATE_SELECT_VOTE_TYPE:
                return $this->handleSelectVoteTypeState($sessionId, $input);
                
            case self::STATE_SELECT_BUNDLE:
                return $this->handleSelectBundleState($sessionId, $input);
                
            case self::STATE_ENTER_CUSTOM_VOTES:
                return $this->handleEnterCustomVotesState($sessionId, $input);
                
            case self::STATE_CONFIRM_VOTE:
                return $this->handleConfirmVoteState($sessionId, $input);
                
            default:
                return $this->createErrorResponse('Invalid session state');
        }
    }
    
    private function handleWelcomeState($sessionId, $input)
    {
        // Show main menu
        $this->updateSession($sessionId, self::STATE_MAIN_MENU);
        
        // Get tenant info from session
        $sessionData = $this->getSessionData($sessionId);
        $tenantId = $sessionData['tenant_id'] ?? null;
        
        $welcomeMessage = "Welcome to SmartCastGH!";
        
        if ($tenantId) {
            $tenantModel = new Tenant();
            $tenant = $tenantModel->find($tenantId);
            
            if ($tenant) {
                if (!empty($tenant['ussd_welcome_message'])) {
                    // Truncate custom welcome message if too long
                    $customMessage = $tenant['ussd_welcome_message'];
                    // If it starts with "Welcome to", extract the part after it
                    if (stripos($customMessage, 'Welcome to ') === 0) {
                        $namePart = substr($customMessage, 11); // Remove "Welcome to "
                        if (strlen($namePart) > 15) {
                            $namePart = substr($namePart, 0, 15);
                        }
                        $welcomeMessage = "Welcome to {$namePart}";
                    } else {
                        // If custom format, just truncate to reasonable length
                        if (strlen($customMessage) > 30) {
                            $customMessage = substr($customMessage, 0, 30);
                        }
                        $welcomeMessage = $customMessage;
                    }
                } else {
                    // Use tenant name, truncate if too long
                    $tenantName = $tenant['name'] ?? 'SmartCastGH';
                    if (strlen($tenantName) > 15) {
                        $tenantName = substr($tenantName, 0, 15);
                    }
                    $welcomeMessage = "Welcome to {$tenantName}!";
                }
            }
        }
        
        $menu = $welcomeMessage . "\n\n";
        $menu .= "1. Vote for Nominee\n";
        $menu .= "2. Vote on an Event\n";
        $menu .= "3. Create an Event\n";
        $menu .= "4. Exit";
        
        return $this->createResponse($menu);
    }
    
    private function handleMainMenuState($sessionId, $input)
    {
        $sessionData = $this->getSessionData($sessionId);
        $tenantId = $sessionData['tenant_id'] ?? null;
        
        switch ($input) {
            case '1':
                // Vote for Nominee - Enter Shortcode
                $this->updateSession($sessionId, self::STATE_ENTER_SHORTCODE);
                return $this->createResponse("Enter nominee shortcode:");
                
            case '2':
                // Vote on Event - Show events list
                return $this->showEventsList($sessionId, $tenantId);
                
            case '3':
                // Create an Event - Send registration link
                return $this->sendRegistrationLink($sessionId);
                
            case '4':
            case '0':
                // Exit
                $this->endSession($sessionId);
                return $this->createResponse('Thank you for using SmartCastGH!', true);
                
            default:
                return $this->createResponse('Invalid selection. Please try again.');
        }
    }
    
    private function handleEnterShortcodeState($sessionId, $input)
    {
        $sessionData = $this->getSessionData($sessionId);
        $shortCode = strtoupper(trim($input));
        
        if (empty($shortCode)) {
            return $this->createResponse('Please enter a valid shortcode.');
        }
        
        // Find contestant by shortcode across ALL tenants
        // This allows shared USSD codes to work for any tenant's nominees
        $contestantCategoryModel = new ContestantCategory();
        $result = $contestantCategoryModel->findByShortCode($shortCode);
        
        if (!$result) {
            $menu = "Nominee code '{$shortCode}' not found.\n\nPlease try again or dial again to exit.";
            return $this->createResponse($menu);
        }
        
        // Get contestant details
        $contestantModel = new Contestant();
        $contestant = $contestantModel->find($result['contestant_id']);
        
        if (!$contestant || !$contestant['active']) {
            return $this->createResponse('Nominee not available for voting.', true);
        }
        
        // Get event details
        $eventModel = new Event();
        $event = $eventModel->find($result['event_id']);
        
        if (!$event || $event['status'] !== 'active') {
            return $this->createResponse('Event is not active for voting.', true);
        }
        
        // Get category details
        $categoryModel = new Category();
        $category = $categoryModel->find($result['category_id']);
        
        // Store tenant_id from the event for transaction tracking
        $this->updateSessionColumns($sessionId, [
            'tenant_id' => $event['tenant_id']
        ]);
        
        // Skip vote type selection - go directly to custom vote entry
        $this->updateSession($sessionId, self::STATE_ENTER_CUSTOM_VOTES, [
            'selected_event' => $event,
            'selected_category' => $category,
            'selected_contestant' => $contestant,
            'tenant_id' => $event['tenant_id']
        ]);
        
        return $this->createResponse("Vote for: " . $contestant['name'] . "\nEnter number of votes (1-10,000):");
    }
    
    private function showEventsList($sessionId, $tenantId, $page = 1)
    {
        // Get active events for this tenant
        $eventModel = new Event();
        
        if ($tenantId) {
            $events = $eventModel->findAll([
                'tenant_id' => $tenantId,
                'status' => 'active'
            ]);
        } else {
            $events = $eventModel->getPublicEvents();
        }
        
        if (empty($events)) {
            return $this->createResponse('No active events available.', true);
        }
        
        // Pagination settings
        $itemsPerPage = 5;
        $totalEvents = count($events);
        $totalPages = ceil($totalEvents / $itemsPerPage);
        $page = max(1, min($page, $totalPages)); // Ensure valid page
        
        // Get events for current page
        $startIndex = ($page - 1) * $itemsPerPage;
        $pageEvents = array_slice($events, $startIndex, $itemsPerPage);
        
        // Update session state with all events and current page
        $this->updateSession($sessionId, self::STATE_SELECT_EVENT, [
            'events' => $events,
            'current_page' => $page,
            'total_pages' => $totalPages
        ]);
        
        // Build menu
        $menu = "Select an event";
        if ($totalPages > 1) {
            $menu .= " (Page {$page}/{$totalPages})";
        }
        $menu .= ":\n";
        
        foreach ($pageEvents as $index => $event) {
            $actualIndex = $startIndex + $index;
            $eventName = $event['name'];
            
            // Truncate long event names (max 30 chars)
            if (strlen($eventName) > 30) {
                $eventName = substr($eventName, 0, 27) . '...';
            }
            
            $menu .= ($actualIndex + 1) . ". " . $eventName . "\n";
        }
        
        // Add navigation options
        if ($page < $totalPages) {
            $menu .= "9. Next Page\n";
        }
        if ($page > 1) {
            $menu .= "8. Previous Page\n";
        }
        $menu .= "0. Back";
        
        return $this->createResponse($menu);
    }
    
    private function sendRegistrationLink($sessionId)
    {
        $session = $this->getSession($sessionId);
        $phoneNumber = $session['msisdn'];
        
        // Build registration URL
        $registrationUrl = APP_URL . '/register';
        
        // TODO: Send SMS with registration link
        // $smsService = new SmsService();
        // $smsService->send($phoneNumber, "Create your event on SmartCastGH: {$registrationUrl}");
        
        $this->endSession($sessionId);
        
        $message = "Registration link sent to {$phoneNumber}\n\n";
        $message .= "Visit: {$registrationUrl}\n\n";
        $message .= "Thank you!";
        
        return $this->createResponse($message, true);
    }
    
    private function handleSelectEventState($sessionId, $input)
    {
        if ($input == '0') {
            // Go back to main menu
            return $this->handleWelcomeState($sessionId, '');
        }
        
        $sessionData = $this->getSessionData($sessionId);
        $events = $sessionData['events'] ?? [];
        $currentPage = $sessionData['current_page'] ?? 1;
        $totalPages = $sessionData['total_pages'] ?? 1;
        $tenantId = $sessionData['tenant_id'] ?? null;
        
        // Handle pagination
        if ($input == '9' && $currentPage < $totalPages) {
            // Next page
            return $this->showEventsList($sessionId, $tenantId, $currentPage + 1);
        }
        
        if ($input == '8' && $currentPage > 1) {
            // Previous page
            return $this->showEventsList($sessionId, $tenantId, $currentPage - 1);
        }
        
        $eventIndex = (int)$input - 1;
        if (!isset($events[$eventIndex])) {
            return $this->createResponse('Invalid selection. Please try again.');
        }
        
        $selectedEvent = $events[$eventIndex];
        
        // Get categories for this event
        $categoryModel = new Category();
        $categories = $categoryModel->getCategoriesByEvent($selectedEvent['id']);
        
        if (empty($categories)) {
            // No categories, go directly to contestants
            $contestantModel = new Contestant();
            $contestants = $contestantModel->getContestantsByEvent($selectedEvent['id']);
            
            $this->updateSession($sessionId, self::STATE_SELECT_CONTESTANT, [
                'selected_event' => $selectedEvent,
                'contestants' => $contestants
            ]);
            
            return $this->buildContestantMenu($contestants);
        }
        
        // Update session with selected event and categories
        $this->updateSession($sessionId, self::STATE_SELECT_CATEGORY, [
            'selected_event' => $selectedEvent,
            'categories' => $categories
        ]);
        
        return $this->buildCategoryMenu($categories);
    }
    
    private function handleSelectCategoryState($sessionId, $input)
    {
        if ($input == '0') {
            return $this->handleWelcomeState($sessionId, '');
        }
        
        $sessionData = $this->getSessionData($sessionId);
        $categories = $sessionData['categories'] ?? [];
        
        $categoryIndex = (int)$input - 1;
        if (!isset($categories[$categoryIndex])) {
            return $this->createResponse('Invalid selection. Please try again.');
        }
        
        $selectedCategory = $categories[$categoryIndex];
        
        // Get contestants for this category
        $contestantModel = new Contestant();
        $contestants = $contestantModel->getContestantsByCategory($selectedCategory['id']);
        
        $this->updateSession($sessionId, self::STATE_SELECT_CONTESTANT, [
            'selected_category' => $selectedCategory,
            'contestants' => $contestants
        ]);
        
        return $this->buildContestantMenu($contestants);
    }
    
    private function handleSelectContestantState($sessionId, $input)
    {
        if ($input == '0') {
            // Go back to category selection or event selection
            $sessionData = $this->getSessionData($sessionId);
            if (isset($sessionData['categories'])) {
                $this->updateSession($sessionId, self::STATE_SELECT_CATEGORY);
                return $this->buildCategoryMenu($sessionData['categories']);
            } else {
                return $this->handleWelcomeState($sessionId, '');
            }
        }
        
        $sessionData = $this->getSessionData($sessionId);
        $contestants = $sessionData['contestants'] ?? [];
        
        $contestantIndex = (int)$input - 1;
        if (!isset($contestants[$contestantIndex])) {
            return $this->createResponse('Invalid selection. Please try again.');
        }
        
        $selectedContestant = $contestants[$contestantIndex];
        
        // Skip vote type selection - go directly to custom vote entry
        $this->updateSession($sessionId, self::STATE_ENTER_CUSTOM_VOTES, [
            'selected_contestant' => $selectedContestant
        ]);
        
        return $this->createResponse("Vote for: " . $selectedContestant['name'] . "\nEnter number of votes (1-10,000):");
    }
    
    private function handleSelectVoteTypeState($sessionId, $input)
    {
        if ($input == '0') {
            // Go back to contestant selection
            $sessionData = $this->getSessionData($sessionId);
            $this->updateSession($sessionId, self::STATE_SELECT_CONTESTANT);
            return $this->buildContestantMenu($sessionData['contestants']);
        }
        
        $sessionData = $this->getSessionData($sessionId);
        
        switch ($input) {
            case '1':
                // Vote Bundles - Show bundles list
                $bundleModel = new VoteBundle();
                $bundles = $bundleModel->getBundlesByEvent($sessionData['selected_event']['id']);
                
                if (empty($bundles)) {
                    return $this->createResponse('No vote bundles available. Please try custom votes.', true);
                }
                
                $this->updateSession($sessionId, self::STATE_SELECT_BUNDLE, [
                    'bundles' => $bundles
                ]);
                
                return $this->buildBundleMenu($bundles, $sessionData['selected_contestant']['name']);
                
            case '2':
                // Custom Votes - Ask for vote count
                $this->updateSession($sessionId, self::STATE_ENTER_CUSTOM_VOTES);
                return $this->createResponse("Enter number of votes (1-10,000):");
                
            default:
                return $this->createResponse('Invalid selection. Please try again.');
        }
    }
    
    private function handleSelectBundleState($sessionId, $input)
    {
        if ($input == '0') {
            $sessionData = $this->getSessionData($sessionId);
            $this->updateSession($sessionId, self::STATE_SELECT_CONTESTANT);
            return $this->buildContestantMenu($sessionData['contestants']);
        }
        
        $sessionData = $this->getSessionData($sessionId);
        $bundles = $sessionData['bundles'] ?? [];
        
        $bundleIndex = (int)$input - 1;
        if (!isset($bundles[$bundleIndex])) {
            return $this->createResponse('Invalid selection. Please try again.');
        }
        
        $selectedBundle = $bundles[$bundleIndex];
        
        $this->updateSession($sessionId, self::STATE_CONFIRM_VOTE, [
            'selected_bundle' => $selectedBundle
        ]);
        
        // Build confirmation message
        $contestant = $sessionData['selected_contestant'];
        $event = $sessionData['selected_event'];
        
        $message = "Confirm your vote:\n";
        $message .= "Event: " . $event['name'] . "\n";
        $message .= "Contestant: " . $contestant['name'] . "\n";
        $message .= "Votes: " . $selectedBundle['votes'] . "\n";
        $message .= "Amount: GHS " . number_format($selectedBundle['price'], 2) . "\n\n";
        $message .= "1. Confirm\n0. Cancel";
        
        return $this->createResponse($message);
    }
    
    private function handleConfirmVoteState($sessionId, $input)
    {
        if ($input == '0') {
            $sessionData = $this->getSessionData($sessionId);
            $this->updateSession($sessionId, self::STATE_ENTER_CUSTOM_VOTES);
            return $this->createResponse("Vote for: " . $sessionData['selected_contestant']['name'] . "\nEnter number of votes (1-10,000):");
        }
        
        if ($input == '1') {
            return $this->processVote($sessionId);
        }
        
        return $this->createResponse('Invalid selection. Please enter 1 to confirm or 0 to cancel.');
    }
    
    private function handleEnterCustomVotesState($sessionId, $input)
    {
        if ($input == '0') {
            // Go back to contestant selection
            $sessionData = $this->getSessionData($sessionId);
            $this->updateSession($sessionId, self::STATE_SELECT_CONTESTANT);
            return $this->buildContestantMenu($sessionData['contestants']);
        }
        
        // Validate vote count
        $voteCount = (int)$input;
        if ($voteCount < 1 || $voteCount > 10000) {
            return $this->createResponse("Invalid vote count. Please enter a number between 1 and 10,000:");
        }
        
        $sessionData = $this->getSessionData($sessionId);
        $event = $sessionData['selected_event'];
        
        // Calculate price (price per vote from event settings)
        $pricePerVote = $event['vote_price'] ?? 0.50; // Default GHS 0.50 per vote
        $totalPrice = $voteCount * $pricePerVote;
        
        // Get a reference bundle ID (use any existing bundle to satisfy foreign key constraint)
        // This is the same approach used by web voting
        $bundleModel = new VoteBundle();
        $bundles = $bundleModel->getBundlesByEvent($event['id']);
        
        $referenceBundleId = null;
        if (!empty($bundles)) {
            // Use the first available bundle as reference
            $referenceBundleId = $bundles[0]['id'];
        } else {
            // Create a default reference bundle if none exist
            $referenceBundleId = $bundleModel->create([
                'event_id' => $event['id'],
                'name' => 'Single Vote',
                'votes' => 1,
                'price' => $pricePerVote,
                'active' => 1
            ]);
        }
        
        // Create custom bundle data with reference bundle ID
        $customBundle = [
            'id' => $referenceBundleId, // Use reference bundle ID to satisfy foreign key
            'votes' => $voteCount,
            'price' => $totalPrice,
            'is_custom' => true
        ];
        
        $this->updateSession($sessionId, self::STATE_CONFIRM_VOTE, [
            'selected_bundle' => $customBundle
        ]);
        
        // Build confirmation message
        $contestant = $sessionData['selected_contestant'];
        
        $message = "Confirm your vote:\n";
        $message .= "Event: " . $event['name'] . "\n";
        $message .= "Contestant: " . $contestant['name'] . "\n";
        $message .= "Votes: " . $voteCount . "\n";
        $message .= "Amount: GHS " . number_format($totalPrice, 2) . "\n\n";
        $message .= "1. Confirm\n0. Cancel";
        
        return $this->createResponse($message);
    }
    
    private function processVote($sessionId)
    {
        $session = $this->getSession($sessionId);
        $sessionData = $session['data'];
        
        try {
            // Get tenant_id from session data
            $tenantId = $sessionData['tenant_id'] ?? $sessionData['selected_event']['tenant_id'];
            
            // Create pending transaction to track the vote
            $transactionModel = new Transaction();
            
            // Prepare transaction data
            $selectedBundle = $sessionData['selected_bundle'];
            $transactionData = [
                'tenant_id' => $tenantId,
                'event_id' => $sessionData['selected_event']['id'],
                'contestant_id' => $sessionData['selected_contestant']['id'],
                'category_id' => $sessionData['selected_category']['id'] ?? null,
                'bundle_id' => $selectedBundle['id'], // Always present now (reference bundle for custom votes)
                'amount' => $selectedBundle['price'],
                'msisdn' => $session['msisdn'],
                'status' => 'pending',
                'provider' => 'hubtel_ussd',
                'provider_reference' => $sessionId // Use session ID as reference
            ];
            
            // Log if this is a custom vote (price doesn't match bundle price)
            if (!empty($selectedBundle['is_custom'])) {
                error_log("USSD: Creating custom vote transaction - Votes: {$selectedBundle['votes']}, Price: {$selectedBundle['price']}, Reference Bundle: {$selectedBundle['id']}");
            }
            
            $transactionId = $transactionModel->createTransaction($transactionData);
            
            // Store transaction ID and vote count in session for fulfillment callback
            $this->setSessionData($sessionId, 'transaction_id', $transactionId);
            $this->setSessionData($sessionId, 'vote_count', $selectedBundle['votes']);
            
            // Update session state
            $this->updateSession($sessionId, self::STATE_PAYMENT);
            
            // Log for debugging
            error_log("USSD: Created pending transaction {$transactionId} for session {$sessionId}");
            error_log("USSD: Vote details - Event: {$sessionData['selected_event']['id']}, Contestant: {$sessionData['selected_contestant']['id']}, Votes: {$sessionData['selected_bundle']['votes']}");
            
            // Return AddToCart response for Hubtel to collect payment
            // This is handled by UssdController, we just return the data
            return [
                'message' => 'Please wait for payment prompt or Dial *170# to approve.',
                'end' => true,
                'add_to_cart' => true,
                'item' => [
                    'ItemName' => "Vote for {$sessionData['selected_contestant']['name']}",
                    'Qty' => $sessionData['selected_bundle']['votes'],
                    'Price' => $sessionData['selected_bundle']['price']
                ],
                'session_id' => $sessionId,
                'transaction_id' => $transactionId
            ];
            
        } catch (\Exception $e) {
            error_log("USSD Vote Error: " . $e->getMessage());
            error_log("USSD Vote Error Stack: " . $e->getTraceAsString());
            error_log("USSD Vote Error Session Data: " . json_encode($sessionData));
            $this->updateSession($sessionId, self::STATE_ERROR);
            
            // More descriptive error message
            $errorMsg = 'Vote failed. ';
            if (strpos($e->getMessage(), 'bundle') !== false) {
                $errorMsg .= 'Bundle error. ';
            }
            $errorMsg .= 'Please try again.';
            
            return $this->createResponse($errorMsg, true);
        }
    }
    
    private function buildCategoryMenu($categories)
    {
        $menu = "Select a category:\n";
        foreach ($categories as $index => $category) {
            $menu .= ($index + 1) . ". " . $category['name'] . "\n";
        }
        $menu .= "0. Back";
        
        return $this->createResponse($menu);
    }
    
    private function buildContestantMenu($contestants)
    {
        if (empty($contestants)) {
            return $this->createResponse('No contestants available for this selection.', true);
        }
        
        $menu = "Select a contestant:\n";
        foreach ($contestants as $index => $contestant) {
            $menu .= ($index + 1) . ". " . $contestant['name'];
            if (!empty($contestant['short_code'])) {
                $menu .= " (" . $contestant['short_code'] . ")";
            }
            $menu .= "\n";
        }
        $menu .= "0. Back";
        
        return $this->createResponse($menu);
    }
    
    private function buildVoteTypeMenu($contestantName)
    {
        $menu = "Vote for: " . $contestantName . "\n";
        $menu .= "Select option:\n";
        $menu .= "1. Vote Bundles\n";
        $menu .= "2. Custom Votes\n";
        $menu .= "0. Back";
        
        return $this->createResponse($menu);
    }
    
    private function buildBundleMenu($bundles, $contestantName)
    {
        $menu = "Vote for: " . $contestantName . "\n";
        $menu .= "Select vote package:\n";
        
        foreach ($bundles as $index => $bundle) {
            // Shorten display: "5 Votes = 2.25" instead of "Vote Pack (5) - GHS 2.25"
            $votes = $bundle['votes'];
            $price = number_format($bundle['price'], 2);
            $menu .= ($index + 1) . ". {$votes} Votes = {$price}\n";
        }
        $menu .= "0. Back";
        
        return $this->createResponse($menu);
    }
    
    private function createResponse($message, $end = false)
    {
        return [
            'message' => $message,
            'end' => $end
        ];
    }
    
    private function createErrorResponse($message)
    {
        return [
            'message' => 'Error: ' . $message,
            'end' => true
        ];
    }
    
    public function getSessionStats($tenantId = null, $days = 7)
    {
        $since = date('Y-m-d H:i:s', time() - ($days * 24 * 3600));
        
        $sql = "
            SELECT 
                state,
                COUNT(*) as session_count,
                COUNT(DISTINCT msisdn) as unique_users
            FROM {$this->table}
            WHERE created_at >= :since
        ";
        
        $params = ['since' => $since];
        
        $sql .= " GROUP BY state ORDER BY session_count DESC";
        
        return $this->db->select($sql, $params);
    }
}
