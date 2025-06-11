<?php
require_once 'includes/db_connection.php';
require_once 'includes/device_detection.php';

// 获取配置
$config = [];
$sql = "SELECT name, value FROM config";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $config[$row['name']] = $row['value'];
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

// 记录访问统计
if ($config['stats_enabled'] == '1') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $device_type = isMobile() ? 'mobile' : 'desktop';
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    
    $sql = "INSERT INTO visits (ip_address, user_agent, device_type, referrer) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $ip, $user_agent, $device_type, $referrer);
    $stmt->execute();
}

$conn->close();

// 如果设置为仅手机访问且当前不是手机设备，则显示提示
if ($config['mobile_only'] == '1' && !isMobile()) {
    header('Location: desktop_redirect.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_contents['banner']['content']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .banner {
            background-color: #007BFF;
            color: white;
            text-align: center;
            padding: 80px 0;
        }
        .banner h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        .banner p {
            font-size: 1.2em;
            margin-bottom: 40px;
        }
        .banner img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .products {
            padding: 60px 0;
            text-align: center;
        }
        .products h2 {
            font-size: 2em;
            margin-bottom: 40px;
        }
        .products img {
            max-width: 80%;
            height: auto;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .products p {
            font-size: 1.1em;
            color: #555;
        }
        .download {
            background-color: #f8f9fa;
            padding: 80px 0;
            text-align: center;
        }
        .download h2 {
            font-size: 2em;
            margin-bottom: 30px;
        }
        .download-btn {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.2em;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .download-btn:hover {
            background-color: #218838;
        }
        .description {
            padding: 60px 0;
        }
        .description h2 {
            font-size: 2em;
            margin-bottom: 30px;
            text-align: center;
        }
        .description p {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 30px;
        }
        .description img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
        }
        
        /* 响应式样式 */
        @media (max-width: 768px) {
            .banner {
                padding: 50px 0;
            }
            .banner h1 {
                font-size: 2em;
            }
            .products, .download, .description {
                padding: 40px 0;
            }
            .products h2, .download h2, .description h2 {
                font-size: 1.8em;
            }
            .download-btn {
                padding: 12px 25px;
                font-size: 1.1em;
            }
        }
    </style>
</head>
<body>
    <!-- 头部横幅 -->
    <div class="banner">
        <div class="container">
            <h1><?php echo htmlspecialchars($page_contents['banner']['content']); ?></h1>
            <?php if (!empty($page_contents['banner']['image_base64'])): ?>
            <img src="data:image/jpeg;base64,<?php echo $page_contents['banner']['image_base64']; ?>" 
                 alt="Banner">
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 产品图 -->
    <div class="products">
        <div class="container">
            <h2>产品展示</h2>
            <?php if (!empty($page_contents['products']['image_base64'])): ?>
            <img src="data:image/jpeg;base64,<?php echo $page_contents['products']['image_base64']; ?>" 
                 alt="Products">
            <?php endif; ?>
            <p><?php echo htmlspecialchars($page_contents['products']['content']); ?></p>
        </div>
    </div>
    
    <!-- APK下载 -->
    <div class="download">
        <div class="container">
            <h2><?php echo htmlspecialchars($page_contents['download']['content']); ?></h2>
            <?php if (!empty($page_contents['download']['apk_path'])): ?>
            <a href="download_apk.php" class="download-btn">
                下载 APK (v<?php echo htmlspecialchars($page_contents['download']['apk_version']); ?>)
            </a>
            <?php else: ?>
            <p>APK文件尚未上传</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 文字描述或广告图 -->
    <div class="description">
        <div class="container">
            <h2>关于我们</h2>
            <?php if (!empty($page_contents['description']['image_base64'])): ?>
            <img src="data:image/jpeg;base64,<?php echo $page_contents['description']['image_base64']; ?>" 
                 alt="Description">
            <?php endif; ?>
            <p><?php echo htmlspecialchars($page_contents['description']['content']); ?></p>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> 版权所有</p>
        </div>
    </footer>
</body>
</html>
