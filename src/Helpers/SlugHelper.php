<?php

namespace SmartCast\Helpers;

class SlugHelper
{
    /**
     * Generate a URL-friendly slug from a string
     */
    public static function generateSlug($string)
    {
        // Convert to lowercase
        $slug = strtolower($string);
        
        // Replace spaces and special characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        
        // Remove leading/trailing hyphens
        $slug = trim($slug, '-');
        
        // Limit length
        $slug = substr($slug, 0, 50);
        
        return $slug;
    }
    
    /**
     * Generate a unique slug for a contestant
     */
    public static function generateContestantSlug($name, $id)
    {
        $baseSlug = self::generateSlug($name);
        
        // Add ID to ensure uniqueness
        return $baseSlug . '-' . $id;
    }
    
    /**
     * Extract ID from a contestant slug
     */
    public static function extractIdFromSlug($slug)
    {
        // Get the last part after the final hyphen
        $parts = explode('-', $slug);
        $id = end($parts);
        
        // Validate it's numeric
        return is_numeric($id) ? (int)$id : null;
    }
    
    /**
     * Generate event slug from code or name
     */
    public static function generateEventSlug($event)
    {
        // Use code if available, otherwise use name
        if (!empty($event['code'])) {
            return strtolower($event['code']);
        }
        
        return self::generateSlug($event['name']) . '-' . $event['id'];
    }
}
