<?php

declare(strict_types=1);

namespace Hipper\Tests\Image;

use Hipper\File\FileModel;
use Hipper\Image\ImageResizer;
use Hipper\Image\ImageResource\ImageResourceFactory;
use Hipper\Image\ImageResource\ImageResourceInterface;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ImageResizerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $imageResourceFactory;
    private $imageResizer;
    private $imageResource;

    public function setUp(): void
    {
        $this->imageResourceFactory = m::mock(ImageResourceFactory::class);

        $this->imageResizer = new ImageResizer(
            $this->imageResourceFactory
        );

        $this->imageResource = m::mock(ImageResourceInterface::class);
    }

    public function testResizeWithImageSmallerThanConstraints()
    {
        $image = new FileModel();
        $mimeType = 'image/jpeg';
        $image->setMimeType($mimeType);
        $pathname = 'foo';
        $maxWidth = 3840;
        $maxHeight = 2400;

        $width = 1440;
        $height = 900;

        $this->createImageResourceFactoryExpectation([$pathname, $mimeType]);
        $this->createImageResourceGetWidthExpectation($width);
        $this->createImageResourceGetHeightExpectation($height);

        $this->imageResizer->resize($image, $pathname, $maxWidth, $maxHeight);
    }

    public function testResizeWithImageThatWouldBeScaledBelowMinimumDimension()
    {
        $image = new FileModel();
        $mimeType = 'image/jpeg';
        $image->setMimeType($mimeType);
        $pathname = 'foo';
        $maxWidth = 1200;
        $maxHeight = 1200;

        $width = 3500;
        $height = 140;

        $this->createImageResourceFactoryExpectation([$pathname, $mimeType]);
        $this->createImageResourceGetWidthExpectation($width);
        $this->createImageResourceGetHeightExpectation($height);

        $this->imageResizer->resize($image, $pathname, $maxWidth, $maxHeight);
    }

    public function testResizeWithSquareImage()
    {
        $image = new FileModel();
        $mimeType = 'image/jpeg';
        $image->setMimeType($mimeType);
        $pathname = 'foo';
        $maxWidth = 1440;
        $maxHeight = 900;

        $width = 3500;
        $height = 3500;

        $this->createImageResourceFactoryExpectation([$pathname, $mimeType]);
        $this->createImageResourceGetWidthExpectation($width);
        $this->createImageResourceGetHeightExpectation($height);
        $this->createImageResourceCopyAndScaleExpectation([$pathname, $maxHeight, $maxHeight]);

        $this->imageResizer->resize($image, $pathname, $maxWidth, $maxHeight);
    }

    public function testResizeWithTallImage()
    {
        $image = new FileModel();
        $mimeType = 'image/jpeg';
        $image->setMimeType($mimeType);
        $pathname = 'foo';
        $maxWidth = 1440;
        $maxHeight = 900;

        $width = 1000;
        $height = 3500;

        $this->createImageResourceFactoryExpectation([$pathname, $mimeType]);
        $this->createImageResourceGetWidthExpectation($width);
        $this->createImageResourceGetHeightExpectation($height);
        $this->createImageResourceCopyAndScaleExpectation([$pathname, 257, 900]);

        $this->imageResizer->resize($image, $pathname, $maxWidth, $maxHeight);
    }

    public function testResizeWithWideImage()
    {
        $image = new FileModel();
        $mimeType = 'image/jpeg';
        $image->setMimeType($mimeType);
        $pathname = 'foo';
        $maxWidth = 1440;
        $maxHeight = null;

        $width = 3500;
        $height = 900;

        $this->createImageResourceFactoryExpectation([$pathname, $mimeType]);
        $this->createImageResourceGetWidthExpectation($width);
        $this->createImageResourceGetHeightExpectation($height);
        $this->createImageResourceCopyAndScaleExpectation([$pathname, 1440, 370]);

        $this->imageResizer->resize($image, $pathname, $maxWidth, $maxHeight);
    }

    private function createImageResourceFactoryExpectation($args)
    {
        $this->imageResourceFactory
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($this->imageResource);
    }

    private function createImageResourceGetWidthExpectation($result)
    {
        $this->imageResource
            ->shouldReceive('getWidth')
            ->once()
            ->andReturn($result);
    }

    private function createImageResourceGetHeightExpectation($result)
    {
        $this->imageResource
            ->shouldReceive('getHeight')
            ->once()
            ->andReturn($result);
    }

    private function createImageResourceCopyAndScaleExpectation($args)
    {
        $this->imageResource
            ->shouldReceive('copyAndScale')
            ->once()
            ->with(...$args);
    }
}
