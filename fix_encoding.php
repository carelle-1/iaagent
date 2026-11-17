<?php

$directory = 'C:\xampp\htdocs\IA_AGENT_STUDIO1\resources\views';

$replacements = [
    'Ã©' => 'é',
    'Ã¨' => 'è',
    'Ã ' => 'à',
    'Ã§' => 'ç',
    'Ãª' => 'ê',
    'Ã«' => 'ë',
    'Ã®' => 'î',
    'Ã¯' => 'ï',
    'Ã´' => 'ô',
    'Ã»' => 'ù',
    'Ã¼' => 'ü',
    'Ã±' => 'ñ',
    'Ã‰' => 'É',
    'Ãˆ' => 'È',
    'Ã€' => 'À',
    'Ã‡' => 'Ç',
    'ÃŠ' => 'Ê',
    'Ã" ' => 'Ô',
    'Ã–' => 'Ö',
    'Ã™' => 'Ù',
    'Ãœ' => 'Ü',
    'â€" ' => '—',
    'â€"' => '–',
    'â€™' => '’',
    'â€œ' => '“',
    'â€' => '”',
    'â€¢' => '•',
    'â€¦' => '…',
    'â‚¬' => '€',
];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'blade') {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        $content = strtr($content, $replacements);
        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo "Fixed: {$file->getFilename()}\n";
        }
    }
}

echo "Done!\n";