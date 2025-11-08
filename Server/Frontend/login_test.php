<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录测试工具</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #3498db;
            margin-top: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-secondary {
            background-color: #e74c3c;
        }
        .btn-secondary:hover {
            background-color: #c0392b;
        }
        .debug-panel {
            margin-top: 30px;
            padding: 15px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .debug-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .debug-content {
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-all;
            background-color: #fff;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 400px;
            overflow-y: auto;
        }
        .tabs {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        .tab {
            padding: 10px 15px;
            cursor: pointer;
            border: 1px solid #ddd;
            border-bottom: none;
            background-color: #f5f5f5;
            margin-right: 5px;
            border-radius: 4px 4px 0 0;
        }
        .tab.active {
            background-color: #fff;
            font-weight: bold;
            color: #3498db;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            margin-left: 10px;
        }
        .status.success {
            background-color: #d4edda;
            color: #155724;
        }
        .status.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .help-section {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .help-section h3 {
            color: #1565c0;
            margin-top: 0;
        }
        .button-group {
            margin-top: 15px;
        }
        .button-group button {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>Poster系统 - 登录测试工具</h1>
    
    <div class="form-group">
        <label for="login-account">账号/邮箱</label>
        <input type="text" id="login-account" placeholder="请输入账号或邮箱" value="">
    </div>
    <div class="form-group">
        <label for="login-password">密码</label>
        <input type="password" id="login-password" placeholder="请输入密码" value="">
    </div>
    <div class="form-group">
        <label>登录类型:</label>
        <span id="login-type-display" class="status pending">自动检测中</span>
    </div>
    
    <div class="button-group">
        <button class="btn" onclick="testLogin()">测试登录</button>
        <button class="btn btn-secondary" onclick="clearDebug()">清空调试</button>
        <button class="btn" onclick="window.location.href='index.php'">返回登录页面</button>
    </div>
    
    <div class="tabs">
        <div class="tab active" onclick="switchTab('request')">请求信息</div>
        <div class="tab" onclick="switchTab('response')">响应信息</div>
        <div class="tab" onclick="switchTab('debug')">调试信息</div>
        <div class="tab" onclick="switchTab('logs')">日志文件</div>
    </div>
    
    <div class="debug-panel">
        <div class="tab-content active" id="request-content">
            <div class="debug-title">请求详情:</div>
            <div class="debug-content" id="request-info">请点击测试登录按钮开始测试...</div>
        </div>
        
        <div class="tab-content" id="response-content">
            <div class="debug-title">响应详情:</div>
            <div class="debug-content" id="response-info">请点击测试登录按钮开始测试...</div>
        </div>
        
        <div class="tab-content" id="debug-content">
            <div class="debug-title">调试信息:</div>
            <div class="debug-content" id="debug-info">请点击测试登录按钮开始测试...</div>
        </div>
        
        <div class="tab-content" id="logs-content">
            <div class="debug-title">日志文件内容:</div>
            <div class="debug-content" id="logs-info">
                <button class="btn btn-small" onclick="loadApiLog()">加载API日志</button>
                <button class="btn btn-small" onclick="loadRawInputLog()">加载原始输入日志</button>
                <div id="log-display" style="margin-top: 10px;">请选择要查看的日志文件...</div>
            </div>
        </div>
    </div>
    
    <div class="help-section">
        <h3>使用帮助</h3>
        <ul>
            <li>此工具用于测试登录功能并显示详细的调试信息</li>
            <li>请输入账号和密码，然后点击"测试登录"按钮</li>
            <li>系统会自动检测登录类型（邮箱或账号）</li>
            <li>切换标签页查看请求、响应和调试信息</li>
            <li>在"日志文件"标签页中可以查看服务器端日志</li>
        </ul>
    </div>
    
    <script>
        // 自动检测登录类型
        document.getElementById('login-account').addEventListener('input', function() {
            const account = this.value;
            const loginTypeDisplay = document.getElementById('login-type-display');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (account) {
                const loginType = emailRegex.test(account) ? '1' : '2';
                loginTypeDisplay.textContent = loginType === '1' ? '邮箱登录 (type=1)' : '账号登录 (type=2)';
            } else {
                loginTypeDisplay.textContent = '自动检测中';
            }
        });
        
        // 切换标签
        function switchTab(tabName) {
            // 隐藏所有标签内容
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // 移除所有标签的激活状态
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // 显示选中的标签内容和激活标签
            document.getElementById(tabName + '-content').classList.add('active');
            document.querySelector('.tab[onclick="switchTab(\'' + tabName + '\')"]').classList.add('active');
        }
        
        // 清空调试信息
        function clearDebug() {
            document.getElementById('request-info').textContent = '请点击测试登录按钮开始测试...';
            document.getElementById('response-info').textContent = '请点击测试登录按钮开始测试...';
            document.getElementById('debug-info').textContent = '请点击测试登录按钮开始测试...';
            document.getElementById('log-display').textContent = '请选择要查看的日志文件...';
        }
        
        // 格式化JSON数据
        function formatJson(json) {
            try {
                if (typeof json === 'string') {
                    return JSON.stringify(JSON.parse(json), null, 2);
                } else {
                    return JSON.stringify(json, null, 2);
                }
            } catch (e) {
                return String(json);
            }
        }
        
        // 测试登录
        function testLogin() {
            const account = document.getElementById('login-account').value;
            const password = document.getElementById('login-password').value;
            
            // 验证输入
            if (!account || !password) {
                alert('请填写完整的账号和密码');
                return;
            }
            
            // 判断登录类型
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const loginType = emailRegex.test(account) ? '1' : '2';
            
            // 构建请求数据
            const data = {
                login_type: loginType,
                account: account,
                passwd: password
            };
            
            // 显示请求信息
            const requestInfo = document.getElementById('request-info');
            requestInfo.textContent = `请求URL: api_handler.php?action=login
请求方法: POST
请求头: Content-Type: application/json
请求数据:\n${formatJson(data)}`;
            
            // 清空响应和调试信息
            document.getElementById('response-info').textContent = '正在发送请求...';
            document.getElementById('debug-info').textContent = '开始登录测试: ' + new Date().toLocaleString();
            
            // 记录开始时间
            const startTime = performance.now();
            
            // 发送请求
            fetch('api_handler.php?action=login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data),
                credentials: 'same-origin'
            })
            .then(response => {
                // 记录结束时间
                const endTime = performance.now();
                const duration = (endTime - startTime).toFixed(2);
                
                // 获取响应头信息
                let headers = '';
                response.headers.forEach((value, key) => {
                    headers += `${key}: ${value}\n`;
                });
                
                // 记录响应状态
                const debugInfo = document.getElementById('debug-info');
                debugInfo.textContent += `\n\n请求完成时间: ${new Date().toLocaleString()}\n请求耗时: ${duration}ms\nHTTP状态码: ${response.status}`;
                
                // 获取原始响应文本
                return response.text().then(text => {
                    // 显示响应信息
                    const responseInfo = document.getElementById('response-info');
                    responseInfo.textContent = `HTTP状态码: ${response.status}\n响应头:\n${headers}\n响应体:\n${text}`;
                    
                    try {
                        // 尝试解析为JSON
                        const jsonData = JSON.parse(text);
                        debugInfo.textContent += `\n\n响应格式: JSON (有效)\n响应数据结构:\n${JSON.stringify(getObjectStructure(jsonData), null, 2)}`;
                        
                        // 显示结果状态
                        if (jsonData && jsonData.code === 200) {
                            alert('登录测试成功');
                        } else {
                            let errorMessage = '登录测试失败';
                            if (jsonData && jsonData.result && jsonData.result.reason) {
                                errorMessage = jsonData.result.reason;
                            }
                            alert(errorMessage);
                        }
                    } catch (error) {
                        debugInfo.textContent += `\n\n响应格式: 非JSON\nJSON解析错误: ${error.message}`;
                        alert('登录测试失败: 服务器返回了非JSON格式的数据');
                    }
                });
            })
            .catch(error => {
                const endTime = performance.now();
                const duration = (endTime - startTime).toFixed(2);
                
                const debugInfo = document.getElementById('debug-info');
                debugInfo.textContent += `\n\n请求失败时间: ${new Date().toLocaleString()}\n请求耗时: ${duration}ms\n错误类型: 网络错误\n错误信息: ${error.message}`;
                
                const responseInfo = document.getElementById('response-info');
                responseInfo.textContent = `请求失败\n错误信息: ${error.message}`;
                
                alert('登录测试失败: 网络错误\n\n错误详情: ' + error.message);
            });
        }
        
        // 获取对象结构
        function getObjectStructure(obj) {
            if (obj === null || typeof obj !== 'object') {
                return typeof obj;
            }
            
            if (Array.isArray(obj)) {
                if (obj.length === 0) {
                    return '[]';
                }
                return ['Array(' + obj.length + ')'];
            }
            
            const structure = {};
            for (const key in obj) {
                if (obj.hasOwnProperty(key)) {
                    structure[key] = getObjectStructure(obj[key]);
                }
            }
            return structure;
        }
        
        // 加载API日志
        function loadApiLog() {
            fetch('api_log.txt')
            .then(response => {
                if (response.ok) {
                    return response.text();
                } else {
                    throw new Error('日志文件不存在或无法访问');
                }
            })
            .then(text => {
                document.getElementById('log-display').textContent = text || '日志文件为空';
            })
            .catch(error => {
                document.getElementById('log-display').textContent = '加载日志失败: ' + error.message;
            });
        }
        
        // 加载原始输入日志
        function loadRawInputLog() {
            fetch('raw_input.txt')
            .then(response => {
                if (response.ok) {
                    return response.text();
                } else {
                    throw new Error('日志文件不存在或无法访问');
                }
            })
            .then(text => {
                document.getElementById('log-display').textContent = text || '日志文件为空';
            })
            .catch(error => {
                document.getElementById('log-display').textContent = '加载日志失败: ' + error.message;
            });
        }
    </script>
</body>
</html>