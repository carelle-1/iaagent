<?php

$directory = 'C:\xampp\htdocs\IA_AGENT_STUDIO1\resources\views';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
$fixed = 0;
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'blade') {
        $bytes = file_get_contents($file->getPathname());
        
        // Replace the specific byte sequences
        $original = $bytes;
        
        // Ã© (C3 83 C2 A9) -> é (C3 A9)
        $bytes = str_replace("\xC3\x83\xC2\xA9", "\xC3\xA9", $bytes);
        // Ã¨ (C3 83 C2 A8) -> è (C3 A8)
        $bytes = str_replace("\xC3\x83\xC2\xA8", "\xC3\xA8", $bytes);
        // Ã  (C3 83 C2 A0) -> à (C3 A0)
        $bytes = str_replace("\xC3\x83\xC2\xA0", "\xC3\xA0", $bytes);
        // Ã§ (C3 83 C2 A7) -> ç (C3 A7)
        $bytes = str_replace("\xC3\x83\xC2\xA7", "\xC3\xA7", $bytes);
        // Ãª (C3 83 C2 AA) -> ê (C3 AA)
        $bytes = str_replace("\xC3\x83\xC2\xAA", "\xC3\xAA", $bytes);
        // Ã« (C3 83 C2 AB) -> ë (C3 AB)
        $bytes = str_replace("\xC3\x83\xC2\xAB", "\xC3\xAB", $bytes);
        // Ã® (C3 83 C2 AE) -> î (C3 AE)
        $bytes = str_replace("\xC3\x83\xC2\xAE", "\xC3\xAE", $bytes);
        // Ã¯ (C3 83 C2 AF) -> ï (C3 AF)
        $bytes = str_replace("\xC3\x83\xC2\xAF", "\xC3\xAF", $bytes);
        // Ã´ (C3 83 C2 B4) -> ô (C3 B4)
        $bytes = str_replace("\xC3\x83\xC2\xB4", "\xC3\xB4", $bytes);
        // Ã» (C3 83 C2 BB) -> ù (C3 B9)
        $bytes = str_replace("\xC3\x83\xC2\xBB", "\xC3\xB9", $bytes);
        // Ã¼ (C3 83 C2 BC) -> ü (C3 BC)
        $bytes = str_replace("\xC3\x83\xC2\xBC", "\xC3\xBC", $bytes);
        // Ã± (C3 83 C2 B1) -> ñ (C3 B1)
        $bytes = str_replace("\xC3\x83\xC2\xB1", "\xC3\xB1", $bytes);
        // Ã‰ (C3 83 C2 89) -> É (C3 89)
        $bytes = str_replace("\xC3\x83\xC2\x89", "\xC3\x89", $bytes);
        // Ãˆ (C3 83 C2 88) -> È (C3 88)
        $bytes = str_replace("\xC3\x83\xC2\x88", "\xC3\x88", $bytes);
        // Ã€ (C3 83 C2 80) -> À (C3 80)
        $bytes = str_replace("\xC3\x83\xC2\x80", "\xC3\x80", $bytes);
        // Ã‡ (C3 83 C2 87) -> Ç (C3 87)
        $bytes = str_replace("\xC3\x83\xC2\x87", "\xC3\x87", $bytes);
        // ÃŠ (C3 83 C2 8A) -> Ê (C3 8A)
        $bytes = str_replace("\xC3\x83\xC2\x8A", "\xC3\x8A", $bytes);
        // Ã" (C3 83 C2 94) -> Ô (C3 94)
        $bytes = str_replace("\xC3\x83\xC2\x94", "\xC3\x94", $bytes);
        // Ã– (C3 83 C2 96) -> Ö (C3 96)
        $bytes = str_replace("\xC3\x83\xC2\x96", "\xC3\x96", $bytes);
        // Ã™ (C3 83 C2 99) -> Ù (C3 99)
        $bytes = str_replace("\xC3\x83\xC2\x99", "\xC3\x99", $bytes);
        // Ãœ (C3 83 C2 9C) -> Ü (C3 9C)
        $bytes = str_replace("\xC3\x83\xC2\x9C", "\xC3\x9C", $bytes);
        
        if ($bytes !== $original) {
            file_put_contents($file->getPathname(), $bytes);
            echo "Fixed: {$file->getFilename()}\n";
            $fixed++;
        }
    }
}

echo "Total fixed: $fixed\n";