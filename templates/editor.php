<?php
/**
 * Markdown编辑器模板
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

$post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
$is_new_post = !$post_id;
$post_title = '';
$markdown_content = '';
$post_status = 'draft';

if (!$is_new_post) {
    $post = get_post($post_id);
    $post_title = $post->post_title;
    $markdown_content = get_post_meta($post_id, '_markdown_content', true);
    if (!$markdown_content) {
        // 如果没有Markdown内容，尝试从HTML内容中获取
        $markdown_content = $post->post_content;
    }
    $post_status = $post->post_status;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php if ($is_new_post): ?>
            <?php _e('新建Markdown文章', 'wp-markdown-editor'); ?>
        <?php else: ?>
            <?php _e('编辑Markdown文章', 'wp-markdown-editor'); ?>
        <?php endif; ?>
    </h1>
    
    <?php if (!$is_new_post): ?>
    <a href="<?php echo admin_url('admin.php?page=wp-markdown-editor'); ?>" class="page-title-action">
        <?php _e('新建文章', 'wp-markdown-editor'); ?>
    </a>
    <?php endif; ?>
    
    <hr class="wp-header-end">
    
    <div id="markdown-editor-container">
        <div class="editor-header">
            <div class="title-section">
                <input type="text" 
                       id="post-title" 
                       name="post_title" 
                       value="<?php echo esc_attr($post_title); ?>" 
                       placeholder="<?php _e('在此输入标题', 'wp-markdown-editor'); ?>"
                       class="widefat">
            </div>
            
            <div class="editor-actions">
                <div class="status-section">
                    <label for="post-status"><?php _e('状态:', 'wp-markdown-editor'); ?></label>
                    <select id="post-status" name="post_status">
                        <option value="draft" <?php selected($post_status, 'draft'); ?>><?php _e('草稿', 'wp-markdown-editor'); ?></option>
                        <option value="publish" <?php selected($post_status, 'publish'); ?>><?php _e('发布', 'wp-markdown-editor'); ?></option>
                        <option value="private" <?php selected($post_status, 'private'); ?>><?php _e('私有', 'wp-markdown-editor'); ?></option>
                    </select>
                </div>
                
                <div class="save-section">
                    <button type="button" id="save-draft" class="button">
                        <?php _e('保存草稿', 'wp-markdown-editor'); ?>
                    </button>
                    <button type="button" id="publish-post" class="button button-primary">
                        <?php _e('发布', 'wp-markdown-editor'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="editor-tabs">
            <button type="button" class="tab-button active" data-tab="write">
                <?php _e('编写', 'wp-markdown-editor'); ?>
            </button>
            <button type="button" class="tab-button" data-tab="preview">
                <?php _e('预览', 'wp-markdown-editor'); ?>
            </button>
            <button type="button" class="tab-button" data-tab="split">
                <?php _e('分屏', 'wp-markdown-editor'); ?>
            </button>
        </div>
        
        <div class="editor-content">
            <div class="editor-pane write-pane active">
                <div class="toolbar">
                    <button type="button" class="toolbar-button" data-action="bold" title="<?php _e('粗体', 'wp-markdown-editor'); ?>">
                        <span class="dashicons dashicons-editor-bold"></span>
                    </button>
                    <button type="button" class="toolbar-button" data-action="italic" title="<?php _e('斜体', 'wp-markdown-editor'); ?>">
                        <span class="dashicons dashicons-editor-italic"></span>
                    </button>
                    <button type="button" class="toolbar-button" data-action="heading" title="<?php _e('标题', 'wp-markdown-editor'); ?>">
                        <span class="dashicons dashicons-heading"></span>
                    </button>
                    <button type="button" class="toolbar-button" data-action="link" title="<?php _e('链接', 'wp-markdown-editor'); ?>">
                        <span class="dashicons dashicons-admin-links"></span>
                    </button>
                    <button type="button" class="toolbar-button" data-action="image" title="<?php _e('图片', 'wp-markdown-editor'); ?>">
                        <span class="dashicons dashicons-format-image"></span>
                    </button>
                    <button type="button" class="toolbar-button" data-action="code" title="<?php _e('代码', 'wp-markdown-editor'); ?>">
                        <span class="dashicons dashicons-editor-code"></span>
                    </button>
                    <button type="button" class="toolbar-button" data-action="quote" title="<?php _e('引用', 'wp-markdown-editor'); ?>">
                        <span class="dashicons dashicons-editor-quote"></span>
                    </button>
                    <button type="button" class="toolbar-button" data-action="list" title="<?php _e('列表', 'wp-markdown-editor'); ?>">
                        <span class="dashicons dashicons-editor-ul"></span>
                    </button>
                </div>
                
                <textarea id="markdown-editor" 
                          name="markdown_content" 
                          placeholder="<?php _e('在此输入Markdown内容...', 'wp-markdown-editor'); ?>"><?php echo esc_textarea($markdown_content); ?></textarea>
            </div>
            
            <div class="editor-pane preview-pane">
                <div class="preview-content" id="preview-content">
                    <p class="no-content"><?php _e('预览将在这里显示...', 'wp-markdown-editor'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="editor-footer">
            <div class="save-status">
                <span id="save-status"></span>
            </div>
            <div class="editor-info">
                <span id="word-count">0 <?php _e('字', 'wp-markdown-editor'); ?></span>
                <span class="separator">|</span>
                <span id="line-count">0 <?php _e('行', 'wp-markdown-editor'); ?></span>
            </div>
        </div>
    </div>
    
    <!-- 隐藏字段 -->
    <input type="hidden" id="post-id" value="<?php echo $post_id; ?>">
    <input type="hidden" id="editor-nonce" value="<?php echo wp_create_nonce('wp_markdown_editor_nonce'); ?>">
</div>

<!-- 帮助模态框 -->
<div id="markdown-help-modal" class="markdown-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('Markdown语法帮助', 'wp-markdown-editor'); ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="help-content">
                <h4><?php _e('基本语法', 'wp-markdown-editor'); ?></h4>
                <table class="help-table">
                    <tr>
                        <td><code># 标题 1</code></td>
                        <td><?php _e('一级标题', 'wp-markdown-editor'); ?></td>
                    </tr>
                    <tr>
                        <td><code>## 标题 2</code></td>
                        <td><?php _e('二级标题', 'wp-markdown-editor'); ?></td>
                    </tr>
                    <tr>
                        <td><code>**粗体**</code></td>
                        <td><strong><?php _e('粗体文本', 'wp-markdown-editor'); ?></strong></td>
                    </tr>
                    <tr>
                        <td><code>*斜体*</code></td>
                        <td><em><?php _e('斜体文本', 'wp-markdown-editor'); ?></em></td>
                    </tr>
                    <tr>
                        <td><code>[链接](http://example.com)</code></td>
                        <td><?php _e('创建链接', 'wp-markdown-editor'); ?></td>
                    </tr>
                    <tr>
                        <td><code>![图片](image.jpg)</code></td>
                        <td><?php _e('插入图片', 'wp-markdown-editor'); ?></td>
                    </tr>
                    <tr>
                        <td><code>`代码`</code></td>
                        <td><code><?php _e('内联代码', 'wp-markdown-editor'); ?></code></td>
                    </tr>
                    <tr>
                        <td><code>> 引用</code></td>
                        <td><?php _e('块引用', 'wp-markdown-editor'); ?></td>
                    </tr>
                    <tr>
                        <td><code>- 列表项</code></td>
                        <td><?php _e('无序列表', 'wp-markdown-editor'); ?></td>
                    </tr>
                    <tr>
                        <td><code>1. 列表项</code></td>
                        <td><?php _e('有序列表', 'wp-markdown-editor'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div> 