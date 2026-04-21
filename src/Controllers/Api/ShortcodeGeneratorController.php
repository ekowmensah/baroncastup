<?php

namespace SmartCast\Controllers\Api;

use SmartCast\Controllers\BaseController;
use SmartCast\Models\ContestantCategory;

class ShortcodeGeneratorController extends BaseController
{
    private $contestantCategoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->contestantCategoryModel = new ContestantCategory();
    }
    
    /**
     * Generate a preview shortcode for a nominee (before saving to database)
     */
    public function generatePreview()
    {
        try {
            // Only allow POST requests
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
                return;
            }

            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['nominee_name'])) {
                $this->json(['success' => false, 'message' => 'Nominee name is required']);
                return;
            }

            $nomineeName = trim($input['nominee_name']);
            $nomineeId = $input['nominee_id'] ?? time(); // Use timestamp as temp ID
            $categoryId = $input['category_id'] ?? 1; // Temp category ID for generation
            
            if (empty($nomineeName)) {
                $this->json(['success' => false, 'message' => 'Nominee name cannot be empty']);
                return;
            }

            // Generate shortcode
            $shortCode = $this->contestantCategoryModel->generateShortCode($categoryId, $nomineeName, $nomineeId);
            
            $this->json([
                'success' => true,
                'shortcode' => $shortCode
            ]);

        } catch (\Exception $e) {
            error_log('Shortcode generation error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to generate shortcode: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Validate if a shortcode is available globally
     */
    public function validateShortcode()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['shortcode'])) {
                $this->json(['success' => false, 'message' => 'Shortcode is required']);
                return;
            }

            $shortcode = strtoupper(trim($input['shortcode']));
            
            if (empty($shortcode)) {
                $this->json(['success' => false, 'message' => 'Shortcode cannot be empty']);
                return;
            }

            // Check if shortcode is taken globally
            $isTaken = $this->contestantCategoryModel->isShortCodeTakenGlobally($shortcode);
            
            $this->json([
                'success' => true,
                'available' => !$isTaken,
                'message' => $isTaken ? 'Shortcode is already taken' : 'Shortcode is available'
            ]);

        } catch (\Exception $e) {
            error_log('Shortcode validation error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to validate shortcode']);
        }
    }
}
