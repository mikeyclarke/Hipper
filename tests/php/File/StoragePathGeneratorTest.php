<?php

declare(strict_types=1);

namespace Hipper\Tests\File;

use Hipper\File\Exception\UnsupportedFileUsageException;
use Hipper\File\StoragePathGenerator;
use PHPUnit\Framework\TestCase;

class StoragePathGeneratorTest extends TestCase
{
    private $storagePathGenerator;

    public function setUp(): void
    {
        $this->storagePathGenerator = new StoragePathGenerator();
    }

    /**
     * @test
     * @dataProvider fileDetailsProvider
     */
    public function supportedUsagePaths($usage, $id, $extension, $expectedPrefix)
    {
        $result = $this->storagePathGenerator->generate($usage, $id, $extension);

        $expected = sprintf('%s/%s.%s', $expectedPrefix, $id, $extension);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function unsupportedUsage()
    {
        $usage = 'foo';
        $id = 'foouuid';
        $extension = 'png';

        $this->expectException(UnsupportedFileUsageException::class);

        $this->storagePathGenerator->generate($usage, $id, $extension);
    }

    public function fileDetailsProvider(): array
    {
        return [
            ['profile_image', 'profileimageuuid', 'jpeg', 'profile-image'],
            ['profile_image_thumbnail', 'profileimagethumbuuid', 'jpeg', 'profile-image-thumbnail'],
        ];
    }
}
