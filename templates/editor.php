<?php
/**
 * Markdown编辑器模板
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 获取文章ID
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is handled in the main plugin file
$post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is for page identification only
$current_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';

// 如果是新建文章页面，确保post_id为0
if ($current_page === 'wp-markdown-editor-new') {
    $post_id = 0;
}

$is_new_post = $post_id === 0;

// 获取文章数据
$post_data = array();
$post_title = '';
$post_content = '';
$post_status = 'draft';
$post_categories = array();
$post_tags = array();

if (!$is_new_post) {
    $post = get_post($post_id);
    if ($post) {
        $post_title = $post->post_title;
        $markdown_content = get_post_meta($post_id, '_markdown_content', true);
        
        // 如果没有Markdown内容，尝试从HTML内容中获取
        if (empty($markdown_content)) {
            $post_content = $post->post_content;
        } else {
            $post_content = $markdown_content;
        }
        
        $post_status = $post->post_status;
        
        // 获取分类
        $categories = wp_get_post_categories($post_id);
        $post_categories = $categories;
        
        // 获取标签
        $tags = wp_get_post_tags($post_id);
        $post_tags = array_map(function($tag) {
            return $tag->term_id;
        }, $tags);
    }
}

// 获取所有可用的分类
$categories = get_categories(array(
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
));

// 获取所有可用的标签
$tags = get_tags(array(
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
));
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php if ($is_new_post): ?>
            <?php esc_html_e('新建Markdown文章', 'advanced-markdown-editor'); ?>
        <?php else: ?>
            <?php esc_html_e('编辑Markdown文章', 'advanced-markdown-editor'); ?>
        <?php endif; ?>
    </h1>
    
    <?php if (!$is_new_post): ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-markdown-editor-new')); ?>" class="page-title-action">
        <?php esc_html_e('新建文章', 'advanced-markdown-editor'); ?>
    </a>
    <?php endif; ?>
    
    <hr class="wp-header-end">
    
    <div id="markdown-editor-container">
        <!-- 隐藏字段用于存储文章ID -->
        <input type="hidden" id="post-id" value="<?php echo $is_new_post ? '' : esc_attr($post_id); ?>">
        
        <div class="editor-header<?php echo (($post_status === 'publish' || $post_status === 'private') && !$is_new_post) ? ' published' : ''; ?>">
            <div class="title-section">
                <input type="text" 
                       id="post-title" 
                       name="post_title" 
                       value="<?php echo esc_attr($post_title); ?>" 
                       placeholder="<?php esc_attr_e('在此输入标题', 'advanced-markdown-editor'); ?>"
                       class="widefat">
            </div>
            
            <div class="editor-actions">
                <div class="status-section">
                    <label for="post-status"><?php esc_html_e('状态', 'advanced-markdown-editor'); ?></label>
                    <select id="post-status" name="post_status">
                        <option value="draft" <?php selected($post_status, 'draft'); ?>><?php esc_html_e('草稿', 'advanced-markdown-editor'); ?></option>
                        <option value="publish" <?php selected($post_status, 'publish'); ?>><?php esc_html_e('发布', 'advanced-markdown-editor'); ?></option>
                        <option value="private" <?php selected($post_status, 'private'); ?>><?php esc_html_e('私有', 'advanced-markdown-editor'); ?></option>
                    </select>
                </div>
                
                <div class="save-section">
                    <button type="button" id="save-draft" class="button">
                        <?php esc_html_e('保存草稿', 'advanced-markdown-editor'); ?>
                    </button>
                    <button type="button" id="publish-post" class="button button-primary">
                        <?php echo (($post_status === 'publish' || $post_status === 'private') && !$is_new_post) ? esc_html__('更新', 'advanced-markdown-editor') : esc_html__('发布', 'advanced-markdown-editor'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="editor-main">
            <!-- 主编辑区 -->
            <div class="editor-main-content">
                <div class="editor-tabs">
                    <button type="button" class="tab-button active" data-tab="write">
                        <?php esc_html_e('编写', 'advanced-markdown-editor'); ?>
                    </button>
                    <button type="button" class="tab-button" data-tab="preview">
                        <?php esc_html_e('预览', 'advanced-markdown-editor'); ?>
                    </button>
                    <button type="button" class="tab-button" data-tab="split">
                        <?php esc_html_e('分屏', 'advanced-markdown-editor'); ?>
                    </button>
                </div>
                
                <div class="editor-content">
                    <div class="editor-pane write-pane active">
                        <div class="toolbar">
                            <button type="button" class="toolbar-button" data-action="bold" title="<?php esc_html_e('粗体', 'advanced-markdown-editor'); ?>">
                                <span class="dashicons dashicons-editor-bold"></span>
                            </button>
                            <button type="button" class="toolbar-button" data-action="italic" title="<?php esc_html_e('斜体', 'advanced-markdown-editor'); ?>">
                                <span class="dashicons dashicons-editor-italic"></span>
                            </button>
                            <button type="button" class="toolbar-button" data-action="heading" title="<?php esc_html_e('标题', 'advanced-markdown-editor'); ?>">
                                <span class="dashicons dashicons-heading"></span>
                            </button>
                            <button type="button" class="toolbar-button" data-action="link" title="<?php esc_html_e('链接', 'advanced-markdown-editor'); ?>">
                                <span class="dashicons dashicons-admin-links"></span>
                            </button>
                            <button type="button" class="toolbar-button" data-action="media" title="<?php esc_html_e('上传图片', 'advanced-markdown-editor'); ?>">
                                <span class="dashicons dashicons-admin-media"></span>
                            </button>
                            <button type="button" class="toolbar-button" data-action="image" title="<?php esc_html_e('插入图片链接', 'advanced-markdown-editor'); ?>">
                                <span class="dashicons dashicons-format-image"></span>
                            </button>
                            <button type="button" class="toolbar-button" data-action="code" title="<?php esc_html_e('代码', 'advanced-markdown-editor'); ?>">
                                <span class="dashicons dashicons-editor-code"></span>
                            </button>
                            <button type="button" class="toolbar-button" data-action="quote" title="<?php esc_html_e('引用', 'advanced-markdown-editor'); ?>">
                                <span class="dashicons dashicons-editor-quote"></span>
                            </button>
                            <button type="button" class="toolbar-button" data-action="list" title="<?php esc_html_e('列表', 'advanced-markdown-editor'); ?>">
                                <span class="dashicons dashicons-editor-ul"></span>
                            </button>
                        </div>
                        
                        <textarea id="markdown-editor" 
                                  name="markdown_content" 
                                  placeholder="<?php esc_html_e('在此输入Markdown内容...', 'advanced-markdown-editor'); ?>"><?php echo esc_textarea($post_content); ?></textarea>
                    </div>
                    
                    <div class="editor-pane preview-pane">
                        <div class="preview-content" id="preview-content">
                            <p class="no-content"><?php esc_html_e('预览将在这里显示...', 'advanced-markdown-editor'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="editor-footer">
                    <div class="save-status">
                        <span id="save-status"></span>
                    </div>
                    <div class="editor-info">
                        <span id="word-count">0 <?php esc_html_e('字', 'advanced-markdown-editor'); ?></span>
                        <span class="separator">|</span>
                        <span id="line-count">0 <?php esc_html_e('行', 'advanced-markdown-editor'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- 右侧边栏 -->
            <div class="editor-sidebar">
                <!-- 发布状态卡片 -->
                <div class="sidebar-card">
                    <div class="card-header">
                        <h3><?php esc_html_e('发布', 'advanced-markdown-editor'); ?></h3>
                    </div>
                    <div class="card-content">
                        <div class="publish-actions">
                            <button type="button" id="save-draft-sidebar" class="button button-large">
                                <?php esc_html_e('保存草稿', 'advanced-markdown-editor'); ?>
                            </button>
                            <button type="button" id="publish-post-sidebar" class="button button-primary button-large">
                                <?php echo (($post_status === 'publish' || $post_status === 'private') && !$is_new_post) ? esc_html__('更新', 'advanced-markdown-editor') : esc_html__('发布', 'advanced-markdown-editor'); ?>
                            </button>
                        </div>
                        <div class="publish-info">
                            <div class="info-item">
                                <label for="post-status-sidebar"><?php esc_html_e('状态', 'advanced-markdown-editor'); ?></label>
                                <select id="post-status-sidebar" name="post_status">
                                    <option value="draft" <?php selected($post_status, 'draft'); ?>><?php esc_html_e('草稿', 'advanced-markdown-editor'); ?></option>
                                    <option value="publish" <?php selected($post_status, 'publish'); ?>><?php esc_html_e('发布', 'advanced-markdown-editor'); ?></option>
                                    <option value="private" <?php selected($post_status, 'private'); ?>><?php esc_html_e('私有', 'advanced-markdown-editor'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 分类卡片 -->
                <div class="sidebar-card">
                    <div class="card-header">
                        <h3><?php esc_html_e('分类', 'advanced-markdown-editor'); ?></h3>
                        <button type="button" class="button button-small" id="add-new-category">
                            <?php esc_html_e('新建', 'advanced-markdown-editor'); ?>
                        </button>
                    </div>
                    <div class="card-content">
                        <div class="categories-tree">
                            <?php if (!empty($categories)): ?>
                                <?php
                                // 构建分类树
                                function advamaed_build_category_tree($categories, $parent_id = 0, $post_categories = array(), $level = 0) {
                                    $tree = '';
                                    foreach ($categories as $category) {
                                        if ($category->parent == $parent_id) {
                                            $checked = in_array($category->term_id, $post_categories) ? 'checked' : '';
                                            $tree .= '<div class="category-tree-item" data-level="' . esc_attr($level) . '">';
                                            $tree .= '<label class="category-label">';
                                            $tree .= '<input type="checkbox" name="post_categories[]" value="' . esc_attr($category->term_id) . '" ' . $checked . '>';
                                            $tree .= '<span class="category-name">' . esc_html($category->name) . '</span>';
                                            $tree .= '</label>';
                                            
                                            // 递归显示子分类
                                            $children = advamaed_build_category_tree($categories, $category->term_id, $post_categories, $level + 1);
                                            if ($children) {
                                                $tree .= '<div class="category-children">' . $children . '</div>';
                                            }
                                            
                                            $tree .= '</div>';
                                        }
                                    }
                                    return $tree;
                                }
                                
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                echo advamaed_build_category_tree($categories, 0, $post_categories);
                                ?>
                            <?php else: ?>
                                <p class="no-items"><?php esc_html_e('暂无分类', 'advanced-markdown-editor'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- 标签卡片 -->
                <div class="sidebar-card">
                    <div class="card-header">
                        <h3><?php esc_html_e('标签', 'advanced-markdown-editor'); ?></h3>
                    </div>
                    <div class="card-content">
                        <div class="tags-input-wrapper">
                            <textarea id="post-tags" 
                                     name="post_tags" 
                                     rows="3"
                                     placeholder="<?php esc_html_e('输入标签，用逗号分隔', 'advanced-markdown-editor'); ?>"><?php 
                                if (!empty($post_tags)) {
                                    $tag_names = array();
                                    foreach ($post_tags as $tag_id) {
                                        $tag = get_tag($tag_id);
                                        if ($tag) {
                                            $tag_names[] = $tag->name;
                                        }
                                    }
                                    echo esc_textarea(implode(', ', $tag_names));
                                }
                            ?></textarea>
                            <div class="tags-suggestions" id="tags-suggestions" style="display: none;"></div>
                        </div>
                        <p class="description"><?php esc_html_e('多个标签用逗号分隔', 'advanced-markdown-editor'); ?></p>
                        
                        <!-- 常用标签快速选择 -->
                        <?php if (!empty($tags)): ?>
                        <div class="popular-tags">
                            <label class="tags-label"><?php esc_html_e('常用标签:', 'advanced-markdown-editor'); ?></label>
                            <div class="tags-cloud">
                                <?php foreach (array_slice($tags, 0, 10) as $tag): ?>
                                    <button type="button" class="tag-cloud-item" data-tag="<?php echo esc_attr($tag->name); ?>">
                                        <?php echo esc_html($tag->name); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- 图片管理卡片 -->
                <div class="sidebar-card">
                    <div class="card-header">
                        <h3><?php esc_html_e('图片', 'advanced-markdown-editor'); ?></h3>
                        <button type="button" class="button button-small" id="open-media-library">
                            <?php esc_html_e('媒体库', 'advanced-markdown-editor'); ?>
                        </button>
                    </div>
                    <div class="card-content">
                        <div class="image-actions">
                            <button type="button" class="button button-small button-full" id="upload-image">
                                <span class="dashicons dashicons-upload"></span>
                                <?php esc_html_e('上传图片', 'advanced-markdown-editor'); ?>
                            </button>
                            <button type="button" class="button button-small button-full" id="insert-image-url">
                                <span class="dashicons dashicons-admin-links"></span>
                                <?php esc_html_e('插入图片链接', 'advanced-markdown-editor'); ?>
                            </button>
                        </div>
                        <div class="image-tips">
                            <p class="description">
                                <?php esc_html_e('支持拖拽图片到编辑器区域快速插入', 'advanced-markdown-editor'); ?>
                            </p>
                            <p class="description">
                                <strong><?php esc_html_e('快捷键', 'advanced-markdown-editor'); ?></strong>
                                Ctrl+U <?php esc_html_e('上传图片', 'advanced-markdown-editor'); ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- 统计信息卡片 -->
                <div class="sidebar-card">
                    <div class="card-header">
                        <h3><?php esc_html_e('统计信息', 'advanced-markdown-editor'); ?></h3>
                    </div>
                    <div class="card-content">
                        <div class="stats-grid">
                            <div class="stat-item">
                                <span class="stat-label"><?php esc_html_e('字数', 'advanced-markdown-editor'); ?></span>
                                <span class="stat-value" id="word-count-sidebar">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label"><?php esc_html_e('行数', 'advanced-markdown-editor'); ?></span>
                                <span class="stat-value" id="line-count-sidebar">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label"><?php esc_html_e('字符', 'advanced-markdown-editor'); ?></span>
                                <span class="stat-value" id="char-count-sidebar">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label"><?php esc_html_e('段落', 'advanced-markdown-editor'); ?></span>
                                <span class="stat-value" id="paragraph-count-sidebar">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 隐藏字段 -->
    <input type="hidden" id="editor-nonce" value="<?php echo esc_attr(wp_create_nonce('advanced_markdown_editor_nonce')); ?>">
    
    <!-- 传递标签数据给JavaScript -->
    <script type="text/javascript">
        var availableTags = <?php echo wp_json_encode(array_map(function($tag) {
            return $tag->name;
        }, $tags)); ?>;
    </script>
</div>

<!-- 新建分类模态框 -->
<div id="new-category-modal" class="markdown-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php esc_html_e('新建分类', 'advanced-markdown-editor'); ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="new-category-form">
                <div class="form-group">
                    <label for="category-name"><?php esc_html_e('分类名称', 'advanced-markdown-editor'); ?></label>
                    <input type="text" id="category-name" name="category_name" class="regular-text" required>
                </div>
                <div class="form-group">
                    <label for="category-slug"><?php esc_html_e('别名', 'advanced-markdown-editor'); ?></label>
                    <input type="text" id="category-slug" name="category_slug" class="regular-text">
                    <p class="description"><?php esc_html_e('别名是在URL中使用的版本，通常为小写，只包含字母、数字和连字符。', 'advanced-markdown-editor'); ?></p>
                </div>
                <div class="form-group">
                    <label for="category-parent"><?php esc_html_e('父级分类', 'advanced-markdown-editor'); ?></label>
                    <select id="category-parent" name="category_parent">
                        <option value="0"><?php esc_html_e('无（顶级分类）', 'advanced-markdown-editor'); ?></option>
                        <?php
                        $modal_categories = get_categories(array('hide_empty' => false));
                        foreach ($modal_categories as $category) {
                            echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
                        }
                        ?>
                    </select>
                    <p class="description"><?php esc_html_e('选择父级分类，留空为顶级分类。', 'advanced-markdown-editor'); ?></p>
                </div>
                <div class="form-group">
                    <label for="category-description"><?php esc_html_e('描述', 'advanced-markdown-editor'); ?></label>
                    <textarea id="category-description" name="category_description" rows="3" class="large-text"></textarea>
                </div>
                <p class="submit">
                    <button type="submit" class="button button-primary"><?php esc_html_e('添加分类', 'advanced-markdown-editor'); ?></button>
                    <button type="button" class="button modal-close"><?php esc_html_e('取消', 'advanced-markdown-editor'); ?></button>
                </p>
            </form>
        </div>
    </div>
</div>

<!-- 帮助模态框 -->
<div id="markdown-help-modal" class="markdown-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php esc_html_e('Markdown语法帮助', 'advanced-markdown-editor'); ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="help-content">
                <h4><?php esc_html_e('基本语法', 'advanced-markdown-editor'); ?></h4>
                <table class="help-table">
                    <tr>
                        <td><code># 标题 1</code></td>
                        <td><?php esc_html_e('一级标题', 'advanced-markdown-editor'); ?></td>
                    </tr>
                    <tr>
                        <td><code>## 标题 2</code></td>
                        <td><?php esc_html_e('二级标题', 'advanced-markdown-editor'); ?></td>
                    </tr>
                    <tr>
                        <td><code>**粗体**</code></td>
                        <td><strong><?php esc_html_e('粗体文本', 'advanced-markdown-editor'); ?></strong></td>
                    </tr>
                    <tr>
                        <td><code>*斜体*</code></td>
                        <td><em><?php esc_html_e('斜体文本', 'advanced-markdown-editor'); ?></em></td>
                    </tr>
                    <tr>
                        <td><code>[链接](http://example.com)</code></td>
                        <td><?php esc_html_e('创建链接', 'advanced-markdown-editor'); ?></td>
                    </tr>
                    <tr>
                        <td><code>![图片](image.jpg)</code></td>
                        <td><?php esc_html_e('插入图片', 'advanced-markdown-editor'); ?></td>
                    </tr>
                    <tr>
                        <td><code>`代码`</code></td>
                        <td><code><?php esc_html_e('内联代码', 'advanced-markdown-editor'); ?></code></td>
                    </tr>
                    <tr>
                        <td><code>> 引用</code></td>
                        <td><?php esc_html_e('块引用', 'advanced-markdown-editor'); ?></td>
                    </tr>
                    <tr>
                        <td><code>- 列表</code></td>
                        <td><?php esc_html_e('无序列表', 'advanced-markdown-editor'); ?></td>
                    </tr>
                    <tr>
                        <td><code>1. 列表</code></td>
                        <td><?php esc_html_e('有序列表', 'advanced-markdown-editor'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div> 
