<?php

namespace SmartCast\Controllers\Api;

use SmartCast\Controllers\BaseController;
use SmartCast\Models\ContestantCategory;
use SmartCast\Models\Contestant;
use SmartCast\Models\Category;
use SmartCast\Models\Event;

class ShortcodeController extends BaseController
{
    private $contestantCategoryModel;
    private $contestantModel;
    private $categoryModel;
    private $eventModel;

    public function __construct()
    {
        parent::__construct();
        $this->contestantCategoryModel = new ContestantCategory();
        $this->contestantModel = new Contestant();
        $this->categoryModel = new Category();
        $this->eventModel = new Event();
    }
    
    /**
     * Test endpoint to verify API is working
     */
    public function test()
    {
        $this->json([
            'success' => true,
            'message' => 'Shortcode API is working',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Look up nominee by shortcode
     */
    public function lookup()
    {
        try {
            // Disable error display to prevent HTML in JSON response
            ini_set('display_errors', 0);
            error_reporting(0);
            
            // Only allow POST requests
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
                return;
            }

            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['shortcode'])) {
                $this->json(['success' => false, 'message' => 'Shortcode is required']);
                return;
            }

            $shortcode = trim(strtoupper($input['shortcode']));
            
            if (empty($shortcode)) {
                $this->json(['success' => false, 'message' => 'Shortcode cannot be empty']);
                return;
            }

            if (strlen($shortcode) < 2) {
                $this->json(['success' => false, 'message' => 'Shortcode must be at least 2 characters long']);
                return;
            }

            // Look up the nominee by shortcode
            $nominee = $this->findNomineeByShortcode($shortcode);
            
            if (!$nominee) {
                $this->json(['success' => false, 'message' => 'No nominee found with this shortcode']);
                return;
            }

            // Check if the event is active and voting is allowed
            if (!$this->isVotingAllowed($nominee['event_id'])) {
                $this->json(['success' => false, 'message' => 'Voting is not currently available for this event']);
                return;
            }

            $this->json([
                'success' => true,
                'nominee' => $nominee
            ]);

        } catch (\Exception $e) {
            error_log('Shortcode lookup error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while searching: ' . $e->getMessage()]);
        }
    }

    /**
     * Find nominee by shortcode
     */
    private function findNomineeByShortcode($shortcode)
    {
        $sql = "
            SELECT 
                cc.contestant_id,
                cc.category_id,
                cc.short_code,
                c.name,
                c.image_url,
                c.bio,
                c.event_id,
                cat.name as category_name,
                e.name as event_name,
                e.code as event_code,
                e.vote_price,
                e.status as event_status,
                e.start_date,
                e.end_date
            FROM contestant_categories cc
            INNER JOIN contestants c ON cc.contestant_id = c.id
            INNER JOIN categories cat ON cc.category_id = cat.id
            INNER JOIN events e ON c.event_id = e.id
            WHERE cc.short_code = :shortcode
            AND c.active = 1
            AND e.status = 'active'
            LIMIT 1
        ";

        try {
            $database = new \SmartCast\Core\Database();
            $result = $database->selectOne($sql, [
                'shortcode' => $shortcode
            ]);
        } catch (\Exception $e) {
            error_log('Database error in shortcode lookup: ' . $e->getMessage());
            return null;
        }

        if ($result) {
            // Format the image URL if it exists
            if ($result['image_url']) {
                // Ensure the image URL is properly formatted
                if (strpos($result['image_url'], 'http') !== 0) {
                    $result['image_url'] = APP_URL . $result['image_url'];
                }
            }

            // Format vote price
            $result['vote_price'] = number_format($result['vote_price'], 2);
        }

        return $result;
    }

    /**
     * Check if voting is allowed for the event
     */
    private function isVotingAllowed($eventId)
    {
        try {
            $database = new \SmartCast\Core\Database();
            $event = $database->selectOne("SELECT * FROM events WHERE id = :id", ['id' => $eventId]);
            
            if (!$event) {
                return false;
            }

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
        } catch (\Exception $e) {
            error_log('Error checking voting allowed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all shortcodes for an event (for testing/admin purposes)
     */
    public function getEventShortcodes($eventId)
    {
        header('Content-Type: application/json');
        
        try {
            $sql = "
                SELECT 
                    cc.short_code,
                    c.name as contestant_name,
                    cat.name as category_name
                FROM contestant_categories cc
                INNER JOIN contestants c ON cc.contestant_id = c.id
                INNER JOIN categories cat ON cc.category_id = cat.id
                WHERE c.event_id = :event_id
                AND c.active = 1
                ORDER BY cat.name, c.name
            ";

            $shortcodes = $this->contestantCategoryModel->getDatabase()->select($sql, [
                'event_id' => $eventId
            ]);

            echo json_encode([
                'success' => true,
                'shortcodes' => $shortcodes
            ]);

        } catch (\Exception $e) {
            error_log('Get event shortcodes error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
    }

    /**
     * Validate shortcode format
     */
    public function validateShortcode()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['shortcode'])) {
                echo json_encode(['success' => false, 'message' => 'Shortcode is required']);
                return;
            }

            $shortcode = trim(strtoupper($input['shortcode']));
            
            // Validate shortcode format
            $isValid = $this->isValidShortcodeFormat($shortcode);
            
            echo json_encode([
                'success' => true,
                'valid' => $isValid,
                'formatted' => $shortcode
            ]);

        } catch (\Exception $e) {
            error_log('Shortcode validation error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
    }

    /**
     * Check if shortcode format is valid
     */
    private function isValidShortcodeFormat($shortcode)
    {
        // Shortcode should be 2-10 characters, alphanumeric only
        return preg_match('/^[A-Z0-9]{2,10}$/', $shortcode);
    }
}
