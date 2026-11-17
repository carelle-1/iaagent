<?php

$directory = 'C:\xampp\htdocs\IA_AGENT_STUDIO1\resources\views';

// Read raw bytes and replace
$replacements = [
    // UTF-8 byte sequences for the mojibake characters
    hex2bin('C383C2A9') => 'é',  // Ã©
    hex2bin('C383C2A8') => 'è',  // Ã¨
    hex2bin('C383C2A0') => 'à',  // Ã
    hex2bin('C383C2A7') => 'ç',  // Ã§
    hex2bin('C383C2AA') => 'ê',  // Ãª
    hex2bin('C383C2AB') => 'ë',  // Ã«
    hex2bin('C383C2AE') => 'î',  // Ã®
    hex2bin('C383C2AF') => 'ï',  // Ã¯
    hex2bin('C383C2B4') => 'ô',  // Ã´
    hex2bin('C383C2BB') => 'ù',  // Ã»
    hex2bin('C383C2BC') => 'ü',  // Ã¼
    hex2bin('C383C2B1') => 'ñ',  // Ã±
    hex2bin('C383C289') => 'É',  // Ã‰
    hex2bin('C383C288') => 'È',  // Ãˆ
    hex2bin('C383C280') => 'À',  // Ã€
    hex2bin('C383C287') => 'Ç',  // Ã‡
    hex2bin('C383C28A') => 'Ê',  // ÃŠ
    hex2bin('C383C294') => 'Ô',  // Ã"
    hex2bin('C383C296') => 'Ö',  // Ã–
    hex2bin('C383C299') => 'Ù',  // Ã™
    hex2bin('C383C29C') => 'Ü',  // Ãœ
    // Quotes and special chars - these are already correct UTF-8
    hex2bin('E28094') => '—',   // em dash
    hex2bin('E28093') => '–',   // en dash
    hex2bin('E28099') => '’',   // right single quote
    hex2bin('E2809C') => '“',   // left double quote
    hex2bin('E2809D') => '”',   // right double quote
    hex2bin('E280A2') => '•',   // bullet
    hex2bin('E280A6') => '…',   // ellipsis
    hex2bin('E282AC') => '€',   // euro
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