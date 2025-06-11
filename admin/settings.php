<?php
session_start();
require_once '../includes/db_connection.php';

// 检查登录状态
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile_only = isset($_POST['mobile_only']) ? 1 : 0;
    $stats_enabled = isset($_POST['stats_enabled']) ? 1 : 0;
    
    $sql = "UPDATE config SET value = ? WHERE name = 'mobile_only'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $mobile_only);
    $stmt->execute();
    
    $sql = "UPDATE config SET value = ? WHERE name = 'stats_enabled'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $stats_enabled);
    $stmt->execute();
    
    $message = "设置已更新";
}

// 获取当前配置
$config = [];
$sql = "SELECT name, value FROM config";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $config[$row['name']] = $row['value'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统设置</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .nav {
            margin-bottom: 20px;
        }
        .nav a {
            display: inline-block;
            margin-right: 10px;
            padding: 8px 12px;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
        }
        .nav a:hover {
            background-color: #0056b3;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        input[type="checkbox"] {
            margin-right: 5px;
        }
        button {
            padding: 8px 15px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 3px;
        }
        .logout {
            float: right;
            background-color: #dc3545;
        }
        .logout:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>系统设置</h1>
        <div class="nav">
            <a href="index.php">首页</a>
            <a href="settings.php">系统设置</a>
            <a href="stats.php">统计数据</a>
            <a href="logout.php" class="logout">退出登录</a>
        </div>
        
        <?php if (isset($message)): ?>
        <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form action="settings.php" method="post">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="mobile_only" <?php echo $config['mobile_only'] == '1' ? 'checked' : ''; ?>>
                    仅允许手机访问
                </label>
                <p style="color: #666; font-size: 0.9em;">启用后，电脑端访问将显示提示信息</p>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="stats_enabled" <?php echo $config['stats_enabled'] == '1' ? 'checked' : ''; ?>>
                    启用统计功能
                </label>
                <p style="color: #666; font-size: 0.9em;">收集并分析用户访问和下载数据</p>
            </div>
            
            <button type="submit">保存设置</button>
        </form>
    </div>
</body>
</html>
