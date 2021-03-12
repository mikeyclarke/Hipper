<?php

declare(strict_types=1);

namespace Hipper\Image\ImageResource;

trait ImageResourceTrait
{
    public function getHeight(): int
    {
        return imagesy($this->image);
    }

    public function getWidth(): int
    {
        return imagesx($this->image);
    }
}
