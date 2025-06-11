<?php
session_start();
require_once '../includes/db_connection.php';

// 检查登录状态
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// 获取配置
$config = [];
$sql = "SELECT name, value FROM config";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $config[$row['name']] = $row['value'];
}

// 如果统计功能未启用，重定向到首页
if ($config['stats_enabled'] != '1') {
    header('Location: index.php');
    exit;
}

// 获取访问统计数据
$stats = [];

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

// 今日下载量
$sql = "SELECT COUNT(*) as today_downloads FROM downloads WHERE DATE(download_time) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$stats['today_downloads'] = $result->fetch_assoc()['today_downloads'];

// 设备分布
$sql = "SELECT device_type, COUNT(*) as count FROM visits GROUP BY device_type";
$result = $conn->query($sql);
$device_stats = [];
while ($row = $result->fetch_assoc()) {
    $device_stats[$row['device_type']] = $row['count'];
}
$stats['device_stats'] = $device_stats;

// 最近7天访问趋势
$seven_days = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $sql = "SELECT COUNT(*) as count FROM visits WHERE DATE(visit_time) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $seven_days[$date] = $count;
}
$stats['seven_days'] = $seven_days;

// 最近访问记录
$recent_visits = [];
$sql = "SELECT ip_address, user_agent, device_type, visit_time, referrer FROM visits 
        ORDER BY visit_time DESC LIMIT 10";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $recent_visits[] = $row;
}

// 最近下载记录
$recent_downloads = [];
$sql = "SELECT ip_address, user_agent, device_type, os_version, download_time, apk_version FROM downloads 
        ORDER BY download_time DESC LIMIT 10";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $recent_downloads[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>统计数据</title>
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
        .chart-container {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .table-container {
            margin: 20px 0;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
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
        <h1>统计数据</h1>
        <div class="nav">
            <a href="index.php">首页</a>
            <a href="settings.php">系统设置</a>
            <a href="stats.php">统计数据</a>
            <a href="logout.php" class="logout">退出登录</a>
        </div>
        
        <h2>统计概览</h2>
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
                <h3>今日下载</h3>
                <p><?php echo $stats['today_downloads']; ?></p>
            </div>
        </div>
        
        <div class="chart-container">
            <h3>最近7天访问趋势</h3>
            <table>
                <tr>
                    <th>日期</th>
                    <?php foreach ($stats['seven_days'] as $date => $count): ?>
                    <td><?php echo date('m-d', strtotime($date)); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>访问量</th>
                    <?php foreach ($stats['seven_days'] as $count): ?>
                    <td><?php echo $count; ?></td>
                    <?php endforeach; ?>
                </tr>
            </table>
        </div>
        
        <div class="chart-container">
            <h3>设备分布</h3>
            <table>
                <tr>
                    <th>设备类型</th>
                    <th>访问量</th>
                    <th>占比</th>
                </tr>
                <?php foreach ($stats['device_stats'] as $device => $count): ?>
                <tr>
                    <td><?php echo $device == 'mobile' ? '移动端' : '桌面端'; ?></td>
                    <td><?php echo $count; ?></td>
                    <td><?php echo round(($count / $stats['total_visits']) * 100, 2); ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="table-container">
            <h3>最近访问记录</h3>
            <table>
                <tr>
                    <th>IP地址</th>
                    <th>设备类型</th>
                    <th>访问时间</th>
                    <th>来源</th>
                </tr>
                <?php foreach ($recent_visits as $visit): ?>
                <tr>
                    <td><?php echo $visit['ip_address']; ?></td>
                    <td><?php echo $visit['device_type'] == 'mobile' ? '移动端' : '桌面端'; ?></td>
                    <td><?php echo $visit['visit_time']; ?></td>
                    <td><?php echo empty($visit['referrer']) ? '直接访问' : $visit['referrer']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="table-container">
            <h3>最近下载记录</h3>
            <table>
                <tr>
                    <th>IP地址</th>
                    <th>设备类型</th>
                    <th>系统版本</th>
                    <th>APK版本</th>
                    <th>下载时间</th>
                </tr>
                <?php foreach ($recent_downloads as $download): ?>
                <tr>
                    <td><?php echo $download['ip_address']; ?></td>
                    <td><?php echo $download['device_type'] == 'mobile' ? '移动端' : '桌面端'; ?></td>
                    <td><?php echo $download['os_version']; ?></td>
                    <td><?php echo $download['apk_version']; ?></td>
                    <td><?php echo $download['download_time']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>
