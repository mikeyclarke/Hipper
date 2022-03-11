<?php

declare(strict_types=1);

namespace Hipper\Image;

use Hipper\File\FileModel;
use Hipper\Image\ImageResource\ImageResourceFactory;

class ThumbnailCreator
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

    public function create(FileModel $image, string $pathname, int $length): void
    {
        $imageResource = $this->imageResourceFactory->create($pathname, $image->getMimeType());

        $width = $imageResource->getWidth();
        $height = $imageResource->getHeight();

        $type = $this->getType($width, $height);

        list($x, $y, $sourceLength) = $this->getOffsets($type, $width, $height);

        $copyLength = $length;
        if ($length > $sourceLength) {
            $copyLength = $sourceLength;
        }

        $imageResource->copyAndCrop($pathname, $x, $y, $sourceLength, $copyLength);
    }

    private function getOffsets(string $type, int $width, int $height): array
    {
        switch ($type) {
            case 'wide':
                $x = ($width - $height) / 2;
                $y = 0;
                $length = $height;
                break;
            case 'tall':
                $x = 0;
                $y = ($height - $width) / 2;
                $length = $width;
                break;
            case 'square':
                $x = 0;
                $y = 0;
                $length = $width;
                break;
            default:
                throw new \Exception('Unsupported image type');
        }

        return [$x, $y, $length];
    }

    private function getType(int $width, int $height): string
    {
        if ($width > $height) {
            return 'wide';
        }

        if ($height > $width) {
            return 'tall';
        }

        return 'square';
    }
}
