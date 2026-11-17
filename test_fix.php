<?php
$file = 'C:\xampp\htdocs\IA_AGENT_STUDIO1\resources\views\admin-dashboard.blade.php';
$content = file_get_contents($file);
$original = $content;

echo "Original length: " . strlen($content) . "\n";

// Check before
$pos = strpos($content, "\xC3\x83\xC2\xA9");
echo "Before - Position of Ã©: $pos\n";

$content = str_replace("\xC3\x83\xC2\xA9", "\xC3\xA9", $content);

// Check after
$pos2 = strpos($content, "\xC3\x83\xC2\xA9");
echo "After - Position of Ã©: $pos2\n";

$pos3 = strpos($content, "\xC3\xA9");
echo "After - Position of é: $pos3\n";

echo "Content changed: " . ($content !== $original ? "YES" : "NO") . "\n";
echo "New length: " . strlen($content) . "\n";

// Write test
file_put_contents($file . '.test', $content);
echo "Test file written\n";