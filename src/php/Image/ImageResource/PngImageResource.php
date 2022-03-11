<?php

declare(strict_types=1);

namespace Hipper\Image\ImageResource;

use GdImage;
use Hipper\Image\ImageResource\Exception\ImageResourceCreateException;
use Hipper\Image\ImageResource\Exception\ImageResourceOutputException;
use Hipper\Image\ImageResource\Exception\ImageResourceResampleException;
use Hipper\Image\ImageResource\ImageResourceInterface;
use Hipper\Image\ImageResource\ImageResourceTrait;

class PngImageResource implements ImageResourceInterface
{
    use ImageResourceTrait;

    protected GdImage $image;

    public function __construct(
        string $pathname
    ) {
        $result = imagecreatefrompng($pathname);
        if (false === $result) {
            throw new ImageResourceCreateException();
        }
        $this->image = $result;
    }

    public function copyAndScale(string $outputPathname, int $width, int $height): void
    {
        $resampled = imagecreatetruecolor($height, $width);

        imagecolortransparent($resampled, imagecolorallocatealpha($resampled, 0, 0, 0, 127));
        imagealphablending($resampled, false);
        imagesavealpha($resampled, true);

        $currWidth = $this->getWidth();
        $currHeight = $this->getHeight();

        $result = imagecopyresampled($resampled, $this->image, 0, 0, 0, 0, $width, $height, $currWidth, $currHeight);
        if (false === $result) {
            throw new ImageResourceResampleException();
        }

        $result = imagepng($resampled, $outputPathname, 100);
        if (false === $result) {
            throw new ImageResourceOutputException();
        }
    }

    public function copyAndCrop(
        string $outputPathname,
        int $sourceXOffset,
        int $sourceYOffset,
        int $sourceLength,
        int $copyLength
    ): void {
        $resampled = imagecreatetruecolor($copyLength, $copyLength);

        imagecolortransparent($resampled, imagecolorallocatealpha($resampled, 0, 0, 0, 127));
        imagealphablending($resampled, false);
        imagesavealpha($resampled, true);

        $currWidth = $this->getWidth();
        $currHeight = $this->getHeight();

        $result = imagecopyresampled(
            $resampled,
            $this->image,
            0,
            0,
            $sourceXOffset,
            $sourceYOffset,
            $copyLength,
            $copyLength,
            $sourceLength,
            $sourceLength
        );
        if (false === $result) {
            throw new ImageResourceResampleException();
        }

        $result = imagepng($resampled, $outputPathname, 100);
        if (false === $result) {
            throw new ImageResourceOutputException();
        }
    }
}
