<?php

declare(strict_types=1);

namespace Hipper\Image\ImageResource;

use Hipper\Image\ImageResource\Exception\ImageResourceCreateException;
use Hipper\Image\ImageResource\ImageResourceInterface;
use Hipper\Image\ImageResource\ImageResourceTrait;

class JpegImageResource implements ImageResourceInterface
{
    use ImageResourceTrait;

    protected resource $image;

    public function __construct(
        string $pathname
    ) {
        $result = imagecreatefromjpeg($pathname);
        if (false === $result) {
            throw new ImageResourceCreateException();
        }
        $this->image = $result;
    }
}
