<?php

declare(strict_types=1);

namespace Hipper\Image\ImageResource;

use Hipper\Image\ImageResource\Exception\UnsupportedImageTypeException;
use Hipper\Image\ImageResource\ImageResourceInterface;

class ImageResourceFactory
{
    public function create(string $pathname, string $mimeType): ImageResourceInterface
    {
        switch ($mimeType) {
            case 'image/jpeg':
                return new JpegImageResource($pathname);
            case 'image/png':
                return new PngImageResource($pathname);
            default:
                throw new UnsupportedImageTypeException();
        }
    }
}
