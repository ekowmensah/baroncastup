<?php

declare(strict_types=1);

/**
 * Virtual USSD phone emulator for local testing.
 *
 * Usage:
 * php test_files/virtual_ussd_phone.php
 * php test_files/virtual_ussd_phone.php --endpoint=http://127.0.0.1:8088/api/ussd/callback --service-code=713*734 --shortcode=RX54 --votes=5
 * php test_files/virtual_ussd_phone.php --skip-fulfillment
 */

$opts = getopt('', [
    'endpoint::',
    'service-code::',
    'mobile::',
    'shortcode::',
    'votes::',
    'session-id::',
    'skip-fulfillment'
]);

if (!is_array($opts)) {
    $opts = [];
}

$endpoint = (string)($opts['endpoint'] ?? 'http://127.0.0.1:8088/api/ussd/callback');
$serviceCode = (string)($opts['service-code'] ?? '713*734');
$mobile = (string)($opts['mobile'] ?? '233244000000');
$shortcode = strtoupper(trim((string)($opts['shortcode'] ?? 'RX54')));
$votes = (int)($opts['votes'] ?? 5);
$sessionId = (string)($opts['session-id'] ?? ('vp_' . time()));
$skipFulfillment = array_key_exists('skip-fulfillment', $opts);

function writeError(string $message): void
{
    if (defined('STDERR')) {
        fwrite(STDERR, $message);
        return;
    }

    $written = @file_put_contents('php://stderr', $message, FILE_APPEND);
    if ($written === false) {
        echo $message;
    }
}

if ($votes < 1 || $votes > 10000) {
    writeError("Invalid --votes value. Must be 1..10000.\n");
    exit(1);
}

function sendJsonPost(string $url, array $payload): array
{
    $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
    if ($body === false) {
        return ['status' => 0, 'body' => '', 'json' => null, 'error' => 'Failed to encode JSON payload'];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => $body,
            'ignore_errors' => true,
            'timeout' => 20
        ]
    ]);

    $responseBody = @file_get_contents($url, false, $context);
    $headers = $http_response_header ?? [];
    $status = 0;

    if (!empty($headers[0]) && preg_match('/\s(\d{3})\s/', $headers[0], $m)) {
        $status = (int)$m[1];
    }

    if ($responseBody === false) {
        $responseBody = '';
    }

    $json = json_decode($responseBody, true);
    if (!is_array($json) && $responseBody !== '') {
        // Some environments prepend warnings/notices before JSON.
        $firstBrace = strpos($responseBody, '{');
        $lastBrace = strrpos($responseBody, '}');
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $candidate = substr($responseBody, $firstBrace, $lastBrace - $firstBrace + 1);
            $json = json_decode($candidate, true);
        }
    }
    return ['status' => $status, 'body' => $responseBody, 'json' => $json, 'error' => null];
}

function printStep(string $title, array $payload, array $response): void
{
    echo "\n=== {$title} ===\n";
    echo "Request: " . json_encode($payload, JSON_UNESCAPED_SLASHES) . "\n";
    echo "HTTP: " . ($response['status'] ?: 'N/A') . "\n";

    if ($response['error']) {
        echo "Error: {$response['error']}\n";
        return;
    }

    if (is_array($response['json'])) {
        echo "Response JSON: " . json_encode($response['json'], JSON_UNESCAPED_SLASHES) . "\n";
    } else {
        echo "Response Body: {$response['body']}\n";
    }
}

echo "Virtual USSD Phone\n";
echo "Endpoint: {$endpoint}\n";
echo "Session: {$sessionId}\n";
echo "ServiceCode: {$serviceCode}\n";
echo "Mobile: {$mobile}\n";
echo "Shortcode: {$shortcode}\n";
echo "Votes: {$votes}\n";

// 1) Dial (initiation)
$payload1 = [
    'SessionId' => $sessionId,
    'ServiceCode' => $serviceCode,
    'Mobile' => $mobile,
    'Message' => '',
    'Type' => 'Initiation'
];
$resp1 = sendJsonPost($endpoint, $payload1);
printStep('Dial', $payload1, $resp1);

if (!is_array($resp1['json'])) {
    writeError("Dial failed: no JSON response.\n");
    exit(2);
}

// 2) Enter shortcode
$payload2 = [
    'SessionId' => $sessionId,
    'ServiceCode' => $serviceCode,
    'Mobile' => $mobile,
    'Message' => $shortcode,
    'Type' => 'Response'
];
$resp2 = sendJsonPost($endpoint, $payload2);
printStep('Enter Shortcode', $payload2, $resp2);

if (!is_array($resp2['json'])) {
    writeError("Shortcode step failed: no JSON response.\n");
    exit(3);
}

// 3) Enter vote count
$payload3 = [
    'SessionId' => $sessionId,
    'ServiceCode' => $serviceCode,
    'Mobile' => $mobile,
    'Message' => (string)$votes,
    'Type' => 'Response'
];
$resp3 = sendJsonPost($endpoint, $payload3);
printStep('Enter Votes', $payload3, $resp3);

if (!is_array($resp3['json'])) {
    writeError("Vote count step failed: no JSON response.\n");
    exit(4);
}

// 4) Confirm
$payload4 = [
    'SessionId' => $sessionId,
    'ServiceCode' => $serviceCode,
    'Mobile' => $mobile,
    'Message' => '1',
    'Type' => 'Response'
];
$resp4 = sendJsonPost($endpoint, $payload4);
printStep('Confirm Vote', $payload4, $resp4);

if (!is_array($resp4['json'])) {
    writeError("Confirm step failed: no JSON response.\n");
    exit(5);
}

$responseType = (string)($resp4['json']['Type'] ?? '');
if ($responseType !== 'AddToCart') {
    echo "\nResult: Flow did not reach AddToCart. Check previous messages.\n";
    exit(6);
}

if ($skipFulfillment) {
    echo "\nResult: AddToCart reached. Fulfillment skipped by flag.\n";
    exit(0);
}

// 5) Simulate service fulfillment callback from provider
$amount = (float)($resp4['json']['Item']['Price'] ?? 0);
$orderId = 'ORD-' . date('YmdHis');

$payload5 = [
    'SessionId' => $sessionId,
    'OrderId' => $orderId,
    'OrderInfo' => [
        'Status' => 'Paid',
        'Amount' => $amount,
        'Payment' => [
            'IsSuccessful' => true,
            'Mobile' => $mobile
        ]
    ]
];
$resp5 = sendJsonPost($endpoint, $payload5);
printStep('Service Fulfillment', $payload5, $resp5);

if (!is_array($resp5['json'])) {
    writeError("Fulfillment step failed: no JSON response.\n");
    exit(7);
}

if (($resp5['json']['success'] ?? false) === true) {
    echo "\nResult: End-to-end USSD test succeeded.\n";
    exit(0);
}

echo "\nResult: Fulfillment responded with failure.\n";
exit(8);
