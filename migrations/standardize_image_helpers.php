<?php
/**
 * Standardize Image Helper Usage Migration
 * 
 * This script standardizes all image helper usage to use the global image_url() function
 * instead of the class-based \SmartCast\Helpers\ImageHelper::getImageUrl()
 */

echo "=== SmartCast Image Helper Standardization ===\n\n";

$viewsPath = __DIR__ . '/../views';

// Files that use ImageHelper::getImageUrl that should be converted
$filesToUpdate = [
    'voting/vote-form.php',
    'voting/direct.php', 
    'organizer/contestants/stats.php',
    'organizer/contestants/show.php',
    'organizer/contestants/edit.php'
];

$totalUpdated = 0;

foreach ($filesToUpdate as $file) {
    $filePath = $viewsPath . '/' . $file;
    
    if (!file_exists($filePath)) {
        echo "⚠️  File not found: $file\n";
        continue;
    }
    
    echo "Processing: $file\n";
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Replace ImageHelper::getImageUrl with image_url
    $content = str_replace(
        'htmlspecialchars(\SmartCast\Helpers\ImageHelper::getImageUrl(',
        'htmlspecialchars(image_url(',
        $content
    );
    
    // Count changes
    $changes = substr_count($originalContent, '\SmartCast\Helpers\ImageHelper::getImageUrl(') - 
               substr_count($content, '\SmartCast\Helpers\ImageHelper::getImageUrl(');
    
    if ($changes > 0) {
        file_put_contents($filePath, $content);
        echo "  ✅ Updated $changes instances in $file\n";
        $totalUpdated += $changes;
    } else {
        echo "  ℹ️  No changes needed in $file\n";
    }
}

echo "\n=== Summary ===\n";
echo "Total instances standardized: $totalUpdated\n";

if ($totalUpdated > 0) {
    echo "\n✅ Image helper standardization completed successfully!\n";
    echo "\nAll image helpers now use the global image_url() function for consistency.\n";
    echo "\nBenefits:\n";
    echo "- Shorter, cleaner syntax\n";
    echo "- Consistent across all files\n";
    echo "- Better performance (no class loading)\n";
    echo "- Easier to maintain\n";
} else {
    echo "\n✅ All files already use consistent image helpers!\n";
}

echo "\n=== Next Steps ===\n";
echo "1. Test all pages to ensure images display correctly\n";
echo "2. Consider removing unused ImageHelper class if no longer needed\n";
echo "3. Update any custom JavaScript to use the new global getImageUrl() function\n";
?>
