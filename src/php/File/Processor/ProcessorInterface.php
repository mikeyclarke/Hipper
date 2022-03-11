<?php

declare(strict_types=1);

namespace Hipper\File\Processor;

use Hipper\File\FileModel;

interface ProcessorInterface
{
    public function canProcessFile(FileModel $file): bool;

    public function process(FileModel $file, string $tempPathname): void;
}
