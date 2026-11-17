<?php

$directory = 'C:\xampp\htdocs\IA_AGENT_STUDIO1\resources\views';

// The mojibake patterns (UTF-8 bytes that represent the misinterpreted characters)
$replacements = [
    // Common French characters that got double-encoded
    "\xC3\x83\xC2\xA9" => 'é',  // Ã© -> é
    "\xC3\x83\xC2\xA8" => 'è',  // Ã¨ -> è
    "\xC3\x83\xC2\xA0" => 'à',  // Ã  -> à
    "\xC3\x83\xC2\xA7" => 'ç',  // Ã§ -> ç
    "\xC3\x83\xC2\xAA" => 'ê',  // Ãª -> ê
    "\xC3\x83\xC2\xAB" => 'ë',  // Ã« -> ë
    "\xC3\x83\xC2\xAE" => 'î',  // Ã® -> î
    "\xC3\x83\xC2\xAF" => 'ï',  // Ã¯ -> ï
    "\xC3\x83\xC2\xB4" => 'ô',  // Ã´ -> ô
    "\xC3\x83\xC2\xBB" => 'ù',  // Ã» -> ù
    "\xC3\x83\xC2\xBC" => 'ü',  // Ã¼ -> ü
    "\xC3\x83\xC2\xB1" => 'ñ',  // Ã± -> ñ
    "\xC3\x83\xC2\x89" => 'É',  // Ã‰ -> É
    "\xC3\x83\xC2\x88" => 'È',  // Ãˆ -> È
    "\xC3\x83\xC2\x80" => 'À',  // Ã€ -> À
    "\xC3\x83\xC2\x87" => 'Ç',  // Ã‡ -> Ç
    "\xC3\x83\xC2\x8A" => 'Ê',  // ÃŠ -> Ê
    "\xC3\x83\xC2\x94" => 'Ô',  // Ã" -> Ô
    "\xC3\x83\xC2\x96" => 'Ö',  // Ã– -> Ö
    "\xC3\x83\xC2\x99" => 'Ù',  // Ã™ -> Ù
    "\xC3\x83\xC2\x9C" => 'Ü',  // Ãœ -> Ü
    // Quotes and special chars
    "\xE2\x80\x94" => '—',   // â€" -> —
    "\xE2\x80\x93" => '–',   // â€" -> –
    "\xE2\x80\x99" => '’',   // â€™ -> ’
    "\xE2\x80\x9C" => '“',   // â€œ -> “
    "\xE2\x80\x9D" => '”',   // â€ -> ”
    "\xE2\x80\xA2" => '•',   // â€¢ -> •
    "\xE2\x80\xA6" => '…',   // â€¦ -> …
    "\xE2\x82\xAC" => '€',   // â‚¬ -> €
];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
$fixed = 0;
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'blade') {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        $content = strtr($content, $replacements);
        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo "Fixed: {$file->getFilename()}\n";
            $fixed++;
        }
    }
}

echo "Total fixed: $fixed\n";