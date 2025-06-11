<?php
session_start();
require_once '../includes/db_connection.php';

// 检查登录状态
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// 处理上传的图片
function processImage($fileInputName, $conn, $section) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === 0) {
        $fileTmpPath = $_FILES[$fileInputName]['tmp_name'];
        $fileName = $_FILES[$fileInputName]['name'];
        $fileSize = $_FILES[$fileInputName]['size'];
        $fileType = $_FILES[$fileInputName]['type'];
        
        // 检查文件类型
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($fileType, $allowedTypes)) {
            return false;
        }
        
        // 检查文件大小 (限制为5MB)
        if ($fileSize > 5 * 1024 * 1024) {
            return false;
        }
        
        // 读取文件内容并转换为Base64
        $fileContent = file_get_contents($fileTmpPath);
        $base64 = base64_encode($fileContent);
        
        // 更新数据库
        $sql = "UPDATE page_contents SET image_base64 = ? WHERE section = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $base64, $section);
        
        return $stmt->execute();
    }
    return true; // 如果没有上传文件，返回true表示操作成功
}

// 处理上传的APK
function processAPK($conn) {
    if (isset($_FILES['download_apk']) && $_FILES['download_apk']['error'] === 0) {
        $fileTmpPath = $_FILES['download_apk']['tmp_name'];
        $fileName = $_FILES['download_apk']['name'];
        $fileSize = $_FILES['download_apk']['size'];
        $fileType = $_FILES['download_apk']['type'];
        
        // 检查文件类型
        if ($fileType != 'application/vnd.android.package-archive' && 
            pathinfo($fileName, PATHINFO_EXTENSION) != 'apk') {
            return false;
        }
        
        // 检查文件大小 (限制为100MB)
        if ($fileSize > 100 * 1024 * 1024) {
            return false;
        }
        
        // 创建上传目录
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // 生成唯一文件名
        $uniqueFileName = 'app_' . time() . '.apk';
        $destPath = $uploadDir . $uniqueFileName;
        
        // 移动文件
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // 获取APK版本
            $apkVersion = !empty($_POST['apk_version']) ? $_POST['apk_version'] : '1.0.0';
            
            // 更新数据库
            $sql = "UPDATE page_contents SET apk_path = ?, apk_version = ? WHERE section = 'download'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $destPath, $apkVersion);
            
            return $stmt->execute();
        }
        return false;
    }
    return true; // 如果没有上传APK，返回true表示操作成功
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 开始事务
    $conn->begin_transaction();
    
    try {
        // 更新文本内容
        $sections = ['banner', 'products', 'download', 'description'];
        foreach ($sections as $section) {
            $contentKey = $section . '_content';
            if (isset($_POST[$contentKey])) {
                $content = $_POST[$contentKey];
                $sql = "UPDATE page_contents SET content = ? WHERE section = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $content, $section);
                $stmt->execute();
            }
        }
        
        // 处理上传的图片
        processImage('banner_image', $conn, 'banner');
        processImage('products_image', $conn, 'products');
        processImage('description_image', $conn, 'description');
        
        // 处理上传的APK
        processAPK($conn);
        
        // 提交事务
        $conn->commit();
        $successMessage = "内容已成功更新";
    } catch (Exception $e) {
        // 回滚事务
        $conn->rollback();
        $errorMessage = "更新失败: " . $e->getMessage();
    }
}

$conn->close();

// 重定向回首页
if (isset($successMessage)) {
    $_SESSION['success_message'] = $successMessage;
}
if (isset($errorMessage)) {
    $_SESSION['error_message'] = $errorMessage;
}
header('Location: index.php');
exit;
?>
