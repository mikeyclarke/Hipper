<?php

declare(strict_types=1);

namespace Hipper\File\Processor;

use Hipper\File\FileModel;
use Hipper\File\Processor\ProcessorInterface;
use Hipper\Image\ImageConstraintsFactory;
use Hipper\Image\ImageResizer;

class ConstrainImageDimensionsProcessor implements ProcessorInterface
{
    public function __construct(
        private ImageConstraintsFactory $imageConstraintsFactory,
        private ImageResizer $imageResizer,
    ) {}

    public function canProcessFile(FileModel $file): bool
    {
        return $file->isImage() && $this->imageResizer->supports($file);
    }

    public function process(FileModel $file, string $tempPathname): void
    {
        list($maxWidth, $maxHeight) = $this->imageConstraintsFactory->create($file->getUsage());

        $this->imageResizer->resize($file, $tempPathname, $maxWidth, $maxHeight);
    }
}
