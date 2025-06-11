<?php
// 数据库连接配置
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', 'your_password');
define('DB_NAME', 'landing_page');

// 创建数据库连接
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 设置字符集
$conn->set_charset("utf8mb4");
?>
