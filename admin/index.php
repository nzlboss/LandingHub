<?php
session_start();
require_once '../includes/db_connection.php';

// 检查登录状态
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// 获取页面内容
$sections = ['banner', 'products', 'download', 'description'];
$page_contents = [];

foreach ($sections as $section) {
    $sql = "SELECT * FROM page_contents WHERE section = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $section);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $page_contents[$section] = $row;
    }
}

// 获取配置
$config = [];
$sql = "SELECT name, value FROM config";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $config[$row['name']] = $row['value'];
}

// 获取统计数据
$stats = [];
if ($config['stats_enabled'] == '1') {
    // 总访问量
    $sql = "SELECT COUNT(*) as total_visits FROM visits";
    $result = $conn->query($sql);
    $stats['total_visits'] = $result->fetch_assoc()['total_visits'];
    
    // 今日访问量
    $today = date('Y-m-d');
    $sql = "SELECT COUNT(*) as today_visits FROM visits WHERE DATE(visit_time) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['today_visits'] = $result->fetch_assoc()['today_visits'];
    
    // 总下载量
    $sql = "SELECT COUNT(*) as total_downloads FROM downloads";
    $result = $conn->query($sql);
    $stats['total_downloads'] = $result->fetch_assoc()['total_downloads'];
    
    // 设备分布
    $sql = "SELECT device_type, COUNT(*) as count FROM visits GROUP BY device_type";
    $result = $conn->query($sql);
    $device_stats = [];
    while ($row = $result->fetch_assoc()) {
        $device_stats[$row['device_type']] = $row['count'];
    }
    $stats['device_stats'] = $device_stats;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理系统</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
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
        .section {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .stats-card {
            display: inline-block;
            width: 200px;
            background-color: #f8f9fa;
            padding: 15px;
            margin: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .stats-card h3 {
            margin: 0;
            color: #6c757d;
        }
        .stats-card p {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        input[type="file"] {
            margin-top: 5px;
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
        <h1>后台管理系统</h1>
        <div class="nav">
            <a href="index.php">首页</a>
            <a href="settings.php">系统设置</a>
            <a href="stats.php">统计数据</a>
            <a href="logout.php" class="logout">退出登录</a>
        </div>
        
        <h2>统计概览</h2>
        <?php if ($config['stats_enabled'] == '1'): ?>
        <div class="stats-section">
            <div class="stats-card">
                <h3>总访问量</h3>
                <p><?php echo $stats['total_visits']; ?></p>
            </div>
            <div class="stats-card">
                <h3>今日访问</h3>
                <p><?php echo $stats['today_visits']; ?></p>
            </div>
            <div class="stats-card">
                <h3>总下载量</h3>
                <p><?php echo $stats['total_downloads']; ?></p>
            </div>
            <div class="stats-card">
                <h3>设备分布</h3>
                <p>
                    移动端: <?php echo isset($stats['device_stats']['mobile']) ? $stats['device_stats']['mobile'] : 0; ?><br>
                    桌面端: <?php echo isset($stats['device_stats']['desktop']) ? $stats['device_stats']['desktop'] : 0; ?>
                </p>
            </div>
        </div>
        <?php else: ?>
        <p>统计功能已禁用，可在<a href="settings.php">系统设置</a>中启用</p>
        <?php endif; ?>
        
        <h2>页面内容管理</h2>
        <form action="update_content.php" method="post" enctype="multipart/form-data">
            <div class="section">
                <h3>头部横幅 (Banner)</h3>
                <div class="form-group">
                    <label for="banner_content">标题内容:</label>
                    <input type="text" id="banner_content" name="banner_content" 
                           value="<?php echo htmlspecialchars($page_contents['banner']['content']); ?>">
                </div>
                <div class="form-group">
                    <label for="banner_image">横幅图片:</label>
                    <?php if (!empty($page_contents['banner']['image_base64'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo $page_contents['banner']['image_base64']; ?>" 
                         alt="Banner" style="max-width: 300px; margin-bottom: 10px;">
                    <?php endif; ?>
                    <input type="file" id="banner_image" name="banner_image">
                </div>
            </div>
            
            <div class="section">
                <h3>产品图</h3>
                <div class="form-group">
                    <label for="products_content">产品描述:</label>
                    <textarea id="products_content" name="products_content" rows="4"><?php 
                        echo htmlspecialchars($page_contents['products']['content']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="products_image">产品图片:</label>
                    <?php if (!empty($page_contents['products']['image_base64'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo $page_contents['products']['image_base64']; ?>" 
                         alt="Products" style="max-width: 300px; margin-bottom: 10px;">
                    <?php endif; ?>
                    <input type="file" id="products_image" name="products_image">
                </div>
            </div>
            
            <div class="section">
                <h3>APK下载</h3>
                <div class="form-group">
                    <label for="download_content">下载说明:</label>
                    <input type="text" id="download_content" name="download_content" 
                           value="<?php echo htmlspecialchars($page_contents['download']['content']); ?>">
                </div>
                <div class="form-group">
                    <label for="download_apk">APK文件:</label>
                    <?php if (!empty($page_contents['download']['apk_path'])): ?>
                    <p>当前版本: <?php echo htmlspecialchars($page_contents['download']['apk_version']); ?> 
                       (文件: <?php echo basename($page_contents['download']['apk_path']); ?>)</p>
                    <?php endif; ?>
                    <input type="file" id="download_apk" name="download_apk" accept=".apk">
                    <div class="form-group">
                        <label for="apk_version">APK版本号:</label>
                        <input type="text" id="apk_version" name="apk_version" placeholder="例如: 1.0.0">
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h3>文字描述或广告图</h3>
                <div class="form-group">
                    <label for="description_content">描述内容:</label>
                    <textarea id="description_content" name="description_content" rows="6"><?php 
                        echo htmlspecialchars($page_contents['description']['content']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="description_image">广告图片:</label>
                    <?php if (!empty($page_contents['description']['image_base64'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo $page_contents['description']['image_base64']; ?>" 
                         alt="Description" style="max-width: 300px; margin-bottom: 10px;">
                    <?php endif; ?>
                    <input type="file" id="description_image" name="description_image">
                </div>
            </div>
            
            <button type="submit">保存更改</button>
        </form>
    </div>
</body>
</html>
