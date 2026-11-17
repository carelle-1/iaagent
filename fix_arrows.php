<?php
$directory = 'C:\xampp\htdocs\IA_AGENT_STUDIO1\resources\views';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
$fixed = 0;
foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        
        // Fix â–¾ (E2 96 BE) -> ▾ (E2 96 BE) - actually this IS the correct UTF-8 for ▾
        // But if it's displaying as â–¾, it means the UTF-8 bytes were misinterpreted as Latin-1/Windows-1252
        // The actual fix: the file should contain the literal ▾ character, not the mojibake
        
        // Common mojibake for box-drawing chars and arrows:
        $content = str_replace("\xE2\x96\xBE", "▾", $content); // ▾ (DOWN TRIANGLE)
        $content = str_replace("\xE2\x96\xBC", "▼", $content); // ▼ (BLACK DOWN-POINTING TRIANGLE)
        $content = str_replace("\xE2\x96\xB2", "▲", $content); // ▲ (BLACK UP-POINTING TRIANGLE)
        $content = str_replace("\xE2\x96\xB6", "▶", $content); // ▶ (BLACK RIGHT-POINTING TRIANGLE)
        $content = str_replace("\xE2\x96\xB4", "◀", $content); // ◀ (BLACK LEFT-POINTING TRIANGLE)
        $content = str_replace("\xE2\x97\x8F", "▏", $content); // ▏ (LEFT ONE EIGHTH BLOCK)
        $content = str_replace("\xE2\x97\x8E", "▎", $content); // ▎ (LEFT ONE QUARTER BLOCK)
        $content = str_replace("\xE2\x97\x8D", "▍", $content); // ▍ (LEFT THREE EIGHTHS BLOCK)
        $content = str_replace("\xE2\x97\x8C", "▌", $content); // ▌ (LEFT HALF BLOCK)
        $content = str_replace("\xE2\x97\x8B", "▋", $content); // ▋ (LEFT FIVE EIGHTHS BLOCK)
        $content = str_replace("\xE2\x97\x8A", "▊", $content); // ▊ (LEFT THREE QUARTERS BLOCK)
        $content = str_replace("\xE2\x97\x89", "▉", $content); // ▉ (LEFT SEVEN EIGHTHS BLOCK)
        $content = str_replace("\xE2\x97\x88", "█", $content); // █ (FULL BLOCK)
        $content = str_replace("\xE2\x94\x80", "─", $content); // ─ (BOX DRAWINGS LIGHT HORIZONTAL)
        $content = str_replace("\xE2\x94\x82", "│", $content); // │ (BOX DRAWINGS LIGHT VERTICAL)
        $content = str_replace("\xE2\x94\x8C", "┌", $content); // ┌ (BOX DRAWINGS LIGHT DOWN AND RIGHT)
        $content = str_replace("\xE2\x94\x90", "┐", $content); // ┐ (BOX DRAWINGS LIGHT DOWN AND LEFT)
        $content = str_replace("\xE2\x94\x94", "└", $content); // └ (BOX DRAWINGS LIGHT UP AND RIGHT)
        $content = str_replace("\xE2\x94\x98", "┘", $content); // ┘ (BOX DRAWINGS LIGHT UP AND LEFT)
        $content = str_replace("\xE2\x94\x9C", "├", $content); // ├ (BOX DRAWINGS LIGHT VERTICAL AND RIGHT)
        $content = str_replace("\xE2\x94\xA4", "┤", $content); // ┤ (BOX DRAWINGS LIGHT VERTICAL AND LEFT)
        $content = str_replace("\xE2\x94\xAC", "┬", $content); // ┬ (BOX DRAWINGS LIGHT DOWN AND HORIZONTAL)
        $content = str_replace("\xE2\x94\xB4", "┴", $content); // ┴ (BOX DRAWINGS LIGHT UP AND HORIZONTAL)
        $content = str_replace("\xE2\x94\xBC", "┼", $content); // ┼ (BOX DRAWINGS LIGHT VERTICAL AND HORIZONTAL)
        
        // Also fix the mojibake versions (if they were double-encoded)
        $content = str_replace("\xC3\xA2\xE2\x80\x9A\xC2\xBE", "▾", $content); // â–¾ -> ▾
        $content = str_replace("\xC3\xA2\xE2\x80\x9A\xC2\xBC", "▼", $content); // â–¼ -> ▼
        $content = str_replace("\xC3\xA2\xE2\x80\x9A\xC2\xB2", "▲", $content); // â–² -> ▲
        $content = str_replace("\xC3\xA2\xE2\x80\x9A\xC2\xB6", "▶", $content); // â–¶ -> ▶
        
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