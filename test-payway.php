<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PayWay KHQR Integration Test ===" . PHP_EOL . PHP_EOL;

try {
    $service = app(App\Services\PayWayService::class);
    echo "✅ PayWay Service Configured: " . ($service->isConfigured() ? 'YES' : 'NO') . PHP_EOL . PHP_EOL;
    
    echo "📱 Testing QR Generation..." . PHP_EOL;
    $result = $service->generateQR([
        'amount' => 1.00,
        'currency' => 'USD',
        'items' => [
            ['name' => 'Test Coffee', 'quantity' => 1, 'price' => 1.00]
        ],
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'phone' => '012345678'
    ]);
    
    echo PHP_EOL . "Result Success: " . ($result['success'] ? '✅ YES' : '❌ NO') . PHP_EOL;
    
    if ($result['success']) {
        echo "Transaction ID: " . $result['data']['tran_id'] . PHP_EOL;
        echo "QR Image: " . (isset($result['data']['qrImage']) ? '✅ Present (' . strlen($result['data']['qrImage']) . ' chars)' : '❌ Missing') . PHP_EOL;
        echo "QR String: " . (isset($result['data']['qrString']) ? '✅ Present' : '❌ Missing') . PHP_EOL;
        echo "Amount: " . $result['data']['amount'] . ' ' . $result['data']['currency'] . PHP_EOL;
        
        // Test check transaction
        echo PHP_EOL . "📊 Testing Transaction Status Check..." . PHP_EOL;
        $statusResult = $service->checkTransaction($result['data']['tran_id']);
        
        echo "Status Check Success: " . ($statusResult['success'] ? '✅ YES' : '❌ NO') . PHP_EOL;
        if ($statusResult['success']) {
            echo "Payment Status: " . ($statusResult['data']['payment_status'] ?? 'N/A') . PHP_EOL;
            echo "Payment Status Code: " . ($statusResult['data']['payment_status_code'] ?? 'N/A') . PHP_EOL;
        }
    } else {
        echo "❌ Error Message: " . ($result['message'] ?? 'Unknown error') . PHP_EOL;
        if (isset($result['error_code'])) {
            echo "Error Code: " . $result['error_code'] . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Test Complete ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
}
