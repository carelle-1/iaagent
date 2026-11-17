<?php
$directory = 'C:\xampp\htdocs\IA_AGENT_STUDIO1\resources\views';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
$fixed = 0;
foreach ($iterator as $file) {
    echo "Checking: " . $file->getFilename() . " - Extension: " . $file->getExtension() . "\n";
    if ($file->isFile() && $file->getExtension() === 'blade') {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        
        $content = str_replace("\xC3\x83\xC2\xA9", "\xC3\xA9", $content);
        $content = str_replace("\xC3\x83\xC2\xA8", "\xC3\xA8", $content);
        $content = str_replace("\xC3\x83\xC2\xA0", "\xC3\xA0", $content);
        $content = str_replace("\xC3\x83\xC2\xA7", "\xC3\xA7", $content);
        $content = str_replace("\xC3\x83\xC2\xAA", "\xC3\xAA", $content);
        $content = str_replace("\xC3\x83\xC2\xAB", "\xC3\xAB", $content);
        $content = str_replace("\xC3\x83\xC2\xAE", "\xC3\xAE", $content);
        $content = str_replace("\xC3\x83\xC2\xAF", "\xC3\xAF", $content);
        $content = str_replace("\xC3\x83\xC2\xB4", "\xC3\xB4", $content);
        $content = str_replace("\xC3\x83\xC2\xBB", "\xC3\xB9", $content);
        $content = str_replace("\xC3\x83\xC2\xBC", "\xC3\xBC", $content);
        $content = str_replace("\xC3\x83\xC2\xB1", "\xC3\xB1", $content);
        $content = str_replace("\xC3\x83\xC2\x89", "\xC3\x89", $content);
        $content = str_replace("\xC3\x83\xC2\x88", "\xC3\x88", $content);
        $content = str_replace("\xC3\x83\xC2\x80", "\xC3\x80", $content);
        $content = str_replace("\xC3\x83\xC2\x87", "\xC3\x87", $content);
        $content = str_replace("\xC3\x83\xC2\x8A", "\xC3\x8A", $content);
        $content = str_replace("\xC3\x83\xC2\x94", "\xC3\x94", $content);
        $content = str_replace("\xC3\x83\xC2\x96", "\xC3\x96", $content);
        $content = str_replace("\xC3\x83\xC2\x99", "\xC3\x99", $content);
        $content = str_replace("\xC3\x83\xC2\x9C", "\xC3\x9C", $content);
        
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