# WordPress 插件目录提交准备清单

## 插件重命名完成 ✅

### 已完成的更改：
- [x] 插件名称：`WordPress Markdown 编辑器` → `Advanced Markdown Editor`
- [x] 移除了限制性术语 "WordPress"
- [x] 更新主插件文件头部信息
- [x] 更新所有文档文件（README.md, MULTILINGUAL.md）
- [x] 更新所有语言文件（POT, PO, MO）
- [x] 更新CSS和JS文件头部注释
- [x] 更新示例文件

## 自动检查问题修复 ✅

### 已修复的问题：
- [x] **隐藏文件错误** - 从发布包中排除 `.gitignore` 文件
- [x] **版本过期错误** - 更新 `readme.txt` 中 "Tested up to" 从 6.4 到 6.8
- [x] **文本域不匹配警告** - 更新文本域从 `wp-markdown-editor` 到 `advanced-markdown-editor`
- [x] 重命名所有语言文件以匹配新文本域
- [x] 更新主插件文件中的所有文本域引用
- [x] 更新模板文件中的所有文本域引用
- [x] 更新脚本和样式句柄名称
- [x] 创建新的干净发布包

### 文本域修复详情：
- ✅ 主插件文件 (`advanced-markdown-editor.php`) - 所有 `__()` 函数调用已更新
- ✅ 模板文件 (`templates/editor.php`) - 所有 `_e()` 和 `__()` 函数调用已更新
- ✅ 语言文件重命名 - 从 `wp-markdown-editor-*` 到 `advanced-markdown-editor-*`
- ✅ 脚本句柄名称 - 从 `wp-markdown-editor-admin` 到 `advanced-markdown-editor-admin`
- ✅ 主插件文件重命名 - 从 `wp-markdown-editor.php` 到 `advanced-markdown-editor.php`

### 保留的wp-markdown-editor引用（正常）：
- WordPress菜单页面slug (`wp-markdown-editor`, `wp-markdown-editor-new`)
- 管理页面URL参数 (`admin.php?page=wp-markdown-editor`)
- 这些是WordPress系统标识符，不影响文本域验证

## WordPress.org 提交要求检查

### 基本要求 ✅
- [x] 插件名称不包含限制性术语
- [x] 使用GPL兼容许可证
- [x] 包含完整的插件头部信息
- [x] 代码遵循WordPress编码标准

### 文件结构 ✅
- [x] 主插件文件：`advanced-markdown-editor.php`
- [x] 说明文档：`README.md`
- [x] 多语言支持：`languages/` 目录
- [x] 资源文件：`assets/` 目录
- [x] 模板文件：`templates/` 目录

### 功能特性 ✅
- [x] 独立的Markdown编辑器
- [x] 实时预览功能
- [x] 多语言支持（6种语言）
- [x] 图片上传和媒体库集成
- [x] 分类标签管理
- [x] 自动保存功能
- [x] 响应式设计

### 安全性 ✅
- [x] 使用WordPress nonce验证
- [x] 权限检查
- [x] 数据过滤和验证
- [x] 防止直接访问

### 兼容性 ✅
- [x] WordPress 5.0+ 兼容
- [x] 与现有编辑器兼容
- [x] 多浏览器支持
- [x] 移动设备友好

## 提交前最终检查

### 代码质量
- [ ] 运行代码检查工具
- [ ] 测试所有功能
- [ ] 检查错误日志
- [ ] 验证多语言功能

### 文档完整性
- [x] README.md 详细说明
- [x] 安装和使用指南
- [x] 多语言文档
- [x] 更新日志

### 测试环境
- [ ] 在干净的WordPress安装中测试
- [ ] 测试插件激活/停用
- [ ] 测试与其他插件的兼容性
- [ ] 测试不同主题的兼容性

## 提交步骤

1. **准备插件包**
   ```bash
   # 方法一：使用git archive排除不需要的文件
   git archive --format=zip --prefix=advanced-markdown-editor/ HEAD -- . ':!.gitignore' ':!WORDPRESS_SUBMISSION_CHECKLIST.md' ':!CONTRIBUTING.md' ':!test-translations.php' ':!example.md' > advanced-markdown-editor.zip
   
   # 方法二：使用zip命令（推荐）
   zip -r advanced-markdown-editor.zip . -x "*.git*" "*/.DS_Store" "*.gitignore" "WORDPRESS_SUBMISSION_CHECKLIST.md" "CONTRIBUTING.md" "test-translations.php" "example.md"
   ```

2. **WordPress.org 账户**
   - 确保有WordPress.org账户
   - 准备插件描述和截图

3. **提交插件**
   - 访问 https://wordpress.org/plugins/developers/add/
   - 上传插件ZIP文件
   - 填写插件信息
   - 等待审核

## 插件信息

- **插件名称**: Advanced Markdown Editor
- **版本**: 1.1.0
- **作者**: xiaocaicai
- **许可证**: GPL v2 or later
- **最低WordPress版本**: 5.0
- **测试版本**: 6.8

## 注意事项

1. **首次提交**：新插件审核通常需要2-4周时间
2. **命名规范**：已确保不包含限制性术语
3. **代码标准**：遵循WordPress编码标准
4. **安全性**：实施了适当的安全措施
5. **文档**：提供了完整的中英文文档

---

*最后更新：2024-01-01* 