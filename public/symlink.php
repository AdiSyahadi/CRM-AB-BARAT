<?php

echo "Cek fungsi symlink:\n";
if (function_exists('symlink')) {
    echo "Fungsi symlink() tersedia.\n";
} else {
    echo "Fungsi symlink() TIDAK tersedia.\n";
    exit(1);
}

$targetFolder = __DIR__ . '/../laravel/storage/app/public';
$linkFolder = __DIR__ . '/storage';

if (file_exists($linkFolder)) {
    echo "Link tujuan sudah ada: $linkFolder\n";
} else {
    try {
        symlink($targetFolder, $linkFolder);
        echo "Symlink berhasil dibuat dari:\n$targetFolder\nke\n$linkFolder\n";
    } catch (Error $e) {
        echo "Error saat membuat symlink: " . $e->getMessage() . "\n";
    } catch (Exception $e) {
        echo "Exception saat membuat symlink: " . $e->getMessage() . "\n";
    }
}
