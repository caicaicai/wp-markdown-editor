<?php
/**
 * Advanced Markdown Editor - 翻译测试脚本
 * 
 * 这个脚本可以帮助您测试插件的多语言功能
 * 使用方法：php test-translations.php
 */

// 检查是否在命令行环境中运行
if (php_sapi_name() !== 'cli') {
    die('此脚本只能在命令行中运行');
}

echo "Advanced Markdown Editor - 翻译测试\n";
echo "=====================================\n\n";

// 测试的语言列表
$languages = [
    'en_US' => 'English',
    'fr_FR' => 'Français', 
    'de_DE' => 'Deutsch',
    'ru_RU' => 'Русский',
    'ja'    => '日本語'
];

// 测试的关键字符串
$test_strings = [
    'Markdown 编辑器' => 'Plugin Name',
    '新建文章' => 'New Post',
    '保存草稿' => 'Save Draft',
    '发布' => 'Publish',
    '分类' => 'Categories',
    '标签' => 'Tags',
    '上传图片' => 'Upload Image',
    '预览' => 'Preview'
];

// 检查语言文件
foreach ($languages as $locale => $name) {
    echo "检查语言: {$name} ({$locale})\n";
    echo str_repeat('-', 40) . "\n";
    
    $po_file = "languages/wp-markdown-editor-{$locale}.po";
    $mo_file = "languages/wp-markdown-editor-{$locale}.mo";
    
    // 检查PO文件
    if (file_exists($po_file)) {
        echo "✓ PO文件存在: {$po_file}\n";
        
        $po_content = file_get_contents($po_file);
        $translated_count = 0;
        
        foreach ($test_strings as $chinese => $english) {
            if (strpos($po_content, "msgid \"{$chinese}\"") !== false) {
                $translated_count++;
            }
        }
        
        echo "✓ 翻译覆盖率: {$translated_count}/" . count($test_strings) . "\n";
        
    } else {
        echo "✗ PO文件不存在: {$po_file}\n";
    }
    
    // 检查MO文件
    if (file_exists($mo_file)) {
        echo "✓ MO文件存在: {$mo_file}\n";
        echo "✓ MO文件大小: " . formatBytes(filesize($mo_file)) . "\n";
    } else {
        echo "✗ MO文件不存在: {$mo_file}\n";
    }
    
    echo "\n";
}

// 检查POT模板文件
echo "检查POT模板文件\n";
echo str_repeat('-', 40) . "\n";

$pot_file = 'languages/wp-markdown-editor.pot';
if (file_exists($pot_file)) {
    echo "✓ POT模板文件存在: {$pot_file}\n";
    
    $pot_content = file_get_contents($pot_file);
    $msgid_count = substr_count($pot_content, 'msgid "');
    
    echo "✓ 字符串总数: {$msgid_count}\n";
    echo "✓ 文件大小: " . formatBytes(filesize($pot_file)) . "\n";
} else {
    echo "✗ POT模板文件不存在: {$pot_file}\n";
}

echo "\n";

// 总结
echo "总结\n";
echo str_repeat('=', 40) . "\n";
echo "支持的语言数量: " . count($languages) . "\n";
echo "核心字符串数量: " . count($test_strings) . "\n";

// 检查是否所有文件都存在
$missing_files = 0;
foreach ($languages as $locale => $name) {
    if (!file_exists("languages/wp-markdown-editor-{$locale}.po")) $missing_files++;
    if (!file_exists("languages/wp-markdown-editor-{$locale}.mo")) $missing_files++;
}

if ($missing_files === 0) {
    echo "✓ 所有语言文件完整\n";
} else {
    echo "✗ 缺少 {$missing_files} 个文件\n";
}

echo "\n使用说明:\n";
echo "1. 在WordPress中设置对应语言即可看到翻译效果\n";
echo "2. 可以使用Poedit等工具编辑PO文件\n";
echo "3. 修改PO文件后需要重新生成MO文件\n";
echo "4. 详细说明请查看 MULTILINGUAL.md 文档\n";

/**
 * 格式化文件大小
 */
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}
?> 