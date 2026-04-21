<?php
/**
 * Clear Invalid Sessions
 * This script helps clear any invalid session data that might be causing issues
 */

echo "=== Clearing Invalid Sessions ===\n";

// Start session to clear it
session_start();

// Get current session data
echo "Current session data:\n";
if (!empty($_SESSION)) {
    foreach ($_SESSION as $key => $value) {
        echo "  $key: $value\n";
    }
} else {
    echo "  No session data found\n";
}

// Clear all session data
session_unset();
session_destroy();

echo "\n✅ Session data cleared!\n";
echo "You can now login fresh without any session conflicts.\n";
echo "\nAvailable login credentials:\n";
echo "- test14@organizer.com / password123 (Tenant 14 - Active)\n";
echo "- session@tenant.com / password123 (Tenant 16 - Active)\n";
echo "- ekowme@gmail.comm / password123 (Platform Admin)\n";
echo "- pending[timestamp]@example.com / password123 (Pending Approval - Will be blocked)\n";
?>
