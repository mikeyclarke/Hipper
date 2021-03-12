<?php

declare(strict_types=1);

namespace Hipper\File;

use Hipper\File\Exception\UnsupportedFileTypeException;

class FileTypeGuesser
{
    public function guessFromMimeType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        throw new UnsupportedFileTypeException();
    }
}
