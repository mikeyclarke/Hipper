<?php

declare(strict_types=1);

namespace Hipper\Image;

use Hipper\File\FileModel;
use Hipper\Image\ImageResource\ImageResourceFactory;

class ImageResizer
{
    private const MIN_DIMENSION = 50;
    private const SUPPORTED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
    ];

    public function __construct(
        private ImageResourceFactory $imageResourceFactory,
    ) {}

    public function supports(FileModel $image): bool
    {
        return in_array($image->getMimeType(), self::SUPPORTED_MIME_TYPES);
    }

    public function resize(FileModel $image, string $pathname, ?int $maxWidth = null, ?int $maxHeight = null): void
    {
        if (null === $maxWidth && null === $maxHeight) {
            throw new \Exception('One of max width and max height must be provided');
        }

        $imageResource = $this->imageResourceFactory->create($pathname, $image->getMimeType());

        $width = $imageResource->getWidth();
        $height = $imageResource->getHeight();

        if (!$this->imageNeedsResizing($width, $height, $maxWidth, $maxHeight)) {
            return;
        }

        list($newWidth, $newHeight) = $this->getScaleDimensions($width, $height, $maxWidth, $maxHeight);
        if ($newWidth < self::MIN_DIMENSION || $newHeight < self::MIN_DIMENSION) {
            return;
        }

        $imageResource->copyAndScale($pathname, $newWidth, $newHeight);
    }

    private function imageNeedsResizing(int $width, int $height, ?int $maxWidth, ?int $maxHeight): bool
    {
        if (null !== $maxWidth && $width > $maxWidth) {
            return true;
        }

        if (null !== $maxHeight && $height > $maxHeight) {
            return true;
        }

        return false;
    }

    private function getScaleDimensions(int $width, int $height, ?int $maxWidth, ?int $maxHeight): array
    {
        $smallestDimension = $this->getSmallestDimension($maxWidth, $maxHeight);
        $widthScale = null;
        $heightScale = null;

        if ($width === $height) {
            return [$smallestDimension, $smallestDimension];
        }

        if (null !== $maxWidth && $width > $maxWidth) {
            $widthScale = $width / $maxWidth;
        }

        if (null !== $maxHeight && $height > $maxHeight) {
            $heightScale = $height / $maxHeight;
        }

        $scale = max($widthScale, $heightScale);

        $newWidth = (int) round($width / $scale);
        $newHeight = (int) round($height / $scale);

        return [
            $newWidth,
            $newHeight,
        ];
    }

    private function getSmallestDimension(?int $maxWidth, ?int $maxHeight): int
    {
        if (null !== $maxWidth && null !== $maxHeight) {
            return min($maxWidth, $maxHeight);
        }

        if (null === $maxWidth) {
            return $maxHeight;
        }

        return $maxWidth;
    }
}
