<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POSTER服务套件 - 远程隧道创建系统</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .container {
            width: 600px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-secondary {
            background-color: #f44336;
        }
        .btn-secondary:hover {
            background-color: #d32f2f;
        }
        .btn-small {
            width: auto;
            padding: 5px 10px;
            font-size: 14px;
        }
        .form-toggle {
            text-align: center;
            margin-top: 15px;
        }
        .form-toggle a {
            color: #0066cc;
            text-decoration: none;
        }
        .form-toggle a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="tunnel-form">
            <h2>POSTER服务套件 远程隧道创建系统</h2>
            <div class="form-group">
                <label for="command-name">指令名称</label>
                <input type="text" id="command-name" placeholder="请输入指令名称">
            </div>
            <div class="form-group">
                <label for="device-name">设备名称</label>
                <input type="text" id="device-name" placeholder="请输入设备名称">
            </div>
            <div class="form-group">
                <label for="operation-type">操作类型</label>
                <select id="operation-type" onchange="toggleAdditionalCondition()">
                    <option value="shutdown">系统操作 | 关机 | shutdown</option>
                    <option value="reboot">系统操作 | 重启 | reboot</option>
                    <option value="run">文件操作 | 运行某文件 | run</option>
                    <option value="kill">进程操作 | 结束进程 | kill</option>
                </select>
            </div>
            <div class="form-group" id="additional-condition-container" style="display: none;">
                <label for="additional-condition">附加条件</label>
                <input type="text" id="additional-condition" placeholder="请输入附加条件">
            </div>
            <button class="btn" onclick="createTunnel()">新建指令</button>
        </div>

        <div id="login-form" style="display: none;">
            <h2>登录</h2>
            <div class="form-group">
                <label for="login-account">账号/邮箱</label>
                <input type="text" id="login-account" placeholder="请输入账号或邮箱">
            </div>
            <div class="form-group">
                <label for="login-password">密码</label>
                <input type="password" id="login-password" placeholder="请输入密码">
            </div>
            <button class="btn" onclick="login()">登录</button>
            <div class="form-toggle">
                <span>还没有账号？</span>
                <a href="javascript:void(0)" onclick="showRegisterForm()">立即注册</a>
            </div>
        </div>

        <div id="register-form" style="display: none;">
            <h2>注册</h2>
            <div class="form-group">
                <label for="register-account">账号</label>
                <input type="text" id="register-account" placeholder="请设置账号">
            </div>
            <div class="form-group">
                <label for="register-password">密码</label>
                <input type="password" id="register-password" placeholder="请设置密码">
            </div>
            <div class="form-group">
                <label for="register-confirm-password">确认密码</label>
                <input type="password" id="register-confirm-password" placeholder="请再次输入密码">
            </div>
            <div class="form-group">
                <label for="register-email">邮箱</label>
                <input type="email" id="register-email" placeholder="请输入邮箱">
            </div>
            <div class="form-group">
                <label for="register-code">验证码</label>
                <input type="text" id="register-code" placeholder="请输入验证码" style="width: 60%; display: inline-block;">
                <button class="btn btn-small btn-secondary" onclick="sendVerificationCode()">发送验证码</button>
            </div>
            <button class="btn" onclick="register()">注册</button>
            <div class="form-toggle">
                <span>已有账号？</span>
                <a href="javascript:void(0)" onclick="showLoginForm()">立即登录</a>
            </div>
        </div>
    </div>

    <script>
        function showRegisterForm() {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('register-form').style.display = 'block';
        }

        function showLoginForm() {
            document.getElementById('login-form').style.display = 'block';
            document.getElementById('register-form').style.display = 'none';
            document.getElementById('tunnel-form').style.display = 'none';
        }

        // 控制附加条件输入框的显示/隐藏
        function toggleAdditionalCondition() {
            const operationType = document.getElementById('operation-type').value;
            const container = document.getElementById('additional-condition-container');
            
            if (operationType === 'run' || operationType === 'kill') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
                document.getElementById('additional-condition').value = '';
            }
        }

        // 创建远程隧道
        function createTunnel() {
            const commandName = document.getElementById('command-name').value;
            const deviceName = document.getElementById('device-name').value;
            const operationType = document.getElementById('operation-type').value;
            const additionalCondition = document.getElementById('additional-condition').value;
            
            // 验证输入
            if (!commandName || !deviceName) {
                alert('请填写指令名称和设备名称');
                return;
            }
            
            // 如果需要附加条件，则验证是否填写
            if ((operationType === 'run' || operationType === 'kill') && !additionalCondition) {
                alert('当前操作类型需要填写附加条件');
                return;
            }
            
            // 构建请求数据
            const data = {
                name: commandName,
                device: deviceName,
                czlx: operationType,
                fjtj: (operationType === 'run' || operationType === 'kill') ? additionalCondition : ''
            };
            
            console.log('隧道创建请求数据:', data);
            
            // 显示加载状态
            const button = document.querySelector('button[onclick="createTunnel()"]');
            const originalButtonText = button.innerText;
            button.innerText = '创建中...';
            button.disabled = true;
            
            // 发送请求
            fetch('api_handler.php?action=create_tunnel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json; charset=Windows-1252',
                    'Accept-Charset': 'Windows-1252'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                console.log('响应状态:', response.status);
                
                // 获取原始响应文本
                return response.text().then(text => {
                    console.log('原始响应文本:', text);
                    
                    try {
                        // 尝试解析为JSON
                        const jsonData = JSON.parse(text);
                        return { isJson: true, data: jsonData, status: response.status };
                    } catch (error) {
                        console.error('JSON解析错误:', error);
                        return { isJson: false, data: text, status: response.status, parseError: error.message };
                    }
                });
            })
            .then(result => {
                console.log('处理后的隧道创建结果:', result);
                
                if (result.isJson) {
                    // 处理JSON响应
                    const jsonResult = result.data;
                    
                    if (jsonResult && jsonResult.code === 200) {
                        alert('隧道创建成功');
                        // 清空输入框
                        document.getElementById('command-name').value = '';
                        document.getElementById('device-name').value = '';
                        document.getElementById('operation-type').selectedIndex = 0;
                        toggleAdditionalCondition();
                    } else {
                        // 详细的错误信息处理
                        let errorMessage = '隧道创建失败';
                        
                        // 确保优先返回result.reason值
                        if (jsonResult && jsonResult.result && jsonResult.result.reason) {
                            errorMessage = jsonResult.result.reason;
                        } else if (jsonResult && jsonResult.msg) {
                            errorMessage = jsonResult.msg;
                        } else if (jsonResult) {
                            errorMessage = '未知的创建错误: ' + JSON.stringify(jsonResult);
                        }
                        
                        alert(errorMessage);
                    }
                } else {
                    // 处理非JSON响应
                    let errorMessage = '隧道创建请求返回了非预期的格式';
                    
                    if (result.status !== 200) {
                        errorMessage = `服务器返回错误 (状态码: ${result.status}): ${result.data.substring(0, 200)}`;
                    }
                    
                    alert(errorMessage);
                }
            })
            .catch(error => {
                console.error('隧道创建错误:', error);
                alert('网络错误: 无法连接到服务器。请检查您的网络连接或稍后再试。\n\n错误详情: ' + error.message);
            })
            .finally(() => {
                // 恢复按钮状态
                button.innerText = originalButtonText;
                button.disabled = false;
            });
        }

        function login() {
            const account = document.getElementById('login-account').value;
            const password = document.getElementById('login-password').value;
            
            // 验证输入
            if (!account || !password) {
                alert('请填写完整信息');
                return;
            }
            
            // 判断登录类型
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const loginType = emailRegex.test(account) ? '1' : '2';
            
            console.log('登录类型:', loginType, '账号:', account);
            
            // 构建请求数据
            const data = {
                login_type: loginType,
                account: account,
                passwd: password
            };
            
            console.log('请求数据:', data);
            
            // 显示加载状态
            const loginButton = document.querySelector('button[onclick="login()"]');
            const originalButtonText = loginButton.innerText;
            loginButton.innerText = '登录中...';
            loginButton.disabled = true;
            
            // 发送请求
            fetch('api_handler.php?action=login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json; charset=Windows-1252',
                    'Accept-Charset': 'Windows-1252'
                },
                body: JSON.stringify(data),
                credentials: 'same-origin' // 确保发送Cookie
            })
            .then(response => {
                console.log('响应状态:', response.status);
                console.log('响应头:', response.headers);
                
                // 获取原始响应文本
                return response.text().then(text => {
                    console.log('原始响应文本:', text);
                    
                    try {
                        // 尝试解析为JSON
                        const jsonData = JSON.parse(text);
                        return { isJson: true, data: jsonData, status: response.status };
                    } catch (error) {
                        console.error('JSON解析错误:', error);
                        // 返回非JSON数据和解析错误信息
                        return { isJson: false, data: text, status: response.status, parseError: error.message };
                    }
                });
            })
            .then(result => {
                console.log('处理后的登录结果:', result);
                
                if (result.isJson) {
                    // 处理JSON响应
                    const jsonResult = result.data;
                    
                    if (jsonResult && jsonResult.code === 200) {
                        alert('登录成功');
                        // 可以在这里添加登录成功后的跳转逻辑
                        // window.location.href = 'dashboard.html';
                    } else {
                        // 详细的错误信息处理
                        let errorMessage = '登录失败';
                        
                        // 特别处理HTML响应的情况
                        if (jsonResult && jsonResult.msg === 'html_response' && jsonResult.result) {
                            console.warn('检测到HTML响应错误');
                            errorMessage = jsonResult.result.reason;
                            if (jsonResult.result.preview) {
                                console.warn('HTML内容预览:', jsonResult.result.preview);
                            }
                            if (jsonResult.result.http_status) {
                                console.warn('HTTP状态码:', jsonResult.result.http_status);
                            }
                        } else if (jsonResult && jsonResult.result && jsonResult.result.reason) {
                            errorMessage = jsonResult.result.reason;
                        } else if (jsonResult && jsonResult.msg) {
                            errorMessage = jsonResult.msg;
                        } else if (jsonResult) {
                            errorMessage = '未知的登录错误: ' + JSON.stringify(jsonResult);
                        }
                        
                        alert(errorMessage);
                    }
                } else {
                    // 处理非JSON响应
                    let errorMessage = '登录请求返回了非预期的格式';
                    
                    // 检查是否是"Unexpected token < in JSON at position 0"错误
                    if (result.parseError && result.parseError.includes('Unexpected token < in JSON at position 0')) {
                        errorMessage = 'API返回了HTML内容而非预期的JSON数据。这通常意味着请求的API路径可能不正确，或服务器返回了错误页面。';
                        console.warn('检测到典型的HTML解析错误: ', result.parseError);
                        
                        // 检查是否包含HTML标签
                        if (result.data.includes('<')) {
                            console.warn('响应确实包含HTML标签');
                            // 提供API测试工具提示
                            if (confirm('建议使用API路径测试工具检查请求路径是否正确？')) {
                                window.location.href = 'api_test.php';
                            }
                        }
                    } else if (result.status !== 200) {
                        errorMessage = `服务器返回错误 (状态码: ${result.status}): ${result.data.substring(0, 200)}`;
                    }
                    
                    alert(errorMessage);
                }
            })
            .catch(error => {
                console.error('登录错误:', error);
                alert('网络错误: 无法连接到服务器。请检查您的网络连接或稍后再试。\n\n错误详情: ' + error.message);
                
                // 提供测试工具提示
                if (confirm('是否需要运行API连通性测试以排查问题？')) {
                    window.location.href = 'api_test.php';
                }
            })
            .finally(() => {
                // 恢复按钮状态
                loginButton.innerText = originalButtonText;
                loginButton.disabled = false;
            });
        }

        function sendVerificationCode() {
            const email = document.getElementById('register-email').value;
            
            if (!email) {
                alert('请输入邮箱');
                return;
            }
            
            // 简单验证邮箱格式
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('请输入有效的邮箱地址');
                return;
            }
            
            // 发送验证码请求
            fetch('api_handler.php?action=send_verification_code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json; charset=Windows-1252',
                    'Accept-Charset': 'Windows-1252'
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('验证码已发送，请查收邮箱');
                } else {
                    alert('发送失败：' + result.message);
                }
            })
            .catch(error => {
                alert('网络错误，请稍后重试');
                console.error('Error:', error);
            });
        }

        function register() {
            const account = document.getElementById('register-account').value;
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('register-confirm-password').value;
            const email = document.getElementById('register-email').value;
            const code = document.getElementById('register-code').value;
            
            // 验证输入
            if (!account || !password || !confirmPassword || !email || !code) {
                alert('请填写完整信息');
                return;
            }
            
            if (password !== confirmPassword) {
                alert('两次输入的密码不一致');
                return;
            }
            
            // 构建请求数据
            const data = {
                account: account,
                passwd: password,
                mail: email,
                code: code
            };
            
            // 发送请求
            fetch('api_handler.php?action=register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json; charset=Windows-1252',
                    'Accept-Charset': 'Windows-1252'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.code === 200) {
                    alert('注册成功');
                    showLoginForm(); // 注册成功后切换到登录页面
                } else {
                    alert(result.result ? result.result.reason : '注册失败');
                }
            })
            .catch(error => {
                alert('网络错误，请稍后重试');
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html>