<?php
$target = __DIR__ . '/storage/app/public';
$link = __DIR__ . '/public/storage';

if (is_link($link)) {
    echo "Symlink already exists.\n";
} else {
    if (symlink($target, $link)) {
        echo "Symlink created: $link → $target\n";
    } else {
        echo "Failed to create symlink.\n";
    }
}
