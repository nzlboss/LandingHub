-- 数据库：landing_page
CREATE DATABASE IF NOT EXISTS landing_page DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE landing_page;

-- 配置表
CREATE TABLE config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 初始配置数据
INSERT INTO config (name, value, description) VALUES
('mobile_only', '0', '是否仅允许手机访问 (1=是, 0=否)'),
('stats_enabled', '1', '是否启用统计功能 (1=是, 0=否)');

-- 页面内容表
CREATE TABLE page_contents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section VARCHAR(20) NOT NULL UNIQUE, -- banner, products, download, description
    content TEXT,
    image_base64 TEXT,
    apk_path VARCHAR(255),
    apk_version VARCHAR(20),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 初始页面内容
INSERT INTO page_contents (section, content) VALUES
('banner', '欢迎访问我们的应用'),
('products', '我们的产品介绍'),
('download', '点击下载我们的应用'),
('description', '这是一个很棒的应用，欢迎体验');

-- 访问统计表
CREATE TABLE visits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_type VARCHAR(20), -- desktop, mobile
    visit_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    referrer TEXT,
    page_section VARCHAR(20)
);

-- 下载统计表
CREATE TABLE downloads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_type VARCHAR(20),
    os_version VARCHAR(50),
    download_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    apk_version VARCHAR(20)
);
