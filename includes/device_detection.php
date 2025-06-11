<?php
/**
 * 检测是否为移动设备
 * @return bool 如果是移动设备返回true，否则返回false
 */
function isMobile() {
    // 检查User-Agent中是否包含移动设备特征字符串
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = [
        'iphone', 'ipad', 'ipod', 'android', 'blackberry', 'windows phone', 
        'tablet', 'mobile', 'pda', 'kindle', 'silk', 'playbook'
    ];
    
    foreach ($mobile_agents as $agent) {
        if (strpos(strtolower($user_agent), $agent) !== false) {
            return true;
        }
    }
    
    // 额外检查屏幕尺寸
    // 注意：仅通过PHP无法准确获取屏幕尺寸，这只是一个简单判断
    if (isset($_SERVER['HTTP_ACCEPT']) && 
        (strpos($_SERVER['HTTP_ACCEPT'], 'text/vnd.wap.wml') !== false || 
         strpos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') !== false)) {
        return true;
    }
    
    return false;
}
?>
