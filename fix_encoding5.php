<?php
$directory = 'C:\xampp\htdocs\IA_AGENT_STUDIO1\resources\views';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
$fixed = 0;
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'blade') {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        
        // Direct byte replacements
        $content = str_replace("\xC3\x83\xC2\xA9", "\xC3\xA9", $content); // Ã© -> é
        $content = str_replace("\xC3\x83\xC2\xA8", "\xC3\xA8", $content); // Ã¨ -> è
        $content = str_replace("\xC3\x83\xC2\xA0", "\xC3\xA0", $content); // Ã  -> à
        $content = str_replace("\xC3\x83\xC2\xA7", "\xC3\xA7", $content); // Ã§ -> ç
        $content = str_replace("\xC3\x83\xC2\xAA", "\xC3\xAA", $content); // Ãª -> ê
        $content = str_replace("\xC3\x83\xC2\xAB", "\xC3\xAB", $content); // Ã« -> ë
        $content = str_replace("\xC3\x83\xC2\xAE", "\xC3\xAE", $content); // Ã® -> î
        $content = str_replace("\xC3\x83\xC2\xAF", "\xC3\xAF", $content); // Ã¯ -> ï
        $content = str_replace("\xC3\x83\xC2\xB4", "\xC3\xB4", $content); // Ã´ -> ô
        $content = str_replace("\xC3\x83\xC2\xBB", "\xC3\xB9", $content); // Ã» -> ù
        $content = str_replace("\xC3\x83\xC2\xBC", "\xC3\xBC", $content); // Ã¼ -> ü
        $content = str_replace("\xC3\x83\xC2\xB1", "\xC3\xB1", $content); // Ã± -> ñ
        $content = str_replace("\xC3\x83\xC2\x89", "\xC3\x89", $content); // Ã‰ -> É
        $content = str_replace("\xC3\x83\xC2\x88", "\xC3\x88", $content); // Ãˆ -> È
        $content = str_replace("\xC3\x83\xC2\x80", "\xC3\x80", $content); // Ã€ -> À
        $content = str_replace("\xC3\x83\xC2\x87", "\xC3\x87", $content); // Ã‡ -> Ç
        $content = str_replace("\xC3\x83\xC2\x8A", "\xC3\x8A", $content); // ÃŠ -> Ê
        $content = str_replace("\xC3\x83\xC2\x94", "\xC3\x94", $content); // Ã" -> Ô
        $content = str_replace("\xC3\x83\xC2\x96", "\xC3\x96", $content); // Ã– -> Ö
        $content = str_replace("\xC3\x83\xC2\x99", "\xC3\x99", $content); // Ã™ -> Ù
        $content = str_replace("\xC3\x83\xC2\x9C", "\xC3\x9C", $content); // Ãœ -> Ü
        
        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo "Fixed: {$file->getFilename()}\n";
            $fixed++;
        }
    }
}

echo "Total fixed: $fixed\n";