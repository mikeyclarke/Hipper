<?php

declare(strict_types=1);

namespace Hipper\Image\ImageResource;

interface ImageResourceInterface
{
    public function getHeight(): int;

    public function getWidth(): int;
}
