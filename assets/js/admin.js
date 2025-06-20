/**
 * Advanced Markdown Editor - Admin JavaScript
 */

(function($) {
    'use strict';

    // 编辑器对象
    const MarkdownEditor = {
        
        // 初始化
        init: function() {
            this.bindEvents();
            this.setupEditor();
            this.loadPost();
            this.setupAutoSave();
            this.setupTagsAutocomplete();
            this.setupMediaUpload();
            this.createNotificationContainer();
            this.initializeButtonStates();
        },

        // 初始化按钮状态
        initializeButtonStates: function() {
            // 检查当前页面的文章状态
            const postId = $('#post-id').val();
            const currentStatus = $('#post-status').val();
            
            if (postId && currentStatus) {
                this.updateButtonsAfterLoad(currentStatus);
            }
        },

        // 创建通知容器
        createNotificationContainer: function() {
            if (!$('#markdown-editor-notifications').length) {
                $('body').append('<div id="markdown-editor-notifications" class="markdown-notifications"></div>');
            }
        },

        // 显示通知
        showNotification: function(message, type = 'info', duration = 3000) {
            const $container = $('#markdown-editor-notifications');
            const notificationId = 'notification-' + Date.now();
            const self = this;
            const $notification = $(`
                <div id="${notificationId}" class="markdown-notification ${type}">
                    <span class="notification-message">${message}</span>
                    <button class="notification-close" data-notification-id="${notificationId}">&times;</button>
                </div>
            `);
            
            $container.append($notification);
            
            // 绑定关闭事件
            $notification.find('.notification-close').on('click', function() {
                self.closeNotification($(this).data('notification-id'));
            });
            
            // 自动关闭
            if (duration > 0) {
                setTimeout(() => {
                    self.closeNotification(notificationId);
                }, duration);
            }
        },

        // 关闭通知
        closeNotification: function(notificationId) {
            const $notification = $('#' + notificationId);
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        },

        // 显示输入对话框
        showInputDialog: function(title, placeholder, defaultValue = '', callback) {
            const dialogId = 'input-dialog-' + Date.now();
            const $dialog = $(`
                <div id="${dialogId}" class="markdown-modal input-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>${title}</h3>
                            <button class="modal-close">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="text" class="dialog-input" placeholder="${placeholder}" value="${defaultValue}">
                        </div>
                        <div class="modal-footer">
                            <button class="button dialog-cancel">取消</button>
                            <button class="button button-primary dialog-confirm">确定</button>
                        </div>
                    </div>
                </div>
            `);
            
            $('body').append($dialog);
            
            const $input = $dialog.find('.dialog-input');
            $input.focus().select();
            
            // 绑定事件
            $dialog.find('.dialog-confirm').on('click', function() {
                const value = $input.val().trim();
                if (value) {
                    callback(value);
                }
                $dialog.remove();
            });
            
            $dialog.find('.dialog-cancel, .modal-close').on('click', function() {
                $dialog.remove();
            });
            
            // 回车确认
            $input.on('keypress', function(e) {
                if (e.which === 13) {
                    $dialog.find('.dialog-confirm').click();
                }
            });
            
            // ESC取消
            $dialog.on('keydown', function(e) {
                if (e.which === 27) {
                    $dialog.remove();
                }
            });
        },

        // 绑定事件
        bindEvents: function() {
            const self = this;

            // 标签页切换
            $('.tab-button').on('click', function() {
                const tab = $(this).data('tab');
                self.switchTab(tab);
            });

            // 工具栏按钮
            $('.toolbar-button').on('click', function() {
                const action = $(this).data('action');
                self.handleToolbarAction(action);
            });

            // 编辑器输入事件
            $('#markdown-editor').on('input', function() {
                self.updatePreview();
                self.updateCounts();
                self.markAsChanged();
            });

            // 标题输入事件
            $('#post-title').on('input', function() {
                self.markAsChanged();
            });

            // 状态改变事件 (支持头部和侧边栏两个选择器)
            $('#post-status, #post-status-sidebar').on('change', function() {
                const status = $(this).val();
                // 同步两个状态选择器
                $('#post-status, #post-status-sidebar').val(status);
                self.markAsChanged();
            });

            // 分类选择事件
            $('input[name="post_categories[]"]').on('change', function() {
                self.markAsChanged();
            });

            // 标签输入事件
            $('#post-tags').on('input', function() {
                self.markAsChanged();
                self.showTagSuggestions($(this).val());
            });

            // 常用标签快速选择
            $(document).on('click', '.tag-cloud-item', function() {
                const tagName = $(this).data('tag');
                self.addQuickTag(tagName);
            });

            // 图片管理按钮
            $('#open-media-library, #upload-image').on('click', function() {
                self.openMediaLibrary();
            });

            $('#insert-image-url').on('click', function() {
                self.handleToolbarAction('image');
            });

            // 保存按钮 (支持头部和侧边栏按钮)
            $('#save-draft, #save-draft-sidebar').on('click', function() {
                self.savePost('draft');
            });

            $('#publish-post, #publish-post-sidebar').on('click', function() {
                const currentStatus = $('#post-status').val();
                const newStatus = currentStatus === 'publish' ? 'publish' : 'publish';
                self.savePost(newStatus);
            });

            // 新建分类按钮
            $('#add-new-category').on('click', function() {
                $('#new-category-modal').show();
                $('#new-category-name').focus();
            });

            // 新建分类表单提交
            $('#new-category-form').on('submit', function(e) {
                e.preventDefault();
                self.createNewCategory();
            });

            // 键盘快捷键
            $(document).on('keydown', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    switch(e.which) {
                        case 83: // Ctrl+S
                            e.preventDefault();
                            self.savePost();
                            break;
                        case 66: // Ctrl+B
                            e.preventDefault();
                            self.handleToolbarAction('bold');
                            break;
                        case 73: // Ctrl+I
                            e.preventDefault();
                            self.handleToolbarAction('italic');
                            break;
                        case 85: // Ctrl+U
                            e.preventDefault();
                            self.openMediaLibrary();
                            break;
                    }
                }

                // ESC键关闭模态框
                if (e.which === 27) {
                    $('.markdown-modal').hide();
                }
            });

            // 窗口关闭前确认
            $(window).on('beforeunload', function() {
                if (self.hasUnsavedChanges) {
                    return '您有未保存的更改，确定要离开吗？';
                }
            });

            // 模态框关闭
            $('.modal-close').on('click', function() {
                $(this).closest('.markdown-modal').hide();
            });

            // 点击模态框外部关闭
            $('.markdown-modal').on('click', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });

            // 标签建议选择
            $(document).on('click', '.tag-suggestion', function() {
                self.selectTagSuggestion($(this).text());
            });

            // 标签输入框键盘导航
            $('#post-tags').on('keydown', function(e) {
                const $suggestions = $('#tags-suggestions');
                const $active = $suggestions.find('.tag-suggestion.active');
                
                if ($suggestions.is(':visible')) {
                    switch(e.which) {
                        case 38: // 上箭头
                            e.preventDefault();
                            if ($active.length && $active.prev().length) {
                                $active.removeClass('active').prev().addClass('active');
                            } else {
                                $suggestions.find('.tag-suggestion').removeClass('active').last().addClass('active');
                            }
                            break;
                        case 40: // 下箭头
                            e.preventDefault();
                            if ($active.length && $active.next().length) {
                                $active.removeClass('active').next().addClass('active');
                            } else {
                                $suggestions.find('.tag-suggestion').removeClass('active').first().addClass('active');
                            }
                            break;
                        case 13: // 回车
                            e.preventDefault();
                            if ($active.length) {
                                self.selectTagSuggestion($active.text());
                            }
                            break;
                        case 27: // ESC
                            $suggestions.hide();
                            break;
                    }
                }
            });

            // 自动生成分类别名
            $('#new-category-name').on('input', function() {
                const name = $(this).val();
                const slug = name.toLowerCase()
                    .replace(/[^a-z0-9\u4e00-\u9fa5]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                $('#new-category-slug').val(slug);
            });
        },

        // 设置编辑器
        setupEditor: function() {
            // 设置编辑器高度
            this.resizeEditor();
            
            // 监听窗口大小变化
            const self = this;
            $(window).on('resize', function() {
                self.resizeEditor();
            });

            // 支持Tab键缩进
            $('#markdown-editor').on('keydown', function(e) {
                if (e.which === 9) { // Tab键
                    e.preventDefault();
                    const textarea = this;
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const value = textarea.value;
                    
                    if (e.shiftKey) {
                        // Shift+Tab 减少缩进
                        const lineStart = value.lastIndexOf('\n', start - 1) + 1;
                        const line = value.substring(lineStart, value.indexOf('\n', start));
                        if (line.startsWith('    ')) {
                            textarea.value = value.substring(0, lineStart) + 
                                           line.substring(4) + 
                                           value.substring(lineStart + line.length);
                            textarea.selectionStart = textarea.selectionEnd = start - 4;
                        }
                    } else {
                        // Tab 增加缩进
                        textarea.value = value.substring(0, start) + 
                                       '    ' + 
                                       value.substring(end);
                        textarea.selectionStart = textarea.selectionEnd = start + 4;
                    }
                    
                    self.updatePreview();
                    self.updateCounts();
                }
            });
        },

        // 设置标签自动完成
        setupTagsAutocomplete: function() {
            // 隐藏标签建议框当点击其他地方时
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.tags-input-wrapper').length) {
                    $('#tags-suggestions').hide();
                }
            });
        },

        // 设置媒体上传
        setupMediaUpload: function() {
            const self = this;
            
            // 检查wp.media是否可用
            if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                console.warn('WordPress媒体库不可用');
                return;
            }
            
            // 创建媒体框架实例
            this.mediaFrame = wp.media({
                title: advancedMarkdownEditor.strings.selectImage,
                button: {
                    text: advancedMarkdownEditor.strings.insertImage
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            // 当媒体被选择时
            this.mediaFrame.on('select', function() {
                const attachment = self.mediaFrame.state().get('selection').first().toJSON();
                self.insertImageFromMedia(attachment);
            });
            
            // 设置拖拽上传
            this.setupDragUpload();
        },

        // 设置拖拽上传
        setupDragUpload: function() {
            const self = this;
            const $editor = $('#markdown-editor');
            
            // 防止默认的拖拽行为
            $editor.on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('drag-over');
            });
            
            $editor.on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
            });
            
            // 处理文件拖拽放置
            $editor.on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    
                    // 检查是否为图片文件
                    if (file.type.startsWith('image/')) {
                        self.uploadFileToMedia(file, e.originalEvent.target.selectionStart);
                    } else {
                        // 不是图片文件
                        e.preventDefault();
                        self.showNotification('请拖拽图片文件', 'error');
                    }
                }
            });
        },

        // 上传文件到媒体库
        uploadFileToMedia: function(file, cursorPosition) {
            const self = this;
            
            // 创建FormData
            const formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'advamaed_upload_image_for_markdown');
            formData.append('nonce', advancedMarkdownEditor.nonce);
            
            // 显示上传状态
            const $status = $('#save-status');
            $status.text('上传图片中...').addClass('saving');
            
            // 使用自定义的AJAX上传处理器
            $.ajax({
                url: advancedMarkdownEditor.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success && response.data && response.data.url) {
                        // 构建Markdown图片语法
                        const altText = response.data.alt || response.data.title || response.data.filename.replace(/\.[^/.]+$/, "");
                        const markdownImage = `![${altText}](${response.data.url})`;
                        
                        // 插入到指定位置
                        const textarea = $('#markdown-editor')[0];
                        const currentValue = textarea.value;
                        const before = currentValue.substring(0, cursorPosition || 0);
                        const after = currentValue.substring(cursorPosition || 0);
                        
                        textarea.value = before + markdownImage + after;
                        
                        // 设置光标位置
                        const newCursorPos = (cursorPosition || 0) + markdownImage.length;
                        textarea.focus();
                        textarea.selectionStart = textarea.selectionEnd = newCursorPos;
                        
                        // 更新预览和统计
                        self.updatePreview();
                        self.updateCounts();
                        self.markAsChanged();
                        
                        $status.text('图片上传成功').removeClass('saving').addClass('saved');
                        setTimeout(function() {
                            $status.text('').removeClass('saved');
                        }, 3000);
                    } else {
                        const errorMsg = response.data || '未知错误';
                        $status.text('图片上传失败: ' + errorMsg).removeClass('saving').addClass('error');
                        setTimeout(function() {
                            $status.text('').removeClass('error');
                        }, 5000);
                        console.error('上传失败:', response);
                    }
                },
                error: function(xhr, status, error) {
                    $status.text('图片上传失败: 网络错误').removeClass('saving').addClass('error');
                    setTimeout(function() {
                        $status.text('').removeClass('error');
                    }, 5000);
                    console.error('上传错误:', xhr.responseText);
                }
            });
        },

        // 从媒体库插入图片
        insertImageFromMedia: function(attachment) {
            const textarea = $('#markdown-editor')[0];
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            
            // 构建Markdown图片语法
            const altText = selectedText || attachment.alt || attachment.title || '图片';
            const imageUrl = attachment.url;
            const markdownImage = `![${altText}](${imageUrl})`;
            
            // 插入到编辑器
            textarea.value = textarea.value.substring(0, start) + 
                           markdownImage + 
                           textarea.value.substring(end);
            
            // 设置光标位置到图片后面
            const newCursorPos = start + markdownImage.length;
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = newCursorPos;
            
            // 更新预览和统计
            this.updatePreview();
            this.updateCounts();
            this.markAsChanged();
        },

        // 打开媒体库
        openMediaLibrary: function() {
            if (this.mediaFrame) {
                this.mediaFrame.open();
            } else {
                // 媒体框架未初始化，尝试重新初始化
                this.setupMediaUpload();
                if (this.mediaFrame) {
                    this.mediaFrame.open();
                }
            }
        },

        // 显示标签建议
        showTagSuggestions: function(input) {
            const $suggestions = $('#tags-suggestions');
            
            if (!input || input.length < 1) {
                $suggestions.hide();
                return;
            }

            // 获取当前输入的最后一个标签
            const tags = input.split(',');
            const currentTag = tags[tags.length - 1].trim().toLowerCase();
            
            if (currentTag.length < 1) {
                $suggestions.hide();
                return;
            }

            // 过滤可用标签
            const matches = window.availableTags.filter(function(tag) {
                return tag.toLowerCase().indexOf(currentTag) === 0 && 
                       tags.indexOf(tag) === -1; // 排除已选择的标签
            });

            if (matches.length === 0) {
                $suggestions.hide();
                return;
            }

            // 构建建议列表
            let html = '';
            matches.slice(0, 10).forEach(function(tag) { // 最多显示10个建议
                html += '<div class="tag-suggestion">' + tag + '</div>';
            });

            $suggestions.html(html).show();
        },

        // 选择标签建议
        selectTagSuggestion: function(selectedTag) {
            const $tagsInput = $('#post-tags');
            const currentValue = $tagsInput.val();
            const tags = currentValue.split(',');
            
            // 替换最后一个标签
            tags[tags.length - 1] = selectedTag;
            
            $tagsInput.val(tags.join(', ') + ', ').focus();
            $('#tags-suggestions').hide();
            this.markAsChanged();
        },

        // 快速添加标签
        addQuickTag: function(tagName) {
            const $tagsInput = $('#post-tags');
            const currentValue = $tagsInput.val().trim();
            const existingTags = currentValue ? currentValue.split(',').map(tag => tag.trim()) : [];
            
            // 检查标签是否已存在
            if (existingTags.indexOf(tagName) === -1) {
                const newValue = currentValue ? currentValue + ', ' + tagName : tagName;
                $tagsInput.val(newValue).focus();
                this.markAsChanged();
            }
        },

        // 创建新分类
        createNewCategory: function() {
            const $form = $('#new-category-form');
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();
            
            $submitBtn.text('创建中...').prop('disabled', true);

            const formData = {
                action: 'advamaed_create_new_category',
                category_name: $('#new-category-name').val(),
                category_slug: $('#new-category-slug').val(),
                category_parent: $('#new-category-parent').val(),
                category_description: $('#new-category-description').val(),
                nonce: advancedMarkdownEditor.nonce
            };

            const self = this;
            $.ajax({
                url: advancedMarkdownEditor.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // 添加新分类到树形列表
                        const newCategory = response.data;
                        const $categoriesTree = $('.categories-tree');
                        
                        if ($categoriesTree.find('.no-items').length) {
                            $categoriesTree.html('');
                        }
                        
                        // 确定插入位置（根据父级分类）
                        const parentId = parseInt(newCategory.parent);
                        let categoryHtml = '<div class="category-tree-item" data-level="0">' +
                            '<label class="category-label">' +
                            '<input type="checkbox" name="post_categories[]" value="' + newCategory.term_id + '" checked>' +
                            '<span class="category-name">' + newCategory.name + '</span>' +
                            '</label>' +
                            '</div>';
                        
                        if (parentId > 0) {
                            // 作为子分类添加
                            const $parentItem = $categoriesTree.find('input[value="' + parentId + '"]').closest('.category-tree-item');
                            if ($parentItem.length) {
                                let $children = $parentItem.find('> .category-children');
                                if (!$children.length) {
                                    $children = $('<div class="category-children"></div>');
                                    $parentItem.append($children);
                                }
                                
                                const parentLevel = parseInt($parentItem.attr('data-level'));
                                categoryHtml = '<div class="category-tree-item" data-level="' + (parentLevel + 1) + '">' +
                                    '<label class="category-label">' +
                                    '<input type="checkbox" name="post_categories[]" value="' + newCategory.term_id + '" checked>' +
                                    '<span class="category-name">' + newCategory.name + '</span>' +
                                    '</label>' +
                                    '</div>';
                                
                                $children.append(categoryHtml);
                            } else {
                                // 如果找不到父级，就添加到根级
                                $categoriesTree.append(categoryHtml);
                            }
                        } else {
                            // 添加到根级
                            $categoriesTree.append(categoryHtml);
                        }
                        
                        // 绑定新分类的change事件
                        $categoriesTree.find('input[name="post_categories[]"]').off('change').on('change', function() {
                            self.markAsChanged();
                        });
                        
                        // 关闭模态框并重置表单
                        $('#new-category-modal').hide();
                        $form[0].reset();
                        
                        // 标记为已更改
                        self.markAsChanged();
                        
                        self.showNotification('分类创建成功！', 'success');
                    } else {
                        self.showNotification('分类创建失败：' + (response.data || '未知错误'), 'error');
                    }
                },
                error: function() {
                    self.showNotification('分类创建失败：网络错误', 'error');
                },
                complete: function() {
                    $submitBtn.text(originalText).prop('disabled', false);
                }
            });
        },

        // 调整编辑器尺寸
        resizeEditor: function() {
            const windowHeight = $(window).height();
            const headerHeight = $('.editor-header').outerHeight();
            const tabsHeight = $('.editor-tabs').outerHeight();
            const toolbarHeight = $('.toolbar').outerHeight();
            const footerHeight = $('.editor-footer').outerHeight();
            const adminBarHeight = $('#wpadminbar').outerHeight() || 0;
            
            // 计算可用高度，考虑侧边栏布局
            const availableHeight = windowHeight - headerHeight - tabsHeight - 
                                  toolbarHeight - footerHeight - adminBarHeight - 120;
            
            const minHeight = Math.max(400, availableHeight);
            $('#markdown-editor, .preview-content').css('min-height', minHeight + 'px');
            
            // 调整侧边栏高度
            const sidebarHeight = windowHeight - headerHeight - adminBarHeight - 40;
            $('.editor-sidebar').css('max-height', Math.max(500, sidebarHeight) + 'px');
        },

        // 切换标签页
        switchTab: function(tab) {
            $('.tab-button').removeClass('active');
            $(`.tab-button[data-tab="${tab}"]`).addClass('active');
            
            $('.editor-content').removeClass('split-mode');
            $('.editor-pane').removeClass('active');
            
            switch(tab) {
                case 'write':
                    $('.write-pane').addClass('active');
                    break;
                case 'preview':
                    $('.preview-pane').addClass('active');
                    this.updatePreview();
                    break;
                case 'split':
                    $('.editor-content').addClass('split-mode');
                    $('.editor-pane').addClass('active');
                    this.updatePreview();
                    break;
            }
        },

        // 处理工具栏动作
        handleToolbarAction: function(action) {
            const textarea = $('#markdown-editor')[0];
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            const self = this;
            
            let replacement = '';
            let newCursorPos = start;
            
            switch(action) {
                case 'bold':
                    replacement = `**${selectedText || '粗体文本'}**`;
                    newCursorPos = selectedText ? end + 4 : start + 2;
                    break;
                case 'italic':
                    replacement = `*${selectedText || '斜体文本'}*`;
                    newCursorPos = selectedText ? end + 2 : start + 1;
                    break;
                case 'heading':
                    replacement = `## ${selectedText || '标题'}`;
                    newCursorPos = selectedText ? end + 3 : start + 3;
                    break;
                case 'link':
                    this.showInputDialog('插入链接', '请输入链接地址', 'https://', function(url) {
                        const replacement = `[${selectedText || '链接文本'}](${url})`;
                        const newCursorPos = selectedText ? end + url.length + 4 : start + 1;
                        
                        // 替换文本
                        textarea.value = textarea.value.substring(0, start) + 
                                       replacement + 
                                       textarea.value.substring(end);
                        
                        // 设置光标位置
                        textarea.focus();
                        textarea.selectionStart = textarea.selectionEnd = newCursorPos;
                        
                        self.updatePreview();
                        self.updateCounts();
                        self.markAsChanged();
                    });
                    return;
                case 'media':
                    // 打开WordPress媒体库
                    this.openMediaLibrary();
                    return; // 不需要继续处理文本替换
                case 'image':
                    this.showInputDialog('插入图片', '请输入图片地址', 'https://', function(imgUrl) {
                        const replacement = `![${selectedText || '图片描述'}](${imgUrl})`;
                        const newCursorPos = selectedText ? end + imgUrl.length + 5 : start + 2;
                        
                        // 替换文本
                        textarea.value = textarea.value.substring(0, start) + 
                                       replacement + 
                                       textarea.value.substring(end);
                        
                        // 设置光标位置
                        textarea.focus();
                        textarea.selectionStart = textarea.selectionEnd = newCursorPos;
                        
                        self.updatePreview();
                        self.updateCounts();
                        self.markAsChanged();
                    });
                    return;
                case 'code':
                    if (selectedText.includes('\n')) {
                        replacement = `\`\`\`\n${selectedText || '代码'}\n\`\`\``;
                        newCursorPos = selectedText ? end + 8 : start + 4;
                    } else {
                        replacement = `\`${selectedText || '代码'}\``;
                        newCursorPos = selectedText ? end + 2 : start + 1;
                    }
                    break;
                case 'quote':
                    replacement = `> ${selectedText || '引用文本'}`;
                    newCursorPos = selectedText ? end + 2 : start + 2;
                    break;
                case 'list':
                    replacement = `- ${selectedText || '列表项'}`;
                    newCursorPos = selectedText ? end + 2 : start + 2;
                    break;
            }
            
            // 替换文本
            textarea.value = textarea.value.substring(0, start) + 
                           replacement + 
                           textarea.value.substring(end);
            
            // 设置光标位置
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = newCursorPos;
            
            this.updatePreview();
            this.updateCounts();
            this.markAsChanged();
        },

        // 更新预览
        updatePreview: function() {
            if (typeof marked === 'undefined') {
                return;
            }
            
            const markdown = $('#markdown-editor').val();
            const html = marked.parse(markdown);
            
            if (html.trim()) {
                $('#preview-content').html(html);
            } else {
                $('#preview-content').html('<p class="no-content">预览将在这里显示...</p>');
            }
        },

        // 更新字数和行数统计
        updateCounts: function() {
            const content = $('#markdown-editor').val();
            const wordCount = content.replace(/\s/g, '').length;
            const lineCount = content.split('\n').length;
            const charCount = content.length;
            const paragraphCount = content.split(/\n\s*\n/).filter(p => p.trim().length > 0).length;
            
            // 更新底部状态栏
            $('#word-count').text(wordCount + ' 字');
            $('#line-count').text(lineCount + ' 行');
            
            // 更新侧边栏详细统计
            $('#word-count-sidebar').text(wordCount);
            $('#line-count-sidebar').text(lineCount);
            $('#char-count-sidebar').text(charCount);
            $('#paragraph-count-sidebar').text(paragraphCount);
        },

        // 标记为已更改
        markAsChanged: function() {
            this.hasUnsavedChanges = true;
            $('#save-status').text('未保存的更改');
        },

        // 加载文章
        loadPost: function() {
            const postId = $('#post-id').val();
            if (!postId) {
                this.updatePreview();
                this.updateCounts();
                return;
            }

            const self = this;
            $.ajax({
                url: advancedMarkdownEditor.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'advamaed_get_markdown_post',
                    post_id: postId,
                    nonce: advancedMarkdownEditor.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#post-title').val(response.data.title);
                        $('#markdown-editor').val(response.data.markdown_content);
                        $('#post-status, #post-status-sidebar').val(response.data.status);
                        
                        // 更新按钮状态
                        self.updateButtonsAfterLoad(response.data.status);
                        
                        self.updatePreview();
                        self.updateCounts();
                        self.hasUnsavedChanges = false;
                    }
                }
            });
        },

        // 加载后更新按钮状态
        updateButtonsAfterLoad: function(status) {
            const isPublished = status === 'publish';
            const isPrivate = status === 'private';
            
            const $publishBtn = $('#publish-post');
            const $publishBtnSidebar = $('#publish-post-sidebar');
            
            if (isPublished || isPrivate) {
                $publishBtn.text('更新');
                $publishBtnSidebar.text('更新');
                
                if (isPublished) {
                    $('.editor-header').addClass('published');
                }
            } else {
                $publishBtn.text('发布');
                $publishBtnSidebar.text('发布');
                $('.editor-header').removeClass('published');
            }
        },

        // 保存文章
        savePost: function(status) {
            // 防止重复提交
            if (this.isSaving) {
                return;
            }
            
            const postId = $('#post-id').val();
            const title = $('#post-title').val();
            const markdownContent = $('#markdown-editor').val();
            const postStatus = status || $('#post-status').val();
            
            // 获取选中的分类
            const categories = [];
            $('input[name="post_categories[]"]:checked').each(function() {
                categories.push(parseInt($(this).val()));
            });
            
            // 获取标签
            const tagsInput = $('#post-tags').val();
            const tags = tagsInput ? tagsInput.split(',').map(function(tag) {
                return tag.trim();
            }).filter(function(tag) {
                return tag.length > 0;
            }) : [];
            
            if (!title.trim()) {
                this.showNotification('请输入文章标题', 'warning');
                $('#post-title').focus();
                return;
            }

            if (!markdownContent.trim()) {
                this.showNotification('请输入文章内容', 'warning');
                $('#markdown-editor').focus();
                return;
            }

            // 转换Markdown为HTML
            const htmlContent = typeof marked !== 'undefined' ? 
                               marked.parse(markdownContent) : markdownContent;

            // 设置保存状态
            this.isSaving = true;
            this.showSaveStatus('saving');
            
            // 禁用按钮
            const $saveButtons = $('#save-draft, #save-draft-sidebar, #publish-post, #publish-post-sidebar');
            $saveButtons.prop('disabled', true);
            
            const self = this;
            $.ajax({
                url: advancedMarkdownEditor.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'advamaed_save_markdown_post',
                    post_id: postId,
                    title: title,
                    markdown_content: markdownContent,
                    html_content: htmlContent,
                    status: postStatus,
                    categories: categories,
                    tags: tags,
                    nonce: advancedMarkdownEditor.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showSaveStatus('saved');
                        self.hasUnsavedChanges = false;
                        
                        // 如果是新文章，更新URL
                        if (!postId) {
                            $('#post-id').val(response.data.post_id);
                            const newUrl = window.location.href + '&post=' + response.data.post_id;
                            window.history.replaceState({}, '', newUrl);
                        }
                        
                        // 更新状态选择器
                        $('#post-status, #post-status-sidebar').val(response.data.post_status);
                        
                        // 更新按钮文本和状态
                        self.updateButtonsAfterSave(response.data);
                        
                        // 显示成功通知
                        self.showSuccessNotification(response.data);
                        
                    } else {
                        self.showSaveStatus('error');
                        self.showNotification('保存失败: ' + (response.data || '未知错误'), 'error');
                    }
                },
                error: function() {
                    self.showSaveStatus('error');
                    self.showNotification('保存失败: 网络错误', 'error');
                },
                complete: function() {
                    // 重置保存状态
                    self.isSaving = false;
                    // 重新启用按钮
                    $saveButtons.prop('disabled', false);
                }
            });
        },

        // 显示保存状态
        showSaveStatus: function(status) {
            const $saveStatus = $('#save-status');
            $saveStatus.removeClass('saving saved error');
            
            switch(status) {
                case 'saving':
                    $saveStatus.addClass('saving').text(advancedMarkdownEditor.strings.saving);
                    break;
                case 'saved':
                    $saveStatus.addClass('saved').text(advancedMarkdownEditor.strings.saved);
                    setTimeout(function() {
                        $saveStatus.text('');
                    }, 3000);
                    break;
                case 'error':
                    $saveStatus.addClass('error').text(advancedMarkdownEditor.strings.error);
                    break;
            }
        },

        // 设置自动保存
        setupAutoSave: function() {
            const self = this;
            setInterval(function() {
                if (self.hasUnsavedChanges) {
                    const title = $('#post-title').val();
                    const content = $('#markdown-editor').val();
                    
                    if (title.trim() && content.trim()) {
                        self.savePost('draft');
                    }
                }
            }, 60000); // 每分钟自动保存一次
        },

        // 更新保存按钮状态
        updateButtonsAfterSave: function(data) {
            const isPublished = data.post_status === 'publish';
            const isPrivate = data.post_status === 'private';
            
            // 更新主要发布按钮
            const $publishBtn = $('#publish-post');
            const $publishBtnSidebar = $('#publish-post-sidebar');
            
            if (isPublished || isPrivate) {
                $publishBtn.text('更新');
                $publishBtnSidebar.text('更新');
            } else {
                $publishBtn.text('发布');
                $publishBtnSidebar.text('发布');
            }
            
            // 如果当前文章已发布，更新状态显示
            if (isPublished) {
                $('.editor-header').addClass('published');
            }
        },

        // 显示成功通知
        showSuccessNotification: function(data) {
            const isPublished = data.post_status === 'publish';
            const isPrivate = data.post_status === 'private';
            
            let message = data.message;
            let notificationType = 'success';
            
            // 构建通知内容
            if (isPublished && data.view_link) {
                message += '<br><a href="' + data.view_link + '" target="_blank" class="notification-link">查看文章</a>';
            } else if (isPrivate && data.view_link) {
                message += '<br><a href="' + data.view_link + '" target="_blank" class="notification-link">查看私有文章</a>';
            }
            
            // 显示HTML通知
            this.showHTMLNotification(message, notificationType, 5000);
        },

        // 显示包含HTML的通知
        showHTMLNotification: function(htmlMessage, type = 'info', duration = 3000) {
            const $container = $('#markdown-editor-notifications');
            const notificationId = 'notification-' + Date.now();
            const self = this;
            const $notification = $(`
                <div id="${notificationId}" class="markdown-notification ${type}">
                    <div class="notification-message">${htmlMessage}</div>
                    <button class="notification-close" data-notification-id="${notificationId}">&times;</button>
                </div>
            `);
            
            $container.append($notification);
            
            // 绑定关闭事件
            $notification.find('.notification-close').on('click', function() {
                self.closeNotification($(this).data('notification-id'));
            });
            
            // 自动关闭
            if (duration > 0) {
                setTimeout(() => {
                    self.closeNotification(notificationId);
                }, duration);
            }
        },

        // 未保存更改标志
        hasUnsavedChanges: false,

        // 保存状态标志
        isSaving: false
    };

    // 页面加载完成后初始化
    $(document).ready(function() {
        // 确保marked库已加载
        if (typeof marked !== 'undefined') {
            // 配置marked选项
            marked.setOptions({
                gfm: true,
                breaks: true,
                sanitize: false,
                highlight: function(code, language) {
                    // 如果有代码高亮库，可以在这里使用
                    return code;
                }
            });
        }
        
        // 初始化可用标签数组（如果不存在）
        if (typeof window.availableTags === 'undefined') {
            window.availableTags = [];
        }
        
        MarkdownEditor.init();
    });

})(jQuery); 