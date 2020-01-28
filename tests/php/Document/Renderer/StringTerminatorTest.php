<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer;

use Hipper\Document\Renderer\StringTerminator;
use PHPUnit\Framework\TestCase;

class StringTerminatorTest extends TestCase
{
    private $stringTerminator;

    public function setUp(): void
    {
        $this->stringTerminator = new StringTerminator;
    }

    /**
     * @test
     * @dataProvider strings
     */
    public function terminateStringWithPunctuationCharacter($str, $expected)
    {
        $result = $this->stringTerminator->terminateStringWithPunctuationCharacter($str);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function canProvideCharacter()
    {
        $str = 'Hello there here’s more stuff';
        $character = ':';

        $expected = 'Hello there here’s more stuff:';

        $result = $this->stringTerminator->terminateStringWithPunctuationCharacter($str, $character);
        $this->assertEquals($expected, $result);
    }

    public function strings()
    {
        return [
            ['Some text', 'Some text.'],
            ['Some text ending in a period.', 'Some text ending in a period.'],
            ['Some text ending in a space ', 'Some text ending in a space.'],
            ['Some text ending in a period and space. ', 'Some text ending in a period and space. '],
            ['Some text ending in a colon:', 'Some text ending in a colon:'],
            ['Some text ending in a colon and space: ', 'Some text ending in a colon and space: '],
            ['Some text with punctuation mid-sentence', 'Some text with punctuation mid-sentence.'],
            ['Some 📝 with multibyte chars…', 'Some 📝 with multibyte chars…'],
        ];
    }
}
