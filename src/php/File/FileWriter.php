<?php

declare(strict_types=1);

namespace Hipper\File;

use League\Flysystem\Filesystem;

class FileWriter
{
    private Filesystem $filesystem;

    public function __construct(
        Filesystem $filesystem,
    ) {
        $this->filesystem = $filesystem;
    }

    public function write(string $storagePath, string $contents): void
    {
        $this->filesystem->write($storagePath, $contents);
    }
}
