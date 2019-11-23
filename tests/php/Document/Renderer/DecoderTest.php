<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer;

use Hipper\Document\Renderer\Decoder;
use Hipper\Document\Renderer\Exception\ContentDecodeException;
use PHPUnit\Framework\TestCase;

class DecoderTest extends TestCase
{
    private $decoder;

    public function setUp(): void
    {
        $this->decoder = new Decoder;
    }

    /**
     * @test
     */
    public function decode()
    {
        $doc = '{"type": "doc", "content": [{"type": "paragraph", "content": [{"type": "text", "text": "👋🍔"}]}]}';

        $expected = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => '👋🍔',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->decoder->decode($doc);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function invalidJson()
    {
        $doc = '*** not JSON ***';

        $this->expectException(ContentDecodeException::class);
        $this->decoder->decode($doc);
    }

    /**
     * @test
     */
    public function docNotTypeDoc()
    {
        $doc = '{"type": "paragraph", "content": [{"type": "text", "text": "Hello"}]}';

        $this->expectException(ContentDecodeException::class);
        $this->decoder->decode($doc);
    }

    /**
     * @test
     */
    public function docWithNoContent()
    {
        $doc = '{"type": "doc"}';

        $this->expectException(ContentDecodeException::class);
        $this->decoder->decode($doc);
    }
}
