<?php

namespace SmartCast\Models;

/**
 * Event Draft Model
 */
class EventDraft extends BaseModel
{
    protected $table = 'event_drafts';
    protected $fillable = [
        'tenant_id', 'draft_name', 'draft_data', 'step', 'created_by'
    ];
    
    public function createDraft($tenantId, $draftName, $createdBy, $initialData = [])
    {
        $draftData = [
            'basic' => $initialData,
            'categories' => [],
            'contestants' => [],
            'bundles' => [],
            'settings' => []
        ];
        
        return $this->create([
            'tenant_id' => $tenantId,
            'draft_name' => $draftName,
            'draft_data' => json_encode($draftData),
            'step' => 1,
            'created_by' => $createdBy
        ]);
    }
    
    public function updateDraftStep($draftId, $step, $stepData)
    {
        $draft = $this->find($draftId);
        if (!$draft) {
            throw new \Exception('Draft not found');
        }
        
        $draftData = json_decode($draft['draft_data'], true);
        
        // Update the specific step data
        switch ($step) {
            case 1:
                $draftData['basic'] = array_merge($draftData['basic'] ?? [], $stepData);
                break;
            case 2:
                $draftData['categories'] = $stepData;
                break;
            case 3:
                $draftData['contestants'] = $stepData;
                break;
            case 4:
                $draftData['bundles'] = $stepData;
                break;
            case 5:
                $draftData['settings'] = $stepData;
                break;
        }
        
        return $this->update($draftId, [
            'draft_data' => json_encode($draftData),
            'step' => $step
        ]);
    }
    
    public function getDraftData($draftId)
    {
        $draft = $this->find($draftId);
        if (!$draft) {
            return null;
        }
        
        $draft['draft_data'] = json_decode($draft['draft_data'], true);
        return $draft;
    }
    
    public function getDraftsByTenant($tenantId)
    {
        $drafts = $this->findAll(['tenant_id' => $tenantId], 'updated_at DESC');
        
        // Decode draft_data for each draft
        foreach ($drafts as &$draft) {
            $draft['draft_data'] = json_decode($draft['draft_data'], true);
        }
        
        return $drafts;
    }
    
    public function publishDraft($draftId)
    {
        $draft = $this->getDraftData($draftId);
        if (!$draft) {
            throw new \Exception('Draft not found');
        }
        
        $eventModel = new Event();
        $categoryModel = new Category();
        $contestantModel = new Contestant();
        $bundleModel = new VoteBundle();
        
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Create event from basic data
            $basicData = $draft['draft_data']['basic'];
            $basicData['tenant_id'] = $draft['tenant_id'];
            $basicData['created_by'] = $draft['created_by'];
            $basicData['status'] = 'draft';
            
            $eventId = $eventModel->create($basicData);
            
            // Create categories
            if (!empty($draft['draft_data']['categories'])) {
                foreach ($draft['draft_data']['categories'] as $categoryData) {
                    $categoryData['event_id'] = $eventId;
                    $categoryData['tenant_id'] = $draft['tenant_id'];
                    $categoryModel->create($categoryData);
                }
            }
            
            // Create contestants
            if (!empty($draft['draft_data']['contestants'])) {
                foreach ($draft['draft_data']['contestants'] as $contestantData) {
                    $contestantData['event_id'] = $eventId;
                    $contestantData['tenant_id'] = $draft['tenant_id'];
                    $contestantData['created_by'] = $draft['created_by'];
                    $contestantModel->create($contestantData);
                }
            }
            
            // Create vote bundles
            if (!empty($draft['draft_data']['bundles'])) {
                foreach ($draft['draft_data']['bundles'] as $bundleData) {
                    $bundleData['event_id'] = $eventId;
                    $bundleModel->create($bundleData);
                }
            } else {
                // Create default bundles if none specified
                $bundleModel->createDefaultBundles($eventId);
            }
            
            // Delete the draft
            $this->delete($draftId);
            
            $this->db->commit();
            
            return $eventId;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function duplicateDraft($draftId, $newName = null)
    {
        $draft = $this->getDraftData($draftId);
        if (!$draft) {
            throw new \Exception('Draft not found');
        }
        
        $newDraftName = $newName ?: $draft['draft_name'] . ' (Copy)';
        
        return $this->create([
            'tenant_id' => $draft['tenant_id'],
            'draft_name' => $newDraftName,
            'draft_data' => json_encode($draft['draft_data']),
            'step' => $draft['step'],
            'created_by' => $draft['created_by']
        ]);
    }
    
    public function validateDraft($draftId)
    {
        $draft = $this->getDraftData($draftId);
        if (!$draft) {
            return ['valid' => false, 'errors' => ['Draft not found']];
        }
        
        $errors = [];
        $draftData = $draft['draft_data'];
        
        // Validate basic information
        if (empty($draftData['basic']['name'])) {
            $errors[] = 'Event name is required';
        }
        
        if (empty($draftData['basic']['code'])) {
            $errors[] = 'Event code is required';
        }
        
        if (empty($draftData['basic']['start_date'])) {
            $errors[] = 'Start date is required';
        }
        
        if (empty($draftData['basic']['end_date'])) {
            $errors[] = 'End date is required';
        }
        
        // Validate that end date is after start date
        if (!empty($draftData['basic']['start_date']) && !empty($draftData['basic']['end_date'])) {
            if (strtotime($draftData['basic']['end_date']) <= strtotime($draftData['basic']['start_date'])) {
                $errors[] = 'End date must be after start date';
            }
        }
        
        // Check if event code is unique
        if (!empty($draftData['basic']['code'])) {
            $eventModel = new Event();
            if ($eventModel->findByCode($draftData['basic']['code'])) {
                $errors[] = 'Event code already exists';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    public function getStepProgress($draftId)
    {
        $draft = $this->find($draftId);
        if (!$draft) {
            return null;
        }
        
        $draftData = json_decode($draft['draft_data'], true);
        
        $progress = [
            'current_step' => $draft['step'],
            'total_steps' => 5,
            'completed_steps' => [],
            'step_names' => [
                1 => 'Basic Information',
                2 => 'Categories',
                3 => 'Contestants',
                4 => 'Vote Bundles',
                5 => 'Settings & Review'
            ]
        ];
        
        // Check which steps are completed
        if (!empty($draftData['basic']['name']) && !empty($draftData['basic']['code'])) {
            $progress['completed_steps'][] = 1;
        }
        
        if (!empty($draftData['categories'])) {
            $progress['completed_steps'][] = 2;
        }
        
        if (!empty($draftData['contestants'])) {
            $progress['completed_steps'][] = 3;
        }
        
        if (!empty($draftData['bundles'])) {
            $progress['completed_steps'][] = 4;
        }
        
        if ($draft['step'] >= 5) {
            $progress['completed_steps'][] = 5;
        }
        
        $progress['completion_percentage'] = (count($progress['completed_steps']) / $progress['total_steps']) * 100;
        
        return $progress;
    }
}
