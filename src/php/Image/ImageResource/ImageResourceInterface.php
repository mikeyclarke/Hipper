<?php

declare(strict_types=1);

namespace Hipper\Image\ImageResource;

interface ImageResourceInterface
{
    public function getHeight(): int;

    public function getWidth(): int;

    public function copyAndScale(string $outputPathname, int $width, int $height): void;

    public function copyAndCrop(
        string $outputPathname,
        int $sourceXOffset,
        int $sourceYOffset,
        int $sourceLength,
        int $copyLength
    ): void;
}
