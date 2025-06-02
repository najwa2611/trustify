<?php
/**
 * Simple Proxy Script
 * Allows accessing any website through the proxy
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create cache directory if it doesn't exist
$cacheDir = 'cache/';
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

function fetchContent($url) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_ENCODING => 'gzip, deflate',
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive'
        ]
    ]);
    
    $content = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        throw new Exception("Connection error: $error");
    }
    
    return [
        'content' => $content,
        'headers' => [
            'Content-Type' => $info['content_type'],
            'Content-Length' => strlen($content)
        ]
    ];
}

// Handle the request
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Only GET requests are allowed');
    }

    $url = $_GET['url'] ?? '';
    if (empty($url)) {
        throw new Exception('URL parameter is required');
    }

    // Basic URL validation
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid URL format');
    }

    // Try to get from cache
    $cacheFile = $cacheDir . md5($url) . '.cache';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
        $result = json_decode(file_get_contents($cacheFile), true);
    } else {
        $result = fetchContent($url);
        // Cache the result
        file_put_contents($cacheFile, json_encode($result));
    }

    // Send headers
    foreach ($result['headers'] as $name => $value) {
        if ($value) {
            header("$name: $value");
        }
    }

    // Output content
    echo $result['content'];

} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>