<?php

declare(strict_types=1);

namespace Hipper\Tests\File;

use Hipper\File\Exception\UnsupportedFileTypeException;
use Hipper\File\FileTypeGuesser;
use PHPUnit\Framework\TestCase;

class FileTypeGuesserTest extends TestCase
{
    private $fileTypeGuesser;

    public function setUp(): void
    {
        $this->fileTypeGuesser = new FileTypeGuesser();
    }

    /**
     * @test
     * @dataProvider imageMimeTypesProvider
     */
    public function imageMimeTypesAreTypeImage($mimeType)
    {
        $result = $this->fileTypeGuesser->guessFromMimeType($mimeType);
        $this->assertEquals('image', $result);
    }

    /**
     * @test
     */
    public function otherMimeTypesAreUnsupported()
    {
        $this->expectException(UnsupportedFileTypeException::class);

        $this->fileTypeGuesser->guessFromMimeType('text/plain');
    }

    public function imageMimeTypesProvider(): array
    {
        return [
            ['image/jpeg'],
            ['image/png'],
            ['image/gif'],
            ['image/webp'],
            ['image/avif'],
        ];
    }
}
