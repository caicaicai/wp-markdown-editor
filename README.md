# Advanced Markdown Editor

一个强大而易用的WordPress Markdown编辑器插件，为您提供专业的Markdown编辑体验，同时保持与WordPress原生编辑器的完美兼容性。

## ✨ 特性

- 🎯 **独立的Markdown编辑器** - 不破坏WordPress原生编辑器
- 📝 **实时预览** - 支持编写、预览、分屏三种模式
- 🛠️ **丰富的工具栏** - 快速插入Markdown语法
- ⌨️ **键盘快捷键** - 提高编辑效率
- 💾 **自动保存** - 防止内容丢失
- 🔄 **双向兼容** - 可在Markdown和原生编辑器间切换
- 📱 **响应式设计** - 支持各种设备尺寸
- 🎨 **现代化界面** - 美观易用的编辑界面
- 🌍 **多语言支持** - 支持中文、英语、法语、德语、俄语、日语
- 🏷️ **分类标签管理** - 树形分类结构，标签自动提示
- 📷 **图片上传** - 支持拖拽上传和媒体库集成
- 📊 **统计信息** - 实时显示字数、行数、字符数、段落数

## 📦 安装

### 方法一：插件上传安装
1. 下载插件文件，压缩为ZIP格式
2. 在WordPress管理后台，进入 **插件 > 安装插件**
3. 点击 **上传插件**，选择ZIP文件并安装
4. 激活插件

### 方法二：手动安装
1. 将插件文件夹上传到 `/wp-content/plugins/` 目录
2. 在WordPress管理后台激活插件

## 🚀 使用方法

### 创建新的Markdown文章
1. 在WordPress管理后台，点击左侧菜单的 **Markdown 编辑器**
2. 选择 **新建文章** 或直接点击主菜单
3. 输入标题和Markdown内容
4. 选择发布状态（草稿/发布/私有）
5. 点击保存或发布

### 编辑现有文章
1. 在文章列表页面，点击 **Markdown编辑** 链接
2. 或者在文章编辑页面的侧边栏，点击 **用Markdown编辑器编辑**
3. 在Markdown编辑器中修改内容
4. 保存更改

### 编辑器功能

#### 三种编辑模式
- **编写模式** - 专注于Markdown编写
- **预览模式** - 实时查看渲染效果
- **分屏模式** - 同时显示编写和预览区域

#### 工具栏功能
- **粗体** - 快速添加粗体格式 (`**文本**`)
- **斜体** - 快速添加斜体格式 (`*文本*`)
- **标题** - 插入标题 (`## 标题`)
- **链接** - 创建链接 (`[文本](URL)`)
- **图片** - 插入图片 (`![描述](URL)`)
- **代码** - 插入代码块或内联代码
- **引用** - 添加引用 (`> 引用`)
- **列表** - 创建无序列表 (`- 项目`)

#### 键盘快捷键
- `Ctrl/Cmd + S` - 保存文章
- `Ctrl/Cmd + B` - 粗体
- `Ctrl/Cmd + I` - 斜体
- `Tab` - 增加缩进
- `Shift + Tab` - 减少缩进

## 📚 Markdown语法支持

插件支持标准的Markdown语法，包括：

### 标题
```markdown
# 一级标题
## 二级标题
### 三级标题
```

### 文本格式
```markdown
**粗体文本**
*斜体文本*
`内联代码`
```

### 链接和图片
```markdown
[链接文本](http://example.com)
![图片描述](image.jpg)
```

### 列表
```markdown
- 无序列表项
- 另一个项目

1. 有序列表项
2. 第二个项目
```

### 引用
```markdown
> 这是一个引用
> 可以跨越多行
```

### 代码块
```markdown
```
代码块
```
```

### 表格
```markdown
| 列1 | 列2 | 列3 |
|-----|-----|-----|
| 内容1 | 内容2 | 内容3 |
```

## 🔧 技术特性

- **前端技术** - HTML5, CSS3, JavaScript (jQuery)
- **Markdown解析** - Marked.js库
- **响应式设计** - 适配桌面和移动设备
- **AJAX保存** - 无刷新保存体验
- **安全性** - WordPress nonce验证
- **兼容性** - 支持WordPress 5.0+

## 📂 文件结构

```
wp-markdown-editor/
├── wp-markdown-editor.php    # 主插件文件
├── templates/
│   └── editor.php            # 编辑器模板
├── assets/
│   ├── css/
│   │   └── admin.css         # 管理样式
│   └── js/
│       └── admin.js          # 管理脚本
├── languages/                # 多语言文件
│   ├── wp-markdown-editor.pot       # 翻译模板
│   ├── wp-markdown-editor-en_US.po  # 英语翻译
│   ├── wp-markdown-editor-en_US.mo  # 英语编译文件
│   ├── wp-markdown-editor-fr_FR.po  # 法语翻译
│   ├── wp-markdown-editor-fr_FR.mo  # 法语编译文件
│   ├── wp-markdown-editor-de_DE.po  # 德语翻译
│   ├── wp-markdown-editor-de_DE.mo  # 德语编译文件
│   ├── wp-markdown-editor-ru_RU.po  # 俄语翻译
│   ├── wp-markdown-editor-ru_RU.mo  # 俄语编译文件
│   ├── wp-markdown-editor-ja.po     # 日语翻译
│   └── wp-markdown-editor-ja.mo     # 日语编译文件
├── README.md                 # 说明文档
└── MULTILINGUAL.md           # 多语言说明文档
```

## 🔄 数据存储

- **文章内容** - 存储在 `post_content` 字段（HTML格式）
- **Markdown源码** - 存储在 `_markdown_content` meta字段
- **标记字段** - `_is_markdown_post` 标识是否为Markdown文章

## 🤝 兼容性

### WordPress兼容性
- 支持WordPress 5.0及以上版本
- 兼容经典编辑器和Gutenberg编辑器
- 与现有主题和插件兼容

### 浏览器支持
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## 🛡️ 安全性

- 使用WordPress nonce验证确保请求安全
- 内容过滤防止XSS攻击
- 权限检查确保只有授权用户可以编辑
- 遵循WordPress编码标准

## 📈 性能优化

- 按需加载资源文件
- 压缩CSS和JavaScript
- 优化数据库查询
- 缓存机制减少服务器负载

## 🌍 多语言支持

插件现已支持多种语言，为全球用户提供本地化体验：

### 支持的语言
- **中文 (简体)** - `zh_CN` (默认)
- **English** - `en_US`
- **Français** - `fr_FR`
- **Deutsch** - `de_DE`
- **Русский** - `ru_RU`
- **日本語** - `ja`

### 语言切换方法
1. **WordPress设置** - 进入 设置 → 常规 → 站点语言
2. **wp-config.php** - 添加 `define('WPLANG', 'en_US');`
3. **用户设置** - 在个人资料中设置语言偏好

### 翻译贡献
欢迎为插件贡献翻译！详细信息请查看 [MULTILINGUAL.md](MULTILINGUAL.md) 文档。

## 🐛 常见问题

### Q: 插件会影响现有的文章吗？
A: 不会。插件完全兼容现有的WordPress编辑器，您可以在两种编辑器间自由切换。

### Q: Markdown文章可以用原生编辑器编辑吗？
A: 可以。Markdown文章会转换为HTML存储，原生编辑器可以正常编辑HTML内容。

### Q: 插件支持哪些Markdown语法？
A: 支持标准Markdown语法以及GitHub风味Markdown (GFM) 扩展。

### Q: 如何备份Markdown源码？
A: Markdown源码存储在WordPress数据库的post meta中，跟随文章一起备份。

## 📝 更新日志

### v1.0.0 (2024-01-01)
- 🎉 首次发布
- ✨ 基本Markdown编辑功能
- 🎨 现代化编辑界面
- 💾 自动保存功能
- 📱 响应式设计
- 🌍 多语言支持（中文、英语、法语、德语、俄语、日语）
- 🏷️ 分类标签管理系统
- 📷 图片上传和媒体库集成
- 📊 实时统计信息显示
- 🔧 键盘快捷键支持
- 💾 自动保存和草稿功能

## 🤝 贡献

欢迎提交Issue和Pull Request！

## 📄 许可证

本插件基于GPL v2或更高版本许可证发布。

## 🙏 致谢

- [Marked.js](https://marked.js.org/) - Markdown解析库
- [WordPress](https://wordpress.org/) - 强大的内容管理系统
- [Dashicons](https://developer.wordpress.org/) - WordPress图标字体

---

如果您觉得这个插件有用，请给我们一个⭐️！ 