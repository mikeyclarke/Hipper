<?php

declare(strict_types=1);

namespace Hipper\Tests\File\Processor;

use Hipper\File\FileModel;
use Hipper\File\Processor\ConstrainImageDimensionsProcessor;
use Hipper\Image\ImageConstraintsFactory;
use Hipper\Image\ImageResizer;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ConstrainImageDimensionsProcessorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $imageConstraintsFactory;
    private $imageResizer;
    private $processor;

    public function setUp(): void
    {
        $this->imageConstraintsFactory = m::mock(ImageConstraintsFactory::class);
        $this->imageResizer = m::mock(ImageResizer::class);

        $this->processor = new ConstrainImageDimensionsProcessor(
            $this->imageConstraintsFactory,
            $this->imageResizer,
        );
    }

    public function testCanProcessFileWithSupportedImage()
    {
        $file = new FileModel();
        $file->setFileType('image');

        $this->createImageResizerSupportsExpectation([$file], true);

        $result = $this->processor->canProcessFile($file);
        $this->assertTrue($result);
    }

    public function testCanProcessFileWithUnsupportedImage()
    {
        $file = new FileModel();
        $file->setFileType('image');

        $this->createImageResizerSupportsExpectation([$file], false);

        $result = $this->processor->canProcessFile($file);
        $this->assertFalse($result);
    }

    public function testCanProcessFileWithNonImage()
    {
        $file = new FileModel();
        $file->setFileType('document');

        $result = $this->processor->canProcessFile($file);
        $this->assertFalse($result);
    }

    public function testProcess()
    {
        $file = new FileModel();
        $usage = 'foo';
        $file->setUsage($usage);
        $tempPathname = 'bar';

        $maxWidth = 3840;
        $maxHeight = 2400;

        $this->createImageConstraintsFactoryExpectation([$usage], [$maxWidth, $maxHeight]);
        $this->createImageResizerResizeExpectation([$file, $tempPathname, $maxWidth, $maxHeight]);

        $this->processor->process($file, $tempPathname);
    }

    private function createImageConstraintsFactoryExpectation($args, $result)
    {
        $this->imageConstraintsFactory
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createImageResizerResizeExpectation($args)
    {
        $this->imageResizer
            ->shouldReceive('resize')
            ->once()
            ->with(...$args);
    }

    private function createImageResizerSupportsExpectation($args, $result)
    {
        $this->imageResizer
            ->shouldReceive('supports')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
