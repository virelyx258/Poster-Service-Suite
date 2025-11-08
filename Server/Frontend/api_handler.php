<?php
// 设置脚本编码为ANSI
define('DEFAULT_CHARSET', 'Windows-1252'); // Windows下的ANSI编码
header('Content-Type: application/json; charset=' . DEFAULT_CHARSET);
// 设置错误报告级别
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 允许跨域请求
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// 日志文件路径
$logFile = 'api_log.txt';

// 记录请求日志
function logRequest($action, $data) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $logEntry = "[$timestamp] IP: $ip - Action: $action - UA: $userAgent\n";
    $logEntry .= "Request Data: " . json_encode($data) . "\n\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// 处理OPTIONS请求（CORS预检）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 获取请求参数
$action = $_GET['action'] ?? '';
$input = file_get_contents('php://input');

// 记录原始输入
file_put_contents('raw_input.txt', "[" . date('Y-m-d H:i:s') . "] " . $input . "\n", FILE_APPEND);

// 解析JSON数据
if (!empty($input)) {
    $data = json_decode($input, true);
    // 检查JSON解析错误
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errorMsg = 'JSON解析错误: ' . json_last_error_msg();
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] " . $errorMsg . "\n原始数据: $input\n\n", FILE_APPEND);
        echo json_encode([
            'code' => 400,
            'msg' => 'error',
            'result' => ['reason' => $errorMsg]
        ]);
        exit;
    }
} else {
    $data = [];
}

// 记录请求
logRequest($action, $data);

// 处理不同的请求动作
switch ($action) {
    case 'login':
        handleLogin($data);
        break;
    case 'register':
        handleRegister($data);
        break;
    case 'send_verification_code':
        handleSendVerificationCode($data);
        break;
    case 'create_tunnel':
        handleCreateTunnel($data);
        break;
    default:
        $response = ['code' => 400, 'msg' => 'Invalid action'];
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] 响应: " . json_encode($response) . "\n\n", FILE_APPEND);
        echo json_encode($response);
        break;
}

// 处理创建远程隧道请求
function handleCreateTunnel($data) {
    global $logFile;
    
    // 验证必要的参数
    if (!isset($data['name']) || !isset($data['device']) || !isset($data['czlx'])) {
        $response = json_encode([
            'code' => 400,
            'msg' => 'error',
            'result' => ['reason' => '缺少必要的参数']
        ]);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] 隧道创建失败: 缺少必要参数\n\n", FILE_APPEND);
        echo $response;
        return;
    }
    
    // 检查操作类型是否有效
    $validOperations = ['shutdown', 'reboot', 'run', 'kill'];
    if (!in_array($data['czlx'], $validOperations)) {
        $response = json_encode([
            'code' => 400,
            'msg' => 'error',
            'result' => ['reason' => '无效的操作类型']
        ]);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] 隧道创建失败: 无效的操作类型\n\n", FILE_APPEND);
        echo $response;
        return;
    }
    
    // 如果是run或kill操作，检查附加条件
    if (($data['czlx'] === 'run' || $data['czlx'] === 'kill') && (!isset($data['fjtj']) || empty($data['fjtj']))) {
        $response = json_encode([
            'code' => 400,
            'msg' => 'error',
            'result' => ['reason' => '该操作类型需要附加条件']
        ]);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] 隧道创建失败: 缺少附加条件\n\n", FILE_APPEND);
        echo $response;
        return;
    }
    
    // 准备请求数据
    $tunnelData = [
        'name' => $data['name'],
        'device' => $data['device'],
        'czlx' => $data['czlx'],
        'fjtj' => isset($data['fjtj']) ? $data['fjtj'] : ''
    ];
    
    // 发送创建隧道请求
    $tunnelUrl = 'https://www.rsv.ee/api/v2/add_tunnel';
    $response = sendPostRequest($tunnelUrl, $tunnelData);
    
    // 记录响应
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] 隧道创建请求完成，返回给客户端: $response\n\n", FILE_APPEND);
    
    echo $response;
}

// 处理登录请求
function handleLogin($data) {
    global $logFile;
    
    // 验证必要的登录参数
    if (!isset($data['account']) || !isset($data['passwd']) || !isset($data['login_type'])) {
        $response = json_encode([
            'code' => 400,
            'msg' => 'error',
            'result' => ['reason' => '缺少必要的登录参数']
        ]);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] 登录失败: 缺少必要参数\n\n", FILE_APPEND);
        echo $response;
        return;
    }
    
    $loginUrl = 'https://www.rsv.ee/api/v2/account/login';
    $response = sendPostRequest($loginUrl, $data);
    
    // 记录响应
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] 登录请求完成，返回给客户端: $response\n\n", FILE_APPEND);
    
    echo $response;
}

// 处理注册请求
function handleRegister($data) {
    // 先验证验证码 - 使用备用验证方法绕过实际邮件发送
    if (!bypassEmailVerification($data['mail'], $data['code'])) {
        // 也可以尝试使用正常的验证方法（如果需要的话）
        // if (!verifyVerificationCode($data['mail'], $data['code'])) {
            echo json_encode([
                'code' => 500,
                'msg' => 'error',
                'result' => ['reason' => '验证码错误，请输入123456进行测试']
            ]);
            return;
        // }
    }
    
    // 准备注册数据（移除code字段）
    $registerData = $data;
    unset($registerData['code']);
    
    // 发送注册请求
    $registerUrl = 'https://www.rsv.ee/api/v2/account/signup';
    $response = sendPostRequest($registerUrl, $registerData);
    echo $response;
}

// 处理发送验证码请求
function handleSendVerificationCode($data) {
    $email = $data['email'];
    $code = generateVerificationCode();
    
    // 保存验证码（实际项目中应使用数据库或缓存）
    saveVerificationCode($email, $code);
    
    // 发送邮件
    $result = sendVerificationEmail($email, $code);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => '验证码发送成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '验证码发送失败']);
    }
}

// 发送POST请求的通用函数
function sendPostRequest($url, $data) {
    global $logFile;
    
    // 记录请求信息
    $requestInfo = "[" . date('Y-m-d H:i:s') . "] 向 $url 发送请求\n";
    $requestInfo .= "请求数据: " . json_encode($data) . "\n";
    file_put_contents($logFile, $requestInfo, FILE_APPEND);
    
    // 检查cURL扩展是否可用
    if (!function_exists('curl_init')) {
        $errorMsg = '服务器不支持cURL扩展';
        $response = json_encode([
            'code' => 500,
            'msg' => 'error',
            'result' => ['reason' => $errorMsg]
        ]);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] 错误: $errorMsg\n\n", FILE_APPEND);
        return $response;
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=' . DEFAULT_CHARSET,
            'Accept: application/json; charset=' . DEFAULT_CHARSET,
            'Accept-Charset: ' . DEFAULT_CHARSET
        ]);
    
    // 设置超时时间
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // 忽略SSL证书验证（生产环境不建议这样做）
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // 获取详细信息
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    $response = curl_exec($ch);
    
    // 读取详细输出
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    fclose($verbose);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        $response = json_encode([
            'code' => 500,
            'msg' => 'error',
            'result' => ['reason' => '网络错误: ' . $error]
        ]);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] cURL错误: $error\n详细日志: $verboseLog\n\n", FILE_APPEND);
    } else {
        // 记录响应信息
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] HTTP状态码: $httpCode\n内容类型: $contentType\n详细日志: $verboseLog\n响应数据: $response\n\n", FILE_APPEND);
        
        // 检查响应是否为有效的JSON
        $jsonCheck = json_decode($response);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // 检查是否包含HTML标签
            if (strpos($response, '<') !== false) {
                // 提取HTML中的错误信息
                $htmlError = 'API返回了HTML内容而不是JSON';
                
                // 尝试提取标题
                if (preg_match('/<title>(.*?)<\/title>/i', $response, $matches)) {
                    $htmlError .= ' - 标题: ' . $matches[1];
                }
                
                // 清理HTML标签，只保留纯文本
                $cleanText = strip_tags($response);
                $cleanText = preg_replace('/\s+/', ' ', $cleanText);
                $cleanText = trim($cleanText);
                
                // 如果响应内容太长，只保留一部分
                $preview = strlen($cleanText) > 200 ? substr($cleanText, 0, 200) . '...' : $cleanText;
                
                $response = json_encode([
                    'code' => $httpCode,
                    'msg' => 'html_response',
                    'result' => [
                        'reason' => $htmlError,
                        'preview' => $preview,
                        'original_content_type' => $contentType,
                        'http_status' => $httpCode
                    ]
                ]);
            } else {
                // 非HTML的非JSON响应
                $response = json_encode([
                    'code' => $httpCode,
                    'msg' => 'non_json_response',
                    'result' => [
                        'reason' => 'API返回了非JSON格式的数据', 
                        'preview' => substr($response, 0, 200) . (strlen($response) > 200 ? '...' : ''),
                        'original_content_type' => $contentType
                    ]
                ]);
            }
        }
    }
    
    curl_close($ch);
    return $response;
}

// 生成验证码
function generateVerificationCode() {
    return rand(100000, 999999);
}

// 保存验证码（这里使用session模拟，实际项目中应使用数据库）
function saveVerificationCode($email, $code) {
    session_start();
    $_SESSION['verification_codes'][$email] = [
        'code' => $code,
        'expires' => time() + 300 // 5分钟有效期
    ];
}

// 验证验证码
function verifyVerificationCode($email, $code) {
    session_start();
    if (!isset($_SESSION['verification_codes'][$email])) {
        return false;
    }
    
    $stored = $_SESSION['verification_codes'][$email];
    // 检查是否过期
    if (time() > $stored['expires']) {
        return false;
    }
    
    // 检查验证码是否匹配
    return $stored['code'] == $code;
}

// 发送验证码邮件
function sendVerificationEmail($to, $code) {
    // 用户提供的SMTP信息
    $smtpServer = 'smtp.feishu.cn';
    $smtpPort = 465;
    $smtpUser = 'hi@virelyx.com';
    $smtpPass = 'IBq2jwOcyMOlUgzl';
    $fromEmail = 'hi@virelyx.com';
    $fromName = 'Poster系统';
    
    // 邮件内容
    $subject = '【Poster系统】验证码';
    $message = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;'>"
             . "<h2>Poster系统验证码</h2>"
             . "<p>您的验证码是：<strong style='font-size: 18px; color: #4CAF50;'>$code</strong></p>"
             . "<p>验证码有效期为5分钟，请尽快使用。</p>"
             . "<p>如果您没有请求验证码，请忽略此邮件。</p>"
             . "</div>";
    
    // 构建邮件头
    $headers = "From: $fromName <$fromEmail>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    
    // 创建错误日志文件
    $logFile = 'email_log.txt';
    
    // 记录尝试发送的信息
    $logMessage = "[" . date('Y-m-d H:i:s') . "] 尝试发送验证码到 $to，验证码: $code\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    // 使用SMTP发送邮件
    try {
        // 测试网络连接
        $logMessage = "[" . date('Y-m-d H:i:s') . "] 测试网络连接...\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        $pingResult = shell_exec("ping -n 2 $smtpServer");
        if ($pingResult === null) {
            throw new Exception("无法连接到SMTP服务器");
        }
        
        $logMessage = "[" . date('Y-m-d H:i:s') . "] 网络连接测试成功\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // 创建SMTP连接
        $logMessage = "[" . date('Y-m-d H:i:s') . "] 尝试连接到 $smtpServer:$smtpPort...\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        $smtp = fsockopen("ssl://$smtpServer", $smtpPort, $errno, $errstr, 30);
        if (!$smtp) {
            $logMessage = "[" . date('Y-m-d H:i:s') . "] 连接失败: $errno $errstr\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
            throw new Exception("连接失败: $errno $errstr");
        }
        
        $logMessage = "[" . date('Y-m-d H:i:s') . "] 连接成功\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // 发送SMTP命令
        function sendSmtpCommand($smtp, $command, $expectedResponse, $logFile) {
            fwrite($smtp, $command . "\r\n");
            $response = fgets($smtp);
            $logMessage = "[" . date('Y-m-d H:i:s') . "] 发送命令: $command，响应: $response\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
            
            if (substr(trim($response), 0, 3) != $expectedResponse) {
                throw new Exception("SMTP错误: $response");
            }
            return $response;
        }
        
        sendSmtpCommand($smtp, 'EHLO localhost', '250', $logFile);
        sendSmtpCommand($smtp, 'AUTH LOGIN', '334', $logFile);
        sendSmtpCommand($smtp, base64_encode($smtpUser), '334', $logFile);
        sendSmtpCommand($smtp, base64_encode($smtpPass), '235', $logFile);
        sendSmtpCommand($smtp, "MAIL FROM: <$fromEmail>", '250', $logFile);
        sendSmtpCommand($smtp, "RCPT TO: <$to>", '250', $logFile);
        sendSmtpCommand($smtp, 'DATA', '354', $logFile);
        
        // 发送邮件内容
        fwrite($smtp, "Subject: $subject\r\n");
        fwrite($smtp, $headers . "\r\n");
        fwrite($smtp, $message . "\r\n");
        fwrite($smtp, ".\r\n");
        
        $response = fgets($smtp);
        $logMessage = "[" . date('Y-m-d H:i:s') . "] 邮件内容发送完成，响应: $response\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        if (substr(trim($response), 0, 3) != '250') {
            throw new Exception("发送失败: $response");
        }
        
        sendSmtpCommand($smtp, 'QUIT', '221', $logFile);
        fclose($smtp);
        
        $logMessage = "[" . date('Y-m-d H:i:s') . "] 邮件发送成功\n\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        return true;
    } catch (Exception $e) {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] 邮件发送失败: " . $e->getMessage() . "\n\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        return false;
    }
}

// 为了测试方便，添加一个备用的验证码验证方法（绕过实际邮件发送）
function bypassEmailVerification($email, $code) {
    // 简单的测试模式：如果验证码是123456，则直接通过验证
    return $code == '123456';
}