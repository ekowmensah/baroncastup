<?php
/**
 * Legacy Payout System Redirect
 * 
 * This file redirects to the new comprehensive payout approval system
 * The old system has been replaced with an advanced workflow that includes:
 * - Proper approval workflow (pending → approved → processing → paid)
 * - Complete audit trail and logging
 * - Enhanced balance tracking
 * - Bulk operations and advanced management
 * - Modern UI with comprehensive features
 * - Receipt download functionality (migrated from legacy system)
 */

// Redirect to new comprehensive payout system
header('Location: ' . SUPERADMIN_URL . '/payouts');
exit;
?>
