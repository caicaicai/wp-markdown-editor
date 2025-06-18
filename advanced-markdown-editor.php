<?php
/**
 * Plugin Name: Advanced Markdown Editor
 * Plugin URI: https://github.com/caicaicai/wp-markdown-editor
 * Description: 为WordPress添加独立的Markdown编辑器，支持文章的Markdown编辑和发布，同时保持与原有编辑器的兼容性。
 * Version: 1.1.0
 * Author: xiaocaicai
 * License: GPL v2 or later
 * Text Domain: advanced-markdown-editor
 * Domain Path: /languages
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('ADVAMAED_VERSION', '1.0.0');
define('ADVAMAED_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ADVAMAED_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ADVAMAED_PLUGIN_BASENAME', plugin_basename(__FILE__));

// 主插件类
class ADVAMAED_Markdown_Editor {
    
    /**
     * 单例实例
     */
    private static $instance = null;
    
    /**
     * 获取单例实例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * 初始化插件
     */
    private function init() {
        // 插件激活和停用钩子
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // 初始化钩子
        add_action('init', array($this, 'init_plugin'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // AJAX钩子
        add_action('wp_ajax_advamaed_save_markdown_post', array($this, 'save_markdown_post'));
        add_action('wp_ajax_advamaed_get_markdown_post', array($this, 'get_markdown_post'));
        add_action('wp_ajax_advamaed_create_new_category', array($this, 'create_new_category'));
        add_action('wp_ajax_advamaed_upload_image_for_markdown', array($this, 'upload_image_for_markdown'));
        
        // 添加自定义post meta
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_post_meta'));
        
        // 在文章列表页面添加Markdown编辑链接
        add_filter('post_row_actions', array($this, 'add_markdown_edit_link'), 10, 2);
        add_filter('page_row_actions', array($this, 'add_markdown_edit_link'), 10, 2);
    }
    
    /**
     * 插件激活
     */
    public function activate() {
        // 创建必要的数据库表或选项
        add_option('advamaed_version', ADVAMAED_VERSION);
    }
    
    /**
     * 插件停用
     */
    public function deactivate() {
        // 清理工作
    }
    
    /**
     * 插件初始化
     */
    public function init_plugin() {
        // WordPress 4.6+ 自动加载翻译，无需手动调用 load_plugin_textdomain
    }
    
    /**
     * 管理页面初始化
     */
    public function admin_init() {
        // 检查用户权限
        if (!current_user_can('edit_posts')) {
            return;
        }
    }
    
    /**
     * 添加管理菜单
     */
    public function add_admin_menu() {
        // 菜单位置选项说明：
        // 6  - 在"文章"之后（当前设置）
        // 15 - 在"链接"位置（如果没有链接菜单）
        // 22 - 在"页面"之后，"评论"之前  
        // 25 - 在"评论"位置
        // 58 - 在外观之前
        // 61 - 在"外观"之后
        
        // 添加主菜单项
        add_menu_page(
            esc_html__('Markdown 编辑器', 'advanced-markdown-editor'),
            esc_html__('Markdown 编辑器', 'advanced-markdown-editor'),
            'edit_posts',
            'wp-markdown-editor',
            array($this, 'markdown_editor_page'),
            'dashicons-edit',
            6  // 推荐位置：在"文章"菜单之后，因为这是编辑相关的功能
        );
        
        // 添加子菜单项
        add_submenu_page(
            'wp-markdown-editor',
            esc_html__('新建文章', 'advanced-markdown-editor'),
            esc_html__('新建文章', 'advanced-markdown-editor'),
            'edit_posts',
            'wp-markdown-editor-new',
            array($this, 'markdown_editor_page')
        );
        
        add_submenu_page(
            'wp-markdown-editor',
            esc_html__('文章列表', 'advanced-markdown-editor'),
            esc_html__('文章列表', 'advanced-markdown-editor'),
            'edit_posts',
            'edit.php'
        );
    }
    
    /**
     * 加载管理页面脚本和样式
     */
    public function enqueue_admin_scripts($hook) {
        // 只在Markdown编辑器页面加载脚本
        if (strpos($hook, 'wp-markdown-editor') !== false) {
            // 加载WordPress媒体库脚本
            wp_enqueue_media();
            
            // 加载样式
            wp_enqueue_style(
                'advanced-markdown-editor-admin',
                ADVAMAED_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                ADVAMAED_VERSION
            );
            
            // 加载脚本
            wp_enqueue_script(
                'advanced-markdown-editor-admin',
                ADVAMAED_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'media-upload', 'media-views'),
                ADVAMAED_VERSION,
                true
            );
            
                    // 加载 Marked.js 用于Markdown解析 (本地版本)
                    wp_enqueue_script(
                'advanced-markdown-editor-marked',
                ADVAMAED_PLUGIN_URL . 'assets/js/marked.min.js',
                array(),
                ADVAMAED_VERSION,
                true
            );
            
            // 传递AJAX URL和nonce
            wp_localize_script('advanced-markdown-editor-admin', 'advancedMarkdownEditor', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('advanced_markdown_editor_nonce'),
                'strings' => array(
                    'saving' => __('保存中...', 'advanced-markdown-editor'),
                    'saved' => __('已保存', 'advanced-markdown-editor'),
                    'error' => __('保存失败', 'advanced-markdown-editor'),
                    'selectImage' => __('选择图片', 'advanced-markdown-editor'),
                    'insertImage' => __('插入图片', 'advanced-markdown-editor'),
                    'uploadImage' => __('上传图片', 'advanced-markdown-editor'),
                )
            ));
        }
    }
    
    /**
     * Markdown编辑器页面
     */
    public function markdown_editor_page() {
        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        $current_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        
        // 如果是新建文章页面，不需要nonce验证
        if ($current_page === 'wp-markdown-editor-new') {
            $post_id = 0; // 确保是新建文章
        }
        // 验证nonce用于编辑现有文章
        elseif ($post_id && (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'edit_markdown_post_' . $post_id))) {
            wp_die(esc_html__('安全验证失败', 'advanced-markdown-editor'));
        }
        
        $post = null;
        
        if ($post_id) {
            $post = get_post($post_id);
            if (!$post || !current_user_can('edit_post', $post_id)) {
                wp_die(esc_html__('您没有权限编辑此文章。', 'advanced-markdown-editor'));
            }
        }
        
        include ADVAMAED_PLUGIN_DIR . 'templates/editor.php';
    }
    
    /**
     * 保存Markdown文章
     */
    public function save_markdown_post() {
        // 验证nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'advanced_markdown_editor_nonce')) {
            wp_die(esc_html__('安全验证失败', 'advanced-markdown-editor'));
        }
        
        // 检查权限
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('权限不足', 'advanced-markdown-editor'));
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $markdown_content = isset($_POST['markdown_content']) ? wp_kses_post(wp_unslash($_POST['markdown_content'])) : '';
        $html_content = isset($_POST['html_content']) ? wp_kses_post(wp_unslash($_POST['html_content'])) : '';
        $status = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : 'draft';
        
        // 处理分类
        $categories = array();
        if (isset($_POST['categories']) && is_array($_POST['categories'])) {
            $categories = array_map('intval', $_POST['categories']);
            // 过滤掉无效的分类ID
            $categories = array_filter($categories, function($cat_id) {
                return term_exists($cat_id, 'category');
            });
        }
        
        // 如果没有选择分类，使用默认分类
        if (empty($categories)) {
            $default_category = get_option('default_category');
            if ($default_category) {
                $categories = array(intval($default_category));
            }
        }
        
        // 处理标签
        $tags = array();
        if (isset($_POST['tags']) && is_array($_POST['tags'])) {
            $tags = array_map('sanitize_text_field', wp_unslash($_POST['tags']));
            $tags = array_filter($tags, function($tag) {
                return !empty(trim($tag));
            });
        }
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $html_content,
            'post_status' => $status,
            'post_type' => 'post'
        );
        
        if ($post_id) {
            // 检查编辑权限
            if (!current_user_can('edit_post', $post_id)) {
                wp_send_json_error(esc_html__('您没有权限编辑此文章', 'advanced-markdown-editor'));
                return;
            }
            
            $post_data['ID'] = $post_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
        }
        
        if ($result && !is_wp_error($result)) {
            // 保存Markdown内容到meta
            update_post_meta($result, '_markdown_content', $markdown_content);
            update_post_meta($result, '_is_markdown_post', true);
            
            // 设置文章分类
            if (!empty($categories)) {
                wp_set_post_categories($result, $categories);
            }
            
            // 设置文章标签
            if (!empty($tags)) {
                wp_set_post_tags($result, $tags);
            }
            
            // 获取文章信息
            $post = get_post($result);
            $view_link = '';
            $edit_link = wp_nonce_url(admin_url('admin.php?page=wp-markdown-editor&post=' . $result), 'edit_markdown_post_' . $result);
            
            if ($status === 'publish') {
                $view_link = get_permalink($result);
            } else if ($status === 'private') {
                $view_link = get_permalink($result);
            }
            
            wp_send_json_success(array(
                'post_id' => $result,
                'post_status' => $status,
                'view_link' => $view_link,
                'edit_link' => $edit_link,
                'is_new_post' => !$post_id,
                'message' => $this->get_save_success_message($status, !$post_id)
            ));
        } else {
            wp_send_json_error(__('文章保存失败', 'advanced-markdown-editor'));
        }
    }
    
    /**
     * 获取保存成功消息
     */
    private function get_save_success_message($status, $is_new_post) {
        if ($status === 'publish') {
            return $is_new_post ? 
                __('文章发布成功！', 'advanced-markdown-editor') : 
                __('文章更新成功！', 'advanced-markdown-editor');
        } else if ($status === 'private') {
            return $is_new_post ? 
                __('私有文章创建成功！', 'advanced-markdown-editor') : 
                __('私有文章更新成功！', 'advanced-markdown-editor');
        } else {
            return $is_new_post ? 
                __('草稿保存成功！', 'advanced-markdown-editor') : 
                __('草稿更新成功！', 'advanced-markdown-editor');
        }
    }
    
    /**
     * 获取Markdown文章内容
     */
    public function get_markdown_post() {
        // 验证nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'advanced_markdown_editor_nonce')) {
            wp_die(esc_html__('安全验证失败', 'advanced-markdown-editor'));
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $post = get_post($post_id);
        
        if (!$post || !current_user_can('edit_post', $post_id)) {
            wp_send_json_error(esc_html__('文章不存在或权限不足', 'advanced-markdown-editor'));
        }
        
        $markdown_content = get_post_meta($post_id, '_markdown_content', true);
        
        wp_send_json_success(array(
            'title' => $post->post_title,
            'markdown_content' => $markdown_content ?: $post->post_content,
            'status' => $post->post_status
        ));
    }
    
    /**
     * 创建新分类
     */
    public function create_new_category() {
        // 验证nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'advanced_markdown_editor_nonce')) {
            wp_die(esc_html__('安全验证失败', 'advanced-markdown-editor'));
        }
        
        // 检查权限
        if (!current_user_can('manage_categories')) {
            wp_send_json_error(esc_html__('您没有权限创建分类', 'advanced-markdown-editor'));
            return;
        }
        
        $category_name = isset($_POST['category_name']) ? sanitize_text_field(wp_unslash($_POST['category_name'])) : '';
        $category_slug = isset($_POST['category_slug']) ? sanitize_title(wp_unslash($_POST['category_slug'])) : '';
        $category_parent = isset($_POST['category_parent']) ? intval($_POST['category_parent']) : 0;
        $category_description = isset($_POST['category_description']) ? sanitize_textarea_field(wp_unslash($_POST['category_description'])) : '';
        
        // 验证分类名称
        if (empty($category_name)) {
            wp_send_json_error(__('分类名称不能为空', 'advanced-markdown-editor'));
            return;
        }
        
        // 检查分类名称是否已存在
        if (term_exists($category_name, 'category')) {
            wp_send_json_error(__('分类名称已存在', 'advanced-markdown-editor'));
            return;
        }
        
        // 验证父级分类
        if ($category_parent > 0) {
            if (!term_exists($category_parent, 'category')) {
                wp_send_json_error(__('父级分类不存在', 'advanced-markdown-editor'));
                return;
            }
        }
        
        // 如果没有提供别名，使用分类名称生成
        if (empty($category_slug)) {
            $category_slug = sanitize_title($category_name);
        }
        
        // 创建分类
        $term_result = wp_insert_term(
            $category_name,
            'category',
            array(
                'slug' => $category_slug,
                'parent' => $category_parent,
                'description' => $category_description
            )
        );
        
        if (is_wp_error($term_result)) {
            wp_send_json_error(__('分类创建失败: ', 'advanced-markdown-editor') . $term_result->get_error_message());
            return;
        }
        
        // 获取创建的分类信息
        $term = get_term($term_result['term_id'], 'category');
        
        wp_send_json_success(array(
            'term_id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'parent' => $term->parent,
            'description' => $term->description,
            'message' => __('分类创建成功', 'advanced-markdown-editor')
        ));
    }
    
    /**
     * 上传图片到媒体库
     */
    public function upload_image_for_markdown() {
        // 验证nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'advanced_markdown_editor_nonce')) {
            wp_die(esc_html__('安全验证失败', 'advanced-markdown-editor'));
        }
        
        // 检查权限
        if (!current_user_can('upload_files')) {
            wp_send_json_error(esc_html__('您没有权限上传文件', 'advanced-markdown-editor'));
            return;
        }
        
        // 检查是否有文件上传
        if (empty($_FILES['file'])) {
            wp_send_json_error(esc_html__('没有选择文件', 'advanced-markdown-editor'));
            return;
        }
        
        $file = isset($_FILES['file']) ? wp_unslash($_FILES['file']) : null;
        
        // 检查文件类型
        $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(__('不支持的文件类型', 'advanced-markdown-editor'));
            return;
        }
        
        // 检查文件大小 (默认最大5MB)
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            wp_send_json_error(__('文件大小超过限制', 'advanced-markdown-editor'));
            return;
        }
        
        // 引入WordPress文件处理函数
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        // 设置上传参数
        $upload_overrides = array(
            'test_form' => false,
            'unique_filename_callback' => array($this, 'unique_filename_callback')
        );
        
        // 处理文件上传
        $uploaded_file = wp_handle_upload($file, $upload_overrides);
        
        if (isset($uploaded_file['error'])) {
            wp_send_json_error($uploaded_file['error']);
            return;
        }
        
        // 获取文件信息
        $file_path = $uploaded_file['file'];
        $file_url = $uploaded_file['url'];
        $file_type = $uploaded_file['type'];
        
        // 创建附件
        $attachment = array(
            'guid' => $file_url,
            'post_mime_type' => $file_type,
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($file_path)),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        // 插入附件到数据库
        $attachment_id = wp_insert_attachment($attachment, $file_path);
        
        if (is_wp_error($attachment_id)) {
            wp_send_json_error(__('附件创建失败', 'advanced-markdown-editor'));
            return;
        }
        
        // 生成附件元数据
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        // 返回成功信息
        wp_send_json_success(array(
            'id' => $attachment_id,
            'url' => $file_url,
            'filename' => basename($file_path),
            'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
            'title' => get_the_title($attachment_id),
            'message' => __('图片上传成功', 'advanced-markdown-editor')
        ));
    }
    
    /**
     * 自定义文件名回调
     */
    public function unique_filename_callback($dir, $name, $ext) {
        // 添加时间戳和随机数确保文件名唯一
        $time = current_time('timestamp');
        $random = wp_rand(1000, 9999);
        return $time . '_' . $random . '_' . $name;
    }
    
    /**
     * 添加meta box
     */
    public function add_meta_boxes() {
        add_meta_box(
            'markdown-editor-meta',
            esc_html__('Markdown 编辑器', 'advanced-markdown-editor'),
            array($this, 'markdown_meta_box_callback'),
            array('post', 'page'),
            'side',
            'high'
        );
    }
    
    /**
     * Meta box回调
     */
    public function markdown_meta_box_callback($post) {
        $is_markdown = get_post_meta($post->ID, '_is_markdown_post', true);
        $edit_link = wp_nonce_url(admin_url('admin.php?page=wp-markdown-editor&post=' . $post->ID), 'edit_markdown_post_' . $post->ID);
        
        echo '<p>';
        if ($is_markdown) {
            echo '<strong>' . esc_html__('此文章使用Markdown编辑器创建', 'advanced-markdown-editor') . '</strong><br>';
        }
        echo '<a href="' . esc_url($edit_link) . '" class="button">' . esc_html__('用Markdown编辑器编辑', 'advanced-markdown-editor') . '</a>';
        echo '</p>';
    }
    
    /**
     * 保存文章meta数据
     */
    public function save_post_meta($post_id) {
        // 这里可以添加额外的meta数据保存逻辑
    }
    
    /**
     * 在文章列表页面添加Markdown编辑链接
     */
    public function add_markdown_edit_link($actions, $post) {
        if (current_user_can('edit_post', $post->ID)) {
            $markdown_edit_link = wp_nonce_url(admin_url('admin.php?page=wp-markdown-editor&post=' . $post->ID), 'edit_markdown_post_' . $post->ID);
            $actions['markdown_edit'] = '<a href="' . esc_url($markdown_edit_link) . '">' . esc_html__('Markdown编辑', 'advanced-markdown-editor') . '</a>';
        }
        return $actions;
    }
}

// 初始化插件
add_action('plugins_loaded', function() {
    ADVAMAED_Markdown_Editor::get_instance();
}); 