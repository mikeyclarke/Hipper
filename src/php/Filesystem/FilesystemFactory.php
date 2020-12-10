<?php

declare(strict_types=1);

namespace Hipper\Filesystem;

use Symfony\Component\Filesystem\Filesystem;

class FilesystemFactory
{
    public function create(): Filesystem
    {
        return new Filesystem();
    }
}
