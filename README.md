# PHP+MySQL响应式落地页管理系统

基于PHP和MySQL开发的轻量级落地页管理系统，支持自适应布局、后台内容管理、APK下载与统计功能。

## 系统功能特点

### 核心功能
- **响应式设计**：自动适配电脑端和手机端显示
- **模块化内容管理**：4大模块（Banner、产品图、APK下载、文字/广告图）均可后台独立配置
- **APK管理**：支持APK上传、版本管理与下载统计
- **图片处理**：上传图片自动转换为Base64格式存储
- **访问控制**：支持设置仅手机端访问模式
- **数据统计**：记录访问量、下载量、设备类型等数据

### 技术栈
- 后端：PHP 7.4+
- 数据库：MySQL 5.7+
- 前端：HTML5/CSS3/JavaScript、Bootstrap 4响应式框架
- 服务器：Apache/Nginx

## 快速部署指南

### 环境要求
- PHP 7.4+（需启用`fileinfo`, `gd`, `mysqli`扩展）
- MySQL 5.7+
- Web服务器（Apache/Nginx）

### 安装步骤

1. **创建数据库**
```sql
CREATE DATABASE landing_page DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **导入数据库结构**
```bash
mysql -u [用户名] -p landing_page < database.sql
```

3. **配置数据库连接**
修改`includes/db_connection.php`文件：
```php
$db_host = "localhost";     // 数据库主机
$db_user = "root";          // 数据库用户名
$db_pass = "password";      // 数据库密码
$db_name = "landing_page";  // 数据库名称
```

4. **创建上传目录**
```bash
mkdir -p uploads/{apks,images,stats}
chmod -R 755 uploads
```

5. **访问系统**
- 前台落地页：访问网站根目录
- 后台管理系统：访问`admin/`目录，首次登录需创建管理员账号

## 目录结构说明

```
project/
├── admin/                  # 后台管理系统
│   ├── assets/             # 静态资源
│   ├── includes/           # 后台功能模块
│   ├── pages/              # 后台页面
│   ├── index.php           # 后台入口
│   └── login.php           # 登录页面
├── includes/               # 公共功能模块
│   ├── db_connection.php   # 数据库连接
│   ├── functions.php       # 公共函数
│   ├── image_process.php   # 图片处理
│   └── stats.php           # 统计功能
├── uploads/                # 上传文件存储
│   ├── apks/               # APK文件
│   ├── images/             # 图片文件
│   └── stats/              # 统计日志
├── desktop_redirect.php    # 电脑端重定向页面
├── index.php               # 前台落地页入口
├── database.sql            # 数据库结构
└── README.md               # 项目说明文档
```

## 后台管理功能

### 内容管理
- **Banner管理**：上传图片、设置标题和描述
- **产品图管理**：多图上传、排序和展示样式设置
- **APK管理**：上传APK文件、设置版本信息和下载链接
- **文字/广告图管理**：富文本编辑、广告图上传与链接设置

### 系统设置
- **访问模式**：开启/关闭仅手机端访问模式
- **统计设置**：开启/关闭数据统计功能
- **基础配置**：设置网站标题、关键词等SEO信息

### 数据统计
- **访问统计**：查看访问量趋势、来源渠道
- **下载统计**：APK下载量、设备类型分布
- **数据导出**：支持导出为CSV/Excel格式报表

## 手机访问限制功能

系统支持通过后台开关设置仅允许手机端访问：
1. 开启开关后，电脑端访问会重定向到提示页面
2. 提示页面包含二维码和手机访问引导
3. 支持手动关闭限制，恢复全设备访问

## 数据库设计

### 主要数据表
1. `config` - 系统配置表
   - `id`, `key`, `value`, `description`

2. `page_contents` - 页面内容表
   - `id`, `section`, `title`, `content`, `image_base64`, `created_at`

3. `apks` - APK管理表
   - `id`, `version`, `file_name`, `file_size`, `uploaded_at`, `is_latest`

4. `visits` - 访问统计表
   - `id`, `ip`, `user_agent`, `visit_time`, `device_type`, `page_section`

5. `downloads` - 下载记录表
   - `id`, `apk_id`, `ip`, `user_agent`, `device_type`, `download_time`

## 维护与支持

### 常见问题
- 图片上传失败：检查`uploads/images`目录权限
- 数据库连接错误：确认`includes/db_connection.php`配置正确
- 手机端检测异常：更新`includes/device_detect.php`中的UserAgent规则

### 安全建议
1. 定期备份数据库和上传文件
2. 修改后台默认路径（如将`admin/`重命名为自定义路径）
3. 启用服务器SSL证书，确保数据传输安全
4. 定期更新PHP和MySQL版本

### 二次开发
如需扩展功能，可参考以下接口：
- `includes/functions.php`：提供核心业务逻辑
- `admin/includes/api.php`：后台API接口
- `includes/stats.php`：统计功能接口

## 许可证

本项目遵循MIT许可证，允许自由修改和商业使用，但请保留原作者信息。
