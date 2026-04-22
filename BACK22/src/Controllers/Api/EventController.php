<?php

namespace SmartCast\Controllers\Api;

use SmartCast\Controllers\BaseController;
use SmartCast\Models\Event;
use SmartCast\Models\Contestant;
use SmartCast\Models\Category;
use SmartCast\Models\Vote;
use SmartCast\Models\LeaderboardCache;

/**
 * API Event Controller
 */
class EventController extends BaseController
{
    private $eventModel;
    private $contestantModel;
    private $categoryModel;
    private $voteModel;
    private $leaderboardModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->eventModel = new Event();
        $this->contestantModel = new Contestant();
        $this->categoryModel = new Category();
        $this->voteModel = new Vote();
        $this->leaderboardModel = new LeaderboardCache();
        
        // Set JSON headers
        header('Content-Type: application/json');
    }
    
    public function index()
    {
        try {
            $events = $this->eventModel->getPublicEvents();
            
            // Format events for API
            $formattedEvents = array_map(function($event) {
                return [
                    'id' => $event['id'],
                    'name' => $event['name'],
                    'code' => $event['code'],
                    'description' => $event['description'],
                    'featured_image' => $event['featured_image'],
                    'start_date' => $event['start_date'],
                    'end_date' => $event['end_date'],
                    'status' => $event['status'],
                    'can_vote' => $this->eventModel->canVote($event['id'])
                ];
            }, $events);
            
            return $this->json([
                'success' => true,
                'data' => $formattedEvents,
                'count' => count($formattedEvents)
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to fetch events',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function show($eventId)
    {
        try {
            $event = $this->eventModel->find($eventId);
            
            if (!$event || $event['visibility'] !== 'public') {
                return $this->json([
                    'success' => false,
                    'message' => 'Event not found or not public'
                ], 404);
            }
            
            // Get related data
            $categories = $this->categoryModel->getCategoriesByEvent($eventId);
            $contestants = $this->contestantModel->getContestantsByEvent($eventId);
            
            $eventData = [
                'id' => $event['id'],
                'name' => $event['name'],
                'code' => $event['code'],
                'description' => $event['description'],
                'featured_image' => $event['featured_image'],
                'start_date' => $event['start_date'],
                'end_date' => $event['end_date'],
                'status' => $event['status'],
                'can_vote' => $this->eventModel->canVote($eventId),
                'results_visible' => (bool)$event['results_visible'],
                'categories' => $categories,
                'contestants' => $contestants
            ];
            
            return $this->json([
                'success' => true,
                'data' => $eventData
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to fetch event details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function results($eventId)
    {
        try {
            $event = $this->eventModel->find($eventId);
            
            if (!$event || $event['visibility'] !== 'public') {
                return $this->json([
                    'success' => false,
                    'message' => 'Event not found or not public'
                ], 404);
            }
            
            if (!$event['results_visible']) {
                return $this->json([
                    'success' => false,
                    'message' => 'Results are not public for this event'
                ], 403);
            }
            
            // Get leaderboard
            $leaderboard = $this->leaderboardModel->getLeaderboard($eventId, 50);
            
            // Get vote statistics
            $stats = $this->voteModel->getVoteStats($eventId);
            
            return $this->json([
                'success' => true,
                'data' => [
                    'event' => [
                        'id' => $event['id'],
                        'name' => $event['name'],
                        'code' => $event['code']
                    ],
                    'leaderboard' => $leaderboard,
                    'statistics' => $stats,
                    'last_updated' => date('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to fetch results',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function search()
    {
        try {
            $query = $_GET['q'] ?? '';
            $status = $_GET['status'] ?? 'active';
            
            if (empty($query)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Search query required'
                ], 400);
            }
            
            $sql = "
                SELECT * FROM events 
                WHERE visibility = 'public' 
                AND active = 1 
                AND status = :status
                AND (name LIKE :query OR description LIKE :query OR code LIKE :query)
                ORDER BY start_date DESC
                LIMIT 20
            ";
            
            $events = $this->eventModel->db->select($sql, [
                'status' => $status,
                'query' => '%' . $query . '%'
            ]);
            
            return $this->json([
                'success' => true,
                'data' => $events,
                'query' => $query,
                'count' => count($events)
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
