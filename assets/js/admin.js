/**
 * WordPress Markdown Editor - Admin JavaScript
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

            // 状态改变事件
            $('#post-status').on('change', function() {
                self.markAsChanged();
            });

            // 保存按钮
            $('#save-draft').on('click', function() {
                self.savePost('draft');
            });

            $('#publish-post').on('click', function() {
                self.savePost('publish');
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
                    }
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

        // 调整编辑器尺寸
        resizeEditor: function() {
            const windowHeight = $(window).height();
            const headerHeight = $('.editor-header').outerHeight();
            const tabsHeight = $('.editor-tabs').outerHeight();
            const toolbarHeight = $('.toolbar').outerHeight();
            const footerHeight = $('.editor-footer').outerHeight();
            const adminBarHeight = $('#wpadminbar').outerHeight() || 0;
            
            const availableHeight = windowHeight - headerHeight - tabsHeight - 
                                  toolbarHeight - footerHeight - adminBarHeight - 100;
            
            $('#markdown-editor, .preview-content').css('min-height', Math.max(400, availableHeight) + 'px');
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
                    const url = prompt('请输入链接地址:', 'http://');
                    if (url) {
                        replacement = `[${selectedText || '链接文本'}](${url})`;
                        newCursorPos = selectedText ? end + url.length + 4 : start + 1;
                    } else {
                        return;
                    }
                    break;
                case 'image':
                    const imgUrl = prompt('请输入图片地址:', 'http://');
                    if (imgUrl) {
                        replacement = `![${selectedText || '图片描述'}](${imgUrl})`;
                        newCursorPos = selectedText ? end + imgUrl.length + 5 : start + 2;
                    } else {
                        return;
                    }
                    break;
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
            
            $('#word-count').text(wordCount + ' 字');
            $('#line-count').text(lineCount + ' 行');
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
                url: wpMarkdownEditor.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_markdown_post',
                    post_id: postId,
                    nonce: wpMarkdownEditor.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#post-title').val(response.data.title);
                        $('#markdown-editor').val(response.data.markdown_content);
                        $('#post-status').val(response.data.status);
                        
                        self.updatePreview();
                        self.updateCounts();
                        self.hasUnsavedChanges = false;
                    }
                }
            });
        },

        // 保存文章
        savePost: function(status) {
            const postId = $('#post-id').val();
            const title = $('#post-title').val();
            const markdownContent = $('#markdown-editor').val();
            const postStatus = status || $('#post-status').val();
            
            if (!title.trim()) {
                alert('请输入文章标题');
                $('#post-title').focus();
                return;
            }

            if (!markdownContent.trim()) {
                alert('请输入文章内容');
                $('#markdown-editor').focus();
                return;
            }

            // 转换Markdown为HTML
            const htmlContent = typeof marked !== 'undefined' ? 
                               marked.parse(markdownContent) : markdownContent;

            this.showSaveStatus('saving');
            
            const self = this;
            $.ajax({
                url: wpMarkdownEditor.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'save_markdown_post',
                    post_id: postId,
                    title: title,
                    markdown_content: markdownContent,
                    html_content: htmlContent,
                    status: postStatus,
                    nonce: wpMarkdownEditor.nonce
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
                        $('#post-status').val(postStatus);
                        
                    } else {
                        self.showSaveStatus('error');
                        alert('保存失败: ' + (response.data || '未知错误'));
                    }
                },
                error: function() {
                    self.showSaveStatus('error');
                    alert('保存失败: 网络错误');
                }
            });
        },

        // 显示保存状态
        showSaveStatus: function(status) {
            const $saveStatus = $('#save-status');
            $saveStatus.removeClass('saving saved error');
            
            switch(status) {
                case 'saving':
                    $saveStatus.addClass('saving').text(wpMarkdownEditor.strings.saving);
                    break;
                case 'saved':
                    $saveStatus.addClass('saved').text(wpMarkdownEditor.strings.saved);
                    setTimeout(function() {
                        $saveStatus.text('');
                    }, 3000);
                    break;
                case 'error':
                    $saveStatus.addClass('error').text(wpMarkdownEditor.strings.error);
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

        // 未保存更改标志
        hasUnsavedChanges: false
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
        
        MarkdownEditor.init();
    });

})(jQuery); 