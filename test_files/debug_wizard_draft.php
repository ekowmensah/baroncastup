<?php
require_once __DIR__ . '/config/config.php';

echo "=== DEBUG WIZARD DRAFT ISSUE ===\n\n";

echo "🔍 DEBUGGING STEPS:\n\n";

echo "1. Check PHP error logs after saving draft:\n";
echo "   • Look for 'DEBUG: Existing contestants map:' messages\n";
echo "   • Look for 'DEBUG: Processing nominee ID:' messages\n";
echo "   • Look for 'DEBUG: Found existing contestant' messages\n\n";

echo "2. Expected Debug Flow:\n";
echo "   FIRST SAVE (Nominee 1 with image):\n";
echo "   • No existing contestants (new event)\n";
echo "   • Creates new contestant with image\n\n";
echo "   SECOND SAVE (Add Nominee 2):\n";
echo "   • DEBUG: Existing contestants map: [\"123\"] (actual DB ID)\n";
echo "   • DEBUG: Processing nominee ID: 123, name: Nominee 1\n";
echo "   • DEBUG: Found existing contestant for ID 123 with image: public/uploads/...\n";
echo "   • DEBUG: Preserving existing image for ID 123: public/uploads/...\n\n";

echo "3. Potential Issues to Check:\n";
echo "   ❌ Nominee IDs in form don't match database IDs\n";
echo "   ❌ JavaScript generating new IDs instead of using existing ones\n";
echo "   ❌ Form data structure incorrect\n";
echo "   ❌ Database ID mapping broken\n\n";

echo "4. Manual Testing:\n";
echo "   • Open browser developer tools\n";
echo "   • Go to Network tab\n";
echo "   • Save draft and check form data being sent\n";
echo "   • Look for 'nominees[ID][name]' structure\n";
echo "   • Verify IDs match database contestant IDs\n\n";

echo "5. Check Error Logs:\n";
echo "   Windows: C:\\xampp\\apache\\logs\\error.log\n";
echo "   Linux: /var/log/apache2/error.log\n\n";

echo "6. Quick Database Check:\n";
echo "   SELECT id, name, image_url FROM contestants WHERE event_id = YOUR_EVENT_ID;\n\n";

echo "🎯 EXPECTED BEHAVIOR:\n";
echo "   • Nominee IDs in form should match database contestant IDs\n";
echo "   • Existing images should be preserved when no new upload\n";
echo "   • Debug logs should show proper ID matching\n\n";

echo "=== END DEBUG ===\n";
?>
