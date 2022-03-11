<?php

declare(strict_types=1);

namespace Hipper\File;

class FileHash
{
    public function get(string $pathname): string
    {
        return md5_file($pathname);
    }

    public function equals(string $pathname, string $hash): bool
    {
        return $hash === $this->get($pathname);
    }
}
