<?php
/**
 * Plugin Name: WordPress Markdown 编辑器
 * Plugin URI: https://github.com/your-username/wp-markdown-editor
 * Description: 为WordPress添加独立的Markdown编辑器，支持文章的Markdown编辑和发布，同时保持与原有编辑器的兼容性。
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: wp-markdown-editor
 * Domain Path: /languages
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('WP_MARKDOWN_EDITOR_VERSION', '1.0.0');
define('WP_MARKDOWN_EDITOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_MARKDOWN_EDITOR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_MARKDOWN_EDITOR_PLUGIN_BASENAME', plugin_basename(__FILE__));

// 主插件类
class WP_Markdown_Editor {
    
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
        add_action('wp_ajax_save_markdown_post', array($this, 'save_markdown_post'));
        add_action('wp_ajax_get_markdown_post', array($this, 'get_markdown_post'));
        
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
        add_option('wp_markdown_editor_version', WP_MARKDOWN_EDITOR_VERSION);
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
        // 加载文本域
        load_plugin_textdomain('wp-markdown-editor', false, dirname(plugin_basename(__FILE__)) . '/languages');
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
            __('Markdown 编辑器', 'wp-markdown-editor'),
            __('Markdown 编辑器', 'wp-markdown-editor'),
            'edit_posts',
            'wp-markdown-editor',
            array($this, 'markdown_editor_page'),
            'dashicons-edit',
            6  // 推荐位置：在"文章"菜单之后，因为这是编辑相关的功能
        );
        
        // 添加子菜单项
        add_submenu_page(
            'wp-markdown-editor',
            __('新建文章', 'wp-markdown-editor'),
            __('新建文章', 'wp-markdown-editor'),
            'edit_posts',
            'wp-markdown-editor-new',
            array($this, 'markdown_editor_page')
        );
        
        add_submenu_page(
            'wp-markdown-editor',
            __('文章列表', 'wp-markdown-editor'),
            __('文章列表', 'wp-markdown-editor'),
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
            // 加载样式
            wp_enqueue_style(
                'wp-markdown-editor-admin',
                WP_MARKDOWN_EDITOR_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                WP_MARKDOWN_EDITOR_VERSION
            );
            
            // 加载脚本
            wp_enqueue_script(
                'wp-markdown-editor-admin',
                WP_MARKDOWN_EDITOR_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                WP_MARKDOWN_EDITOR_VERSION,
                true
            );
            
            // 加载 Marked.js 用于Markdown解析
            wp_enqueue_script(
                'marked-js',
                'https://cdn.jsdelivr.net/npm/marked/marked.min.js',
                array(),
                '4.3.0',
                true
            );
            
            // 传递AJAX URL和nonce
            wp_localize_script('wp-markdown-editor-admin', 'wpMarkdownEditor', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_markdown_editor_nonce'),
                'strings' => array(
                    'saving' => __('保存中...', 'wp-markdown-editor'),
                    'saved' => __('已保存', 'wp-markdown-editor'),
                    'error' => __('保存失败', 'wp-markdown-editor'),
                )
            ));
        }
    }
    
    /**
     * Markdown编辑器页面
     */
    public function markdown_editor_page() {
        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        $post = null;
        
        if ($post_id) {
            $post = get_post($post_id);
            if (!$post || !current_user_can('edit_post', $post_id)) {
                wp_die(__('您没有权限编辑此文章。', 'wp-markdown-editor'));
            }
        }
        
        include WP_MARKDOWN_EDITOR_PLUGIN_DIR . 'templates/editor.php';
    }
    
    /**
     * 保存Markdown文章
     */
    public function save_markdown_post() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wp_markdown_editor_nonce')) {
            wp_die(__('安全验证失败', 'wp-markdown-editor'));
        }
        
        // 检查权限
        if (!current_user_can('edit_posts')) {
            wp_die(__('权限不足', 'wp-markdown-editor'));
        }
        
        $post_id = intval($_POST['post_id']);
        $title = sanitize_text_field($_POST['title']);
        $markdown_content = wp_kses_post($_POST['markdown_content']);
        $html_content = wp_kses_post($_POST['html_content']);
        $status = sanitize_text_field($_POST['status']);
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $html_content,
            'post_status' => $status,
            'post_type' => 'post'
        );
        
        if ($post_id) {
            $post_data['ID'] = $post_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
        }
        
        if ($result && !is_wp_error($result)) {
            // 保存Markdown内容到meta
            update_post_meta($result, '_markdown_content', $markdown_content);
            update_post_meta($result, '_is_markdown_post', true);
            
            wp_send_json_success(array(
                'post_id' => $result,
                'message' => __('文章保存成功', 'wp-markdown-editor')
            ));
        } else {
            wp_send_json_error(__('文章保存失败', 'wp-markdown-editor'));
        }
    }
    
    /**
     * 获取Markdown文章内容
     */
    public function get_markdown_post() {
        // 验证nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wp_markdown_editor_nonce')) {
            wp_die(__('安全验证失败', 'wp-markdown-editor'));
        }
        
        $post_id = intval($_POST['post_id']);
        $post = get_post($post_id);
        
        if (!$post || !current_user_can('edit_post', $post_id)) {
            wp_send_json_error(__('文章不存在或权限不足', 'wp-markdown-editor'));
        }
        
        $markdown_content = get_post_meta($post_id, '_markdown_content', true);
        
        wp_send_json_success(array(
            'title' => $post->post_title,
            'markdown_content' => $markdown_content ?: $post->post_content,
            'status' => $post->post_status
        ));
    }
    
    /**
     * 添加meta box
     */
    public function add_meta_boxes() {
        add_meta_box(
            'markdown-editor-meta',
            __('Markdown 编辑器', 'wp-markdown-editor'),
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
        $edit_link = admin_url('admin.php?page=wp-markdown-editor&post=' . $post->ID);
        
        echo '<p>';
        if ($is_markdown) {
            echo '<strong>' . __('此文章使用Markdown编辑器创建', 'wp-markdown-editor') . '</strong><br>';
        }
        echo '<a href="' . esc_url($edit_link) . '" class="button">' . __('用Markdown编辑器编辑', 'wp-markdown-editor') . '</a>';
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
            $markdown_edit_link = admin_url('admin.php?page=wp-markdown-editor&post=' . $post->ID);
            $actions['markdown_edit'] = '<a href="' . esc_url($markdown_edit_link) . '">' . __('Markdown编辑', 'wp-markdown-editor') . '</a>';
        }
        return $actions;
    }
}

// 初始化插件
add_action('plugins_loaded', function() {
    WP_Markdown_Editor::get_instance();
}); 