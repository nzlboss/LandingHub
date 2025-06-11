<?php
session_start();
require_once 'includes/db_connection.php';
require_once 'includes/device_detection.php';

// 获取下载信息
$sql = "SELECT apk_path, apk_version FROM page_contents WHERE section = 'download'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $apk_path = $row['apk_path'];
    $apk_version = $row['apk_version'];
    
    // 检查文件是否存在
    if (file_exists($apk_path)) {
        // 记录下载统计
        if (isset($_SESSION['stats_enabled']) && $_SESSION['stats_enabled'] == '1') {
            $ip = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $device_type = isMobile() ? 'mobile' : 'desktop';
            
            // 尝试从User-Agent中提取操作系统版本
            $os_version = 'Unknown';
            if (preg_match('/Android\s([0-9.]+)/', $user_agent, $matches)) {
                $os_version = 'Android ' . $matches[1];
            } elseif (preg_match('/iPhone OS\s([0-9_]+)/', $user_agent, $matches)) {
                $os_version = 'iOS ' . str_replace('_', '.', $matches[1]);
            }
            
            $sql = "INSERT INTO downloads (ip_address, user_agent, device_type, os_version, apk_version) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $ip, $user_agent, $device_type, $os_version, $apk_version);
            $stmt->execute();
        }
        
        // 设置下载头信息
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($apk_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($apk_path));
        
        // 输出文件内容
        readfile($apk_path);
        exit;
    } else {
        // 文件不存在
        header('HTTP/1.0 404 Not Found');
        echo "文件不存在";
    }
} else {
    // 数据库中没有APK信息
    header('HTTP/1.0 404 Not Found');
    echo "没有找到APK文件";
}

$conn->close();
?>
