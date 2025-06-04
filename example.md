# Advanced Markdown Editor 示例

这是一个Markdown编辑器的示例文档，展示了各种Markdown语法的使用方法。

## 文本格式

这是普通文本。

**这是粗体文本**

*这是斜体文本*

***这是粗斜体文本***

~~这是删除线文本~~

`这是内联代码`

## 标题

# 一级标题
## 二级标题  
### 三级标题
#### 四级标题
##### 五级标题
###### 六级标题

## 列表

### 无序列表
- 第一项
- 第二项
  - 子项目1
  - 子项目2
- 第三项

### 有序列表
1. 第一步
2. 第二步
   1. 子步骤A
   2. 子步骤B
3. 第三步

## 链接和图片

### 链接
[WordPress官网](https://wordpress.org)

[带标题的链接](https://wordpress.org "WordPress官方网站")

### 图片
![WordPress Logo](https://s.w.org/style/images/wp-header-logo.png)

## 引用

> 这是一个简单的引用。

> 这是一个多行引用。
> 
> 它可以跨越多行，
> 并且支持其他Markdown语法，比如**粗体**和*斜体*。

## 代码

### 内联代码
使用 `echo "Hello World"` 命令来输出文本。

### 代码块
```php
<?php
function hello_world() {
    echo "Hello, WordPress!";
}

hello_world();
?>
```

```javascript
// JavaScript示例
function markdownEditor() {
    console.log('欢迎使用Markdown编辑器！');
    
    const editor = document.getElementById('markdown-editor');
    editor.addEventListener('input', updatePreview);
}
```

```css
/* CSS样式示例 */
.markdown-editor {
    font-family: 'Monaco', 'Consolas', monospace;
    font-size: 14px;
    line-height: 1.6;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
}
```

## 表格

| 功能 | 描述 | 状态 |
|------|------|------|
| 实时预览 | 边写边看效果 | ✅ 已完成 |
| 语法高亮 | 代码语法着色 | ✅ 已完成 |
| 自动保存 | 防止内容丢失 | ✅ 已完成 |
| 导出功能 | 导出多种格式 | 🚧 开发中 |

## 分隔线

---

## 任务列表

- [x] 创建Markdown编辑器
- [x] 添加实时预览功能
- [x] 实现自动保存
- [ ] 添加插件和扩展
- [ ] 支持协同编辑

## 数学公式

内联公式：$E = mc^2$

块级公式：
$$
\sum_{i=1}^{n} x_i = x_1 + x_2 + \cdots + x_n
$$

## 脚注

这里有一个脚注引用[^1]。

这里是另一个脚注[^2]。

[^1]: 这是第一个脚注的内容。
[^2]: 这是第二个脚注的内容，可以包含**格式化文本**。

## 特殊语法

### 高亮文本
==这是高亮文本==

### 下标和上标
H~2~O 是水的化学式。

这是上标示例：x^2^ + y^2^ = r^2^

### 键盘按键
按 <kbd>Ctrl</kbd> + <kbd>S</kbd> 保存文档。

## HTML标签

Markdown也支持HTML标签：

<div style="background-color: #f0f0f0; padding: 10px; border-radius: 5px;">
这是一个HTML div标签，可以添加自定义样式。
</div>

<details>
<summary>点击展开详细信息</summary>
这里是折叠的内容，点击上面的标题可以展开或收起。

支持**Markdown语法**和`代码`。
</details>

---

这个示例展示了Markdown编辑器支持的大部分语法。您可以复制这些内容到编辑器中试验各种效果！ 