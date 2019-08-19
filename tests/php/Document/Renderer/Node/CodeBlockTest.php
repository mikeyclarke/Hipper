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

        $expected = ['<pre><code>', '</code></pre>'];

        $result = $this->codeBlockNode->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }
}
