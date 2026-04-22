<?php

namespace SmartCast\Controllers;

use SmartCast\Models\Event;
use SmartCast\Models\Contestant;
use SmartCast\Models\Category;
use SmartCast\Models\Nomination;
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
    private $nominationModel;
    private $bundleModel;
    private $leaderboardModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->eventModel = new Event();
        $this->contestantModel = new Contestant();
        $this->categoryModel = new Category();
        $this->nominationModel = new Nomination();
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
        $canNominate = $this->eventModel->isNominationOpen($event);
        
        $this->view('events/show', [
            'event' => $event,
            'categories' => $categories,
            'contestants' => $contestants,
            'bundles' => $bundles,
            'leaderboard' => $leaderboard,
            'leaderboards' => $leaderboards,
            'canVote' => $canVote,
            'canNominate' => $canNominate,
            'title' => $event['name']
        ]);
    }

    public function showNominationForm($eventSlug)
    {
        $event = $this->resolveEvent($eventSlug);

        if (!$event || $event['visibility'] !== 'public' || $event['status'] !== 'active') {
            $this->redirect('/', 'Event not found or not available', 'error');
        }

        if (!$this->eventModel->isNominationOpen($event)) {
            $this->redirect('/events/' . ($event['code'] ?: $event['id']), 'Self-nomination is not open for this event.', 'error');
        }

        $categories = $this->categoryModel->getCategoriesByEvent($event['id']);
        if (empty($categories)) {
            $this->redirect('/events/' . ($event['code'] ?: $event['id']), 'This event has no categories available for nominations yet.', 'error');
        }

        $this->view('events/nominate', [
            'event' => $event,
            'categories' => $categories,
            'csrfField' => $this->session->csrfField(),
            'title' => 'Nominate Yourself - ' . $event['name']
        ]);
    }

    public function submitNomination($eventSlug)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/events/' . $eventSlug . '/nominate', 'Invalid request method', 'error');
        }

        $this->validateCsrf('/events/' . $eventSlug . '/nominate');

        $event = $this->resolveEvent($eventSlug);
        if (!$event || $event['visibility'] !== 'public' || $event['status'] !== 'active') {
            $this->redirect('/', 'Event not found or not available', 'error');
        }

        $eventPath = '/events/' . ($event['code'] ?: $event['id']);
        $formPath = $eventPath . '/nominate';

        if (!$this->eventModel->isNominationOpen($event)) {
            $this->redirect($eventPath, 'Self-nomination is not open for this event.', 'error');
        }

        if (!empty($_POST['website'] ?? '')) {
            $this->redirect($eventPath, 'Nomination submitted successfully. It will be reviewed by the organizer.', 'success');
        }

        $data = $this->sanitizeInput($_POST);
        $categoryId = (int)($data['category_id'] ?? 0);
        $name = trim($data['name'] ?? '');
        $bio = trim($data['bio'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (!$categoryId || $name === '') {
            $this->redirect($formPath, 'Please provide your name and choose a category.', 'error');
        }

        if ($email === '' && $phone === '') {
            $this->redirect($formPath, 'Please provide either an email address or phone number.', 'error');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect($formPath, 'Please provide a valid email address.', 'error');
        }

        if (empty($_POST['consent'])) {
            $this->redirect($formPath, 'Please confirm that the information you are submitting is accurate.', 'error');
        }

        $category = $this->categoryModel->find($categoryId);
        if (!$category || (int)$category['event_id'] !== (int)$event['id']) {
            $this->redirect($formPath, 'Please choose a valid category for this event.', 'error');
        }

        if ($this->nominationModel->hasDuplicatePendingOrApproved($event['id'], $categoryId, $name, $email, $phone)) {
            $this->redirect($formPath, 'A nomination with these details already exists for this category.', 'error');
        }

        try {
            $photoUrl = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoUrl = $this->uploadFile($_FILES['photo'], 'nominations');
            } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $this->redirect($formPath, 'The uploaded photo could not be processed. Please try another image.', 'error');
            }

            $nominationId = $this->nominationModel->create([
                'tenant_id' => $event['tenant_id'],
                'event_id' => $event['id'],
                'category_id' => $categoryId,
                'category_name_snapshot' => $category['name'],
                'name' => $name,
                'bio' => $bio,
                'photo_url' => $photoUrl,
                'email' => $email,
                'phone' => $phone,
                'status' => 'pending',
                'submitter_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
            ]);

            if (!$nominationId) {
                throw new \Exception('Failed to save nomination');
            }

            if (empty($event['nomination_requires_approval'])) {
                $this->nominationModel->approveAsContestant($nominationId, null, $categoryId);
                $this->redirect($eventPath, 'Nomination submitted and published successfully.', 'success');
            }

            $this->redirect($eventPath, 'Nomination submitted successfully. It will be reviewed by the organizer.', 'success');
        } catch (\Exception $e) {
            error_log('Self nomination error: ' . $e->getMessage());
            $this->redirect($formPath, 'Unable to submit nomination: ' . $e->getMessage(), 'error');
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
}
