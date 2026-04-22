<?php
/**
 * Legacy Payout Page - Redirects to New Comprehensive Payout System
 * 
 * This file has been replaced with a comprehensive payout system located at:
 * /organizer/payouts (PayoutController)
 * 
 * The new system includes:
 * - Advanced payout method management
 * - Automatic payout scheduling
 * - Real-time revenue distribution
 * - Dynamic fee calculation
 * - Complete backend integration
 */

// Redirect to new comprehensive payout system
header('Location: ' . ORGANIZER_URL . '/payouts');
exit;
?>
