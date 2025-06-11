<?php
require_once 'includes/db_connection.php';

// 获取页面标题
$sql = "SELECT content FROM page_contents WHERE section = 'banner'";
$result = $conn->query($sql);
$title = "请使用手机访问";
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $title = $row['content'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            text-align: center;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #007BFF;
            font-size: 2em;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.2em;
            color: #555;
            margin-bottom: 30px;
        }
        .qrcode {
            margin: 30px 0;
        }
        .qrcode img {
            max-width: 200px;
            height: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }
        .back-btn {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>请使用手机访问</h1>
        <p>此页面专为移动设备优化，请使用您的手机扫描下方二维码访问。</p>
        
        <div class="qrcode">
            <!-- 这里可以替换为实际的二维码图片 -->
            <img src="https://picsum.photos/200/200?random=1" alt="二维码">
            <p>或在手机浏览器中访问：<a href="javascript:window.location.href">当前页面</a></p>
        </div>
        
        <a href="javascript:history.back()" class="back-btn">返回上一页</a>
    </div>
</body>
</html>
