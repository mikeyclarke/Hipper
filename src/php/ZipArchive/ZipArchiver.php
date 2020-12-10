<?php

declare(strict_types=1);

namespace Hipper\ZipArchive;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ZipArchiver
{
    public function recursivelyArchiveDirectory(string $zipPathname, string $directoryPathname): void
    {
        $zip = new ZipArchive();
        $zip->open($zipPathname, ZipArchive::CREATE);

        $nodes = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directoryPathname, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($nodes as $pathname => $file) {
            $relativePath = mb_substr($pathname, mb_strlen($directoryPathname));

            if ($file->isFile()) {
                $zip->addFile($pathname, $relativePath);
            } elseif ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            }
        }

        $zip->close();
    }
}
