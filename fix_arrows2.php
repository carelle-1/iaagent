<?php
$directory = 'C:\xampp\htdocs\IA_AGENT_STUDIO1\resources\views';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
$fixed = 0;
foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        
        // The mojibake "â–¾" is the UTF-8 encoding of the literal characters â + ‚ + ¾
        // Which is bytes: C3 A2 E2 80 9A C2 BE
        // This should be replaced with the actual ▾ character (UTF-8: E2 96 BE)
        
        $content = str_replace("\xC3\xA2\xE2\x80\x9A\xC2\xBE", "\xE2\x96\xBE", $content); // â–¾ -> ▾
        $content = str_replace("\xC3\xA2\xE2\x80\x9A\xC2\xBC", "\xE2\x96\xBC", $content); // â–¼ -> ▼
        $content = str_replace("\xC3\xA2\xE2\x80\x9A\xC2\xB2", "\xE2\x96\xB2", $content); // â–² -> ▲
        $content = str_replace("\xC3\xA2\xE2\x80\x9A\xC2\xB6", "\xE2\x96\xB6", $content); // â–¶ -> ▶
        $content = str_replace("\xC3\xA2\xE2\x80\x9A\xC2\xB4", "\xE2\x96\xB4", $content); // â–´ -> ◀
        
        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo "FIXED: {$file->getFilename()}\n";
            $fixed++;
        } else {
            echo "No change: {$file->getFilename()}\n";
        }
    }
}

echo "Total fixed: $fixed\n";