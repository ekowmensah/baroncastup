<?php

namespace SmartCast\Controllers;

use SmartCast\Models\Event;
use SmartCast\Models\Contestant;
use SmartCast\Models\Category;
use SmartCast\Models\VoteBundle;
use SmartCast\Models\LeaderboardCache;

/**
 * Public Event Controller
 */
class EventController extends BaseController
{
    private $eventModel;
    private $contestantModel;
    private $categoryModel;
    private $bundleModel;
    private $leaderboardModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->eventModel = new Event();
        $this->contestantModel = new Contestant();
        $this->categoryModel = new Category();
        $this->bundleModel = new VoteBundle();
        $this->leaderboardModel = new LeaderboardCache();
    }
    
    public function index()
    {
        $events = $this->eventModel->getPublicEvents();
        
        $this->view('events/index', [
            'events' => $events,
            'title' => 'Active Events'
        ]);
    }
    
    public function show($eventSlug)
    {
        // Handle both slug and ID
        $event = $this->resolveEvent($eventSlug);
        
        if (!$event || $event['visibility'] !== 'public' || $event['status'] !== 'active') {
            $this->redirect('/', 'Event not found or not available', 'error');
        }
        
        // Get event details
        $categories = $this->categoryModel->getCategoriesByEvent($event['id']);
        $contestants = $this->contestantModel->getContestantsByEvent($event['id']);
        $bundles = $this->bundleModel->getBundlesByEvent($event['id']);
        
        // Get category-specific leaderboards
        $leaderboards = [];
        foreach ($categories as $category) {
            $leaderboards[$category['id']] = [
                'category' => $category,
                'leaderboard' => $this->leaderboardModel->getLeaderboard($event['id'], $category['id'], 10)
            ];
        }
        
        // For backward compatibility, get overall leaderboard (first category or empty)
        $leaderboard = !empty($leaderboards) ? reset($leaderboards)['leaderboard'] : [];
        
        // Check if voting is currently allowed
        $canVote = $this->eventModel->canVote($event['id']);
        
        $this->view('events/show', [
            'event' => $event,
            'categories' => $categories,
            'contestants' => $contestants,
            'bundles' => $bundles,
            'leaderboard' => $leaderboard,
            'leaderboards' => $leaderboards,
            'canVote' => $canVote,
            'title' => $event['name']
        ]);
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
}
