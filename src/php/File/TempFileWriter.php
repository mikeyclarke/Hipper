<?php

declare(strict_types=1);

namespace Hipper\File;

class TempFileWriter
{
    public function write(string $filename, string $contents): string
    {
        $pathname = sprintf('%s/%s', sys_get_temp_dir(), $filename);
        file_put_contents($pathname, $contents);
        return $pathname;
    }

    public function remove(string $pathname): void
    {
        unlink($pathname);
    }
}
