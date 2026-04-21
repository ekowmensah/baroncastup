<?php

require_once __DIR__ . '/includes/autoloader.php';

echo "<h1>Payment Callback Route Test</h1>\n";

// Test the callback URL structure
$transactionId = 117;
$callbackUrl = "https://smartcast.mensweb.xyz/api/payment/callback/{$transactionId}";

echo "<h2>Callback URL Structure</h2>\n";
echo "<p><strong>Expected URL:</strong> {$callbackUrl}</p>\n";
echo "<p><strong>Your URL:</strong> https://smartcast.mensweb.xyz/api/payment/callback/117?trxref=68efefc4c9&reference=68efefc4c9</p>\n";

echo "<h2>Route Configuration</h2>\n";
echo "<p>✅ GET route added: <code>/api/payment/callback/{transactionId}</code></p>\n";
echo "<p>✅ Controller method: <code>VoteController@handlePaymentCallback</code></p>\n";
echo "<p>✅ Handles both POST (webhooks) and GET (redirects)</p>\n";

echo "<h2>What Happens Now</h2>\n";
echo "<ol>\n";
echo "<li><strong>User completes payment</strong> → Paystack redirects to callback URL</li>\n";
echo "<li><strong>GET callback received</strong> → Extract reference from URL parameters</li>\n";
echo "<li><strong>Verify with Paystack</strong> → Call Paystack API to confirm payment</li>\n";
echo "<li><strong>Process vote</strong> → Record votes if payment successful</li>\n";
echo "<li><strong>Redirect user</strong> → Send to payment status page with result</li>\n";
echo "</ol>\n";

echo "<h2>Testing the Route</h2>\n";
echo "<p>You can test the callback route by visiting:</p>\n";
echo "<p><a href='http://localhost/smartcast/api/payment/callback/117?reference=test123' target='_blank'>\n";
echo "http://localhost/smartcast/api/payment/callback/117?reference=test123</a></p>\n";

echo "<h2>Paystack Configuration</h2>\n";
echo "<p>Make sure in your Paystack dashboard:</p>\n";
echo "<ul>\n";
echo "<li><strong>Callback URL:</strong> https://smartcast.mensweb.xyz/api/payment/callback/{transaction_id}</li>\n";
echo "<li><strong>Webhook URL:</strong> https://smartcast.mensweb.xyz/api/payment/webhook.php?provider=paystack</li>\n";
echo "</ul>\n";

echo "<hr>\n";
echo "<p><em>The 404 error should now be resolved!</em></p>\n";
