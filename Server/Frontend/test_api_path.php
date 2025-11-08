<?php
// 设置错误报告级别
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 允许跨域请求
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: text/html; charset=utf-8');

// 测试函数
function testApiPaths() {
    echo '<!DOCTYPE html>';
    echo '<html lang="zh-CN">';
    echo '<head>';
    echo '    <meta charset="UTF-8">';
    echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '    <title>API路径测试工具</title>';
    echo '    <style>';
    echo '        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }';
    echo '        h1 { color: #2c3e50; }';
    echo '        .test-result { margin-bottom: 20px; padding: 15px; border-radius: 5px; }';
    echo '        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }';
    echo '        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }';
    echo '        .url { font-family: monospace; margin: 10px 0; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #6c757d; }';
    echo '        .response { font-family: monospace; white-space: pre-wrap; word-break: break-all; margin-top: 10px; padding: 10px; background-color: #fff; border: 1px solid #ddd; max-height: 300px; overflow-y: auto; }';
    echo '        .button { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }';
    echo '        .button:hover { background-color: #0056b3; }';
    echo '        .debug-info { margin-top: 10px; padding: 10px; background-color: #e9ecef; border-radius: 4px; }';
    echo '        .step { margin-bottom: 15px; }';
    echo '    </style>';
    echo '</head>';
    echo '<body>';
    echo '    <h1>API路径测试工具</h1>';
    
    // 测试登录API路径
    echo '    <h2>1. 测试登录API路径</h2>';
    $loginResult = testApiCall('https://www.rsv.ee/api/v2/account/login', [
        'login_type' => '2',
        'account' => 'test_account',
        'passwd' => 'test_password'
    ]);
    displayTestResult('登录API', $loginResult);
    
    // 测试注册API路径
    echo '    <h2>2. 测试注册API路径</h2>';
    $registerResult = testApiCall('https://www.rsv.ee/api/v2/account/signup', [
        'account' => 'test_account_' . time(),
        'passwd' => 'test_password',
        'mail' => 'test_' . time() . '@example.com'
    ]);
    displayTestResult('注册API', $registerResult);
    
    echo '    <div style="margin-top: 30px;">';
    echo '        <a href="login_test.php" class="button">使用详细登录测试工具</a>';
    echo '        <a href="index.php" class="button" style="margin-left: 10px;">返回登录页面</a>';
    echo '    </div>';
    
    echo '    <div class="debug-info" style="margin-top: 30px;">';
    echo '        <h3>调试信息：</h3>';
    echo '        <p>1. 如果两个测试都失败，可能是API服务器无法访问或路径仍然不正确</p>';
    echo '        <p>2. 如果登录API失败但显示了具体错误信息（如"该用户不存在"），则说明路径正确</p>';
    echo '        <p>3. 如果遇到JSON解析错误，请检查响应内容是否包含HTML标签</p>';
    echo '    </div>';
    
    echo '</body>';
    echo '</html>';
}

// 测试API调用
function testApiCall($url, $data) {
    $result = [
        'url' => $url,
        'data' => $data,
        'success' => false,
        'status_code' => 0,
        'response' => '',
        'error' => '',
        'is_json' => false,
        'json_data' => null
    ];
    
    try {
        // 检查cURL扩展是否可用
        if (!function_exists('curl_init')) {
            $result['error'] = 'cURL扩展未启用';
            return $result;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $result['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $result['error'] = 'cURL错误: ' . curl_error($ch);
        } else {
            $result['response'] = $response;
            $result['success'] = true;
            
            // 检查响应是否为有效的JSON
            $jsonData = json_decode($response);
            if (json_last_error() === JSON_ERROR_NONE) {
                $result['is_json'] = true;
                $result['json_data'] = $jsonData;
            }
        }
        
        curl_close($ch);
    } catch (Exception $e) {
        $result['error'] = '异常: ' . $e->getMessage();
    }
    
    return $result;
}

// 显示测试结果
function displayTestResult($name, $result) {
    $statusClass = $result['success'] ? 'success' : 'error';
    $statusText = $result['success'] ? '成功' : '失败';
    
    echo '<div class="test-result ' . $statusClass . '">';
    echo '    <h3>' . $name . ' - 测试' . $statusText . '</h3>';
    echo '    <div class="url">请求URL: ' . $result['url'] . '</div>';
    echo '    <div>请求数据: ' . json_encode($result['data']) . '</div>';
    echo '    <div>HTTP状态码: ' . $result['status_code'] . '</div>';
    
    if (!empty($result['error'])) {
        echo '    <div>错误信息: ' . $result['error'] . '</div>';
    }
    
    echo '    <div>响应类型: ' . ($result['is_json'] ? '有效的JSON' : '非JSON或无效的JSON') . '</div>';
    
    echo '    <div class="response">';
    if ($result['is_json'] && $result['json_data']) {
        echo '格式化JSON响应:\n';
        echo json_encode($result['json_data'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo '原始响应:\n';
        echo $result['response'];
    }
    echo '</div>';
    
    // 检查是否有HTML标签
    if (strpos($result['response'], '<') !== false) {
        echo '    <div class="debug-info">';
        echo '        <p style="color: #dc3545;">警告: 响应中包含HTML标签，这可能是导致"Unexpected token < in JSON at position 0"错误的原因</p>';
        
        // 尝试提取HTML中的错误信息
        $errorMatch = [];
        if (preg_match('/<title>(.*?)<\/title>/i', $result['response'], $errorMatch)) {
            echo '        <p>HTML标题: ' . $errorMatch[1] . '</p>';
        }
        
        if (preg_match('/<body>(.*?)<\/body>/is', $result['response'], $errorMatch)) {
            $bodyText = strip_tags($errorMatch[1]);
            $bodyText = preg_replace('/\s+/', ' ', $bodyText);
            echo '        <p>HTML内容摘要: ' . substr($bodyText, 0, 200) . '...</p>';
        }
        echo '    </div>';
    }
    
    echo '</div>';
}

// 执行测试
testApiPaths();