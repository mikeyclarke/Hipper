<?php
declare(strict_types=1);

namespace Hipper\Tests\File;

use Hipper\File\FileNameGenerator;
use PHPUnit\Framework\TestCase;

class FileNameGeneratorTest extends TestCase
{
    private $fileNameGenerator;

    public function setUp(): void
    {
        $this->fileNameGenerator = new FileNameGenerator;
    }

    /**
     * @test
     */
    public function maxLengthIsApplied()
    {
        $stringToSluggify = str_repeat('a', 256);
        $extension = 'pdf';

        $maxLength = 255 - (mb_strlen($extension) + 1);

        $expected = sprintf('%s.%s', str_repeat('a', $maxLength), $extension);

        $result = $this->fileNameGenerator->generateFromString($stringToSluggify, $extension);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function defaultNameCanBeProvided()
    {
        $stringToSluggify = '';
        $extension = 'pdf';
        $defaultName = 'Untitled doc';

        $expected = sprintf('%s.%s', $defaultName, $extension);

        $result = $this->fileNameGenerator->generateFromString($stringToSluggify, $extension, $defaultName);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider fileNameExamples
     * @test
     */
    public function reservedCharactersAndWordsAreStripped($stringToSluggify, $expected)
    {
        $result = $this->fileNameGenerator->generateFromString($stringToSluggify, 'md');
        $this->assertEquals($expected, $result);
    }

    public function fileNameExamples()
    {
        return [
            ['', 'File.md'],
            [':', 'File.md'],
            ['|', 'File.md'],
            ['?', 'File.md'],
            ['"', 'File.md'],
            ['>', 'File.md'],
            ['<', 'File.md'],
            ['/', 'File.md'],
            ["\\", 'File.md'],
            ['a', 'a.md'],
            [' a', 'a.md'],
            ['a ', 'a.md'],
            [' a ', 'a.md'],
            ["a    \n", 'a.md'],
            ['x x', 'x x.md'],
            ['x  x', 'x x.md'],
            ['x   x', 'x x.md'],
            [' x  |  x ', 'x x.md'],
            ["x\tx", 'x x.md'],
            ["x\r\nx", 'x x.md'],
            ['笊, ざる', '笊, ざる.md'],
            ['  what\\ēver//wëird:user:înput:', 'whatēverwëirduserînput.md'],
            ['.My file', 'My file.md'],
            ['..My file', 'My file.md'],
            ['My file.exe', 'My fileexe.md'],
            ['CON', 'File.md'],
            ['lpt1', 'File.md'],
            ['com4', 'File.md'],
            ['aux', 'File.md'],
            ["COM10", 'COM10.md'],
        ];
    }
}
