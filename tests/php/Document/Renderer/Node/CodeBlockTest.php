<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\CodeBlock;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CodeBlockTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $codeBlockNode;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->codeBlockNode = new CodeBlock(
            $this->context
        );
    }

    /**
     * @test
     */
    public function getHtmlTags()
    {
        $attributes = null;
        $htmlId = null;

        $expected = ['<pre><code>', '</code></pre>'];

        $result = $this->codeBlockNode->getHtmlTags($attributes, $htmlId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toPlainTextString()
    {
        $textContent = '<some><code></code></some>';

        $expected = $textContent . "\r\n";

        $result = $this->codeBlockNode->toPlainTextString($textContent);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownString()
    {
        $content = "<some><code>\n</code></some>";

        $expected = "```\n<some><code>\n</code></some>\n```\n";

        $result = $this->codeBlockNode->toMarkdownString($content, 0, null, null);
        $this->assertEquals($expected, $result);
    }
}
