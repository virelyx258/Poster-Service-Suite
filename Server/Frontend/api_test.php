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
function runTests() {
    $results = [];
    
    // 测试1: 检查PHP环境
    $results[] = testPhpEnvironment();
    
    // 测试2: 检查cURL扩展
    $results[] = testCurlExtension();
    
    // 测试3: 测试网络连接
    $results[] = testNetworkConnection('https://www.rsv.ee');
    
    // 测试4: 测试API连接
    $results[] = testApiConnection('https://www.rsv.ee/api/v2/login');
    
    // 测试5: 检查文件权限
    $results[] = testFilePermissions();
    
    return $results;
}

// 测试PHP环境
function testPhpEnvironment() {
    $result = [
        'name' => 'PHP环境测试',
        'status' => 'success',
        'message' => 'PHP版本: ' . phpversion()
    ];
    
    if (phpversion() < '7.0') {
        $result['status'] = 'warning';
        $result['message'] .= ' (建议使用PHP 7.0或更高版本)';
    }
    
    return $result;
}

// 测试cURL扩展
function testCurlExtension() {
    $result = [
        'name' => 'cURL扩展测试',
        'status' => 'success',
        'message' => 'cURL扩展已启用'
    ];
    
    if (!function_exists('curl_init')) {
        $result['status'] = 'error';
        $result['message'] = 'cURL扩展未启用，请在php.ini中启用curl扩展';
    }
    
    return $result;
}

// 测试网络连接
function testNetworkConnection($url) {
    $result = [
        'name' => '网络连接测试',
        'status' => 'success',
        'message' => '能够连接到 ' . $url
    ];
    
    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $result['status'] = 'error';
            $result['message'] = '无法连接到 ' . $url . ': ' . curl_error($ch);
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result['message'] .= ' (HTTP状态码: ' . $httpCode . ')';
        }
        
        curl_close($ch);
    } catch (Exception $e) {
        $result['status'] = 'error';
        $result['message'] = '网络连接异常: ' . $e->getMessage();
    }
    
    return $result;
}

// 测试API连接
function testApiConnection($apiUrl) {
    $result = [
        'name' => 'API连接测试',
        'status' => 'success',
        'message' => 'API连接成功',
        'details' => []
    ];
    
    try {
        // 准备测试数据（使用无效的账号密码进行测试）
        $testData = [
            'login_type' => '2',
            'account' => 'test_account',
            'passwd' => 'test_password'
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=Windows-1252',
            'Accept: application/json; charset=Windows-1252',
            'Accept-Charset: Windows-1252'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $result['status'] = 'error';
            $result['message'] = 'API请求失败: ' . curl_error($ch);
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result['details']['http_code'] = $httpCode;
            $result['details']['response_size'] = strlen($response) . ' bytes';
            
            // 检查响应是否为有效的JSON
            $jsonData = json_decode($response);
            if (json_last_error() === JSON_ERROR_NONE) {
                $result['details']['is_json'] = true;
                $result['details']['json_structure'] = print_r(array_keys((array)$jsonData), true);
            } else {
                $result['status'] = 'warning';
                $result['message'] = 'API返回了非JSON格式的数据';
                $result['details']['is_json'] = false;
                $result['details']['json_error'] = json_last_error_msg();
                $result['details']['preview'] = substr($response, 0, 200) . (strlen($response) > 200 ? '...' : '');
            }
        }
        
        curl_close($ch);
    } catch (Exception $e) {
        $result['status'] = 'error';
        $result['message'] = 'API测试异常: ' . $e->getMessage();
    }
    
    return $result;
}

// 测试文件权限
function testFilePermissions() {
    $result = [
        'name' => '文件权限测试',
        'status' => 'success',
        'message' => '文件系统权限正常',
        'details' => []
    ];
    
    // 测试日志文件写入权限
    $testFile = 'test_permission.txt';
    try {
        $testContent = '测试内容: ' . time();
        file_put_contents($testFile, $testContent);
        
        if (file_exists($testFile) && file_get_contents($testFile) === $testContent) {
            $result['details']['write_access'] = '通过';
            unlink($testFile); // 清理测试文件
        } else {
            $result['status'] = 'error';
            $result['message'] = '文件写入权限不足';
            $result['details']['write_access'] = '失败';
        }
    } catch (Exception $e) {
        $result['status'] = 'error';
        $result['message'] = '文件操作异常: ' . $e->getMessage();
    }
    
    return $result;
}

// 执行测试
$testResults = runTests();

// 生成HTML报告
echo '<!DOCTYPE html>';
echo '<html lang="zh-CN">';
echo '<head>';
echo '    <meta charset="UTF-8">';
echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '    <title>API连通性测试报告</title>';
echo '    <style>';
echo '        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }';
echo '        h1 { color: #2c3e50; }';
echo '        .test-container { margin-bottom: 20px; padding: 15px; border-radius: 5px; }';
echo '        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }';
echo '        .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }';
echo '        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }';
echo '        .test-name { font-weight: bold; margin-bottom: 5px; }';
echo '        .test-message { margin-bottom: 10px; }';
echo '        .test-details { background-color: rgba(255, 255, 255, 0.5); padding: 10px; border-radius: 3px; font-family: monospace; white-space: pre-wrap; }';
echo '        .button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }';
echo '        .button:hover { background-color: #0056b3; }';
echo '        .summary { margin-bottom: 20px; padding: 15px; background-color: #e9ecef; border-radius: 5px; }';
echo '        .error-count { color: #dc3545; font-weight: bold; }';
echo '        .warning-count { color: #ffc107; font-weight: bold; }';
echo '    </style>';
echo '</head>';
echo '<body>';
echo '    <h1>Poster系统 - API连通性测试报告</h1>';

// 计算统计信息
$errorCount = 0;
$warningCount = 0;
foreach ($testResults as $test) {
    if ($test['status'] === 'error') {
        $errorCount++;
    } elseif ($test['status'] === 'warning') {
        $warningCount++;
    }
}

// 显示汇总信息
echo '    <div class="summary">';
echo '        <p>测试完成时间: ' . date('Y-m-d H:i:s') . '</p>';
echo '        <p>总测试数: ' . count($testResults) . ', 错误: <span class="error-count">' . $errorCount . '</span>, 警告: <span class="warning-count">' . $warningCount . '</span></p>';
echo '    </div>';

// 显示详细测试结果
foreach ($testResults as $test) {
    echo '    <div class="test-container ' . $test['status'] . '">';
    echo '        <div class="test-name">' . $test['name'] . '</div>';
    echo '        <div class="test-message">' . $test['message'] . '</div>';
    
    if (!empty($test['details'])) {
        echo '        <div class="test-details">';
        foreach ($test['details'] as $key => $value) {
            echo ucwords(str_replace('_', ' ', $key)) . ': ' . $value . "\n";
        }
        echo '        </div>';
    }
    
    echo '    </div>';
}

echo '    <div style="margin-top: 30px;">';
echo '        <a href="index.php" class="button">返回登录页面</a>';
echo '        <a href="api_test.php" class="button" style="margin-left: 10px;">重新测试</a>';
echo '    </div>';
echo '</body>';
echo '</html>';