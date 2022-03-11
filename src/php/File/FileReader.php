<?php

declare(strict_types=1);

namespace Hipper\File;

use Hipper\File\Exception\FileReadException;

class FileReader
{
    public function read(string $pathname): string
    {
        $result = file_get_contents($pathname);
        if (false === $result) {
            throw new FileReadException();
        }
        return $result;
    }
}
