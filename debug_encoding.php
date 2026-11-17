<?php
$content = file_get_contents('C:\xampp\htdocs\IA_AGENT_STUDIO1\resources\views\admin-dashboard.blade.php');
$needle = "\xC3\x83\xC2\xA9";
$pos = strpos($content, $needle);
echo "Position: $pos\n";
if ($pos !== false) {
    echo "FOUND!\n";
    echo "Context: " . substr($content, $pos-5, 20) . "\n";
} else {
    echo "NOT FOUND\n";
    // Let's check what's actually in the file
    echo "File length: " . strlen($content) . "\n";
    // Check for the characters as they appear
    $pos2 = strpos($content, 'Ã©');
    echo "Position of 'Ã©': $pos2\n";
}