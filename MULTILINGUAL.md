# Advanced Markdown Editor - 多语言支持

Advanced Markdown Editor现在支持多种语言，为全球用户提供本地化体验。

## 支持的语言

### 已翻译语言
- **中文 (简体)** - `zh_CN` (默认)
- **英语** - `en_US`
- **法语** - `fr_FR`
- **德语** - `de_DE`
- **俄语** - `ru_RU`
- **日语** - `ja`

## 语言文件结构

```
languages/
├── advanced-markdown-editor.pot          # 翻译模板文件
├── advanced-markdown-editor-en_US.po     # 英语翻译源文件
├── advanced-markdown-editor-en_US.mo     # 英语翻译编译文件
├── advanced-markdown-editor-fr_FR.po     # 法语翻译源文件
├── advanced-markdown-editor-fr_FR.mo     # 法语翻译编译文件
├── advanced-markdown-editor-de_DE.po     # 德语翻译源文件
├── advanced-markdown-editor-de_DE.mo     # 德语翻译编译文件
├── advanced-markdown-editor-ru_RU.po     # 俄语翻译源文件
├── advanced-markdown-editor-ru_RU.mo     # 俄语翻译编译文件
├── advanced-markdown-editor-ja.po        # 日语翻译源文件
└── advanced-markdown-editor-ja.mo        # 日语翻译编译文件
```

## 如何切换语言

### 方法1：WordPress设置
1. 进入WordPress后台 → 设置 → 常规
2. 在"站点语言"中选择对应语言
3. 保存设置即可

### 方法2：wp-config.php设置
在`wp-config.php`文件中添加或修改：

```php
// 英语
define('WPLANG', 'en_US');

// 法语
define('WPLANG', 'fr_FR');

// 德语
define('WPLANG', 'de_DE');

// 俄语
define('WPLANG', 'ru_RU');

// 日语
define('WPLANG', 'ja');

// 中文（默认）
define('WPLANG', '');
```

### 方法3：用户个人设置
每个用户可以在个人资料页面设置自己的语言偏好。

## 翻译对照表

### 核心功能翻译

| 中文 | English | Français | Deutsch | Русский | 日本語 |
|------|---------|----------|---------|---------|-------- |
| Markdown 编辑器 | Markdown Editor | Éditeur Markdown | Markdown Editor | Markdown Редактор | Markdownエディター |
| 新建文章 | New Post | Nouvel Article | Neuer Beitrag | Новая запись | 新規投稿 |
| 保存草稿 | Save Draft | Enregistrer le Brouillon | Entwurf Speichern | Сохранить черновик | 下書きを保存 |
| 发布 | Published | Publié | Veröffentlicht | Опубликовано | 公開 |
| 分类 | Categories | Catégories | Kategorien | Категории | カテゴリー |
| 标签 | Tags | Étiquettes | Schlagwörter | Метки | タグ |
| 上传图片 | Upload Image | Télécharger une Image | Bild Hochladen | Загрузить изображение | 画像をアップロード |
| 预览 | Preview | Aperçu | Vorschau | Предпросмотр | プレビュー |

### 编辑器工具翻译

| 中文 | English | Français | Deutsch | Русский | 日本語 |
|------|---------|----------|---------|---------|-------- |
| 粗体 | Bold | Gras | Fett | Жирный | 太字 |
| 斜体 | Italic | Italique | Kursiv | Курсив | 斜体 |
| 链接 | Link | Lien | Link | Ссылка | リンク |
| 代码 | Code | Code | Code | Код | コード |
| 引用 | Quote | Citation | Zitat | Цитата | 引用 |
| 列表 | List | Liste | Liste | Список | リスト |

## 为开发者提供的信息

### 添加新语言

1. **创建PO文件**：
   ```bash
   cp languages/advanced-markdown-editor.pot languages/advanced-markdown-editor-xx_XX.po
   ```

2. **翻译字符串**：
   使用Poedit、GlotPress或手动编辑PO文件

3. **生成MO文件**：
   ```bash
   msgfmt languages/advanced-markdown-editor-xx_XX.po -o languages/advanced-markdown-editor-xx_XX.mo
   ```

### 更新现有翻译

1. **更新POT模板**：
   ```bash
   wp i18n make-pot . languages/advanced-markdown-editor.pot
   ```

2. **合并到现有PO文件**：
   ```bash
   msgmerge --update languages/advanced-markdown-editor-xx_XX.po languages/advanced-markdown-editor.pot
   ```

3. **重新生成MO文件**：
   ```bash
   msgfmt languages/advanced-markdown-editor-xx_XX.po -o languages/advanced-markdown-editor-xx_XX.mo
   ```

### 在代码中使用翻译

所有可翻译字符串都应该使用WordPress国际化函数：

```php
// 基本翻译
__('文本', 'advanced-markdown-editor')

// 输出翻译
_e('文本', 'advanced-markdown-editor')

// 复数形式
_n('单数', '复数', $number, 'advanced-markdown-editor')

// 带上下文的翻译
_x('文本', '上下文', 'advanced-markdown-editor')
```

## 翻译质量说明

所有翻译都经过专业语言处理，确保：

- **准确性**：术语翻译准确，符合技术文档标准
- **一致性**：同一概念在整个界面中使用相同翻译
- **本地化**：适应不同语言的表达习惯
- **用户友好**：界面文字简洁明了，易于理解

## 贡献翻译

欢迎为Advanced Markdown Editor贡献翻译！

1. Fork项目仓库
2. 添加或改进语言文件
3. 提交Pull Request
4. 我们会审核并合并您的贡献

### 翻译指南

- 保持界面文字简洁
- 使用正式的语言风格
- 确保技术术语准确
- 测试翻译在实际界面中的效果
- 检查字符串长度是否适合界面布局

## 技术支持

如有翻译相关问题，请：

1. 检查语言文件是否正确安装
2. 确认WordPress语言设置
3. 清除缓存并刷新页面
4. 在GitHub提交Issue报告问题

---

*Advanced Markdown Editor致力于为全球用户提供最佳的多语言体验。* 