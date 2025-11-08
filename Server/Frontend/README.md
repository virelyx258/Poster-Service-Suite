# Poster登录注册系统

这是一个基于PHP的简单登录注册系统，实现了与Poster API的交互功能。

## 功能说明

1. **登录功能**：向 `https://www.rsv.ee/api/v2/login` 发送POST请求
   - 自动判断登录类型：如果输入包含"@+域名"则为邮箱登录（login_type=1），否则为账号登录（login_type=2）
   - 成功登录后弹窗提示"登录成功"
   - 登录失败显示API返回的错误信息

2. **注册功能**：向 `https://www.rsv.ee/api/v2/signup` 发送POST请求
   - 需要填写账号、密码、确认密码和邮箱
   - 包含验证码功能（当前为测试模式）
   - 成功注册后弹窗提示"注册成功"并自动切换到登录页面
   - 注册失败显示API返回的错误信息

## 使用方法

### 安装PHP环境

在运行这个系统之前，您需要在计算机上安装PHP。您可以从 [PHP官方网站](https://www.php.net/downloads) 下载并安装适合您操作系统的PHP版本。

### 启动服务器

1. 打开命令提示符（Windows）或终端（macOS/Linux）
2. 导航到项目目录：`cd c:\Users\hi\Desktop\Poster_Web`
3. 启动PHP内置服务器：`php -S localhost:8000`
4. 打开浏览器，访问：`http://localhost:8000`

### 测试注册功能

由于SMTP邮件发送可能存在配置问题，当前系统使用了测试模式：
- 在注册页面，您可以输入任意有效的邮箱地址
- 对于验证码，直接输入 `123456` 即可通过验证

## 文件说明

- `index.php`：前端页面，包含登录和注册表单的HTML、CSS和JavaScript代码
- `api_handler.php`：后端处理文件，实现了API调用和验证码功能
- `README.md`：本说明文件

## 注意事项

- 这是一个简化版的系统，实际生产环境中应加强安全性措施
- 验证码功能目前处于测试模式，如果需要实际发送邮件，请修改 `api_handler.php` 中的相关代码
- 确保您的网络环境可以访问 `https://www.rsv.ee/api/v2` 接口