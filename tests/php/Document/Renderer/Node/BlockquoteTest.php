<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\Blockquote;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class BlockquoteTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $blockquoteNode;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->blockquoteNode = new Blockquote(
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

        $expected = ['<blockquote>', '</blockquote>'];

        $result = $this->blockquoteNode->getHtmlTags($attributes, $htmlId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toPlainTextString()
    {
        $textContent = <<<TEXT
You expect of me a speech? I have only one to give, and it is the same one I’d give were we not standing on the brim of
a battlefield. It is the same one I’d give were we to meet in the street by chance. I have only ever hoped for one
thing… to see this kingdom united under this English crown. All men are born to die. We know it. We carry it with us
always. If your day be today, so be it. Mine will be tomorrow. Or mine today and yours tomorrow. It matters not. What
matters is that you know, in your hearts, that today you are that kingdom united. You are England. Each and every one
of you. England is you. And it is the space between you. Fight not for yourselves, fight for that space. Fill that
space. Make it tissue. Make it mass. Make it impenetrable. Make it yours! Make it England! Make it England! Great men
to it Captains and Lords. Great men to it!
TEXT;

        $expected = $textContent . "\r\n";

        $result = $this->blockquoteNode->toPlainTextString($textContent);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownString()
    {
        // phpcs:disable Generic.Files.LineLength
        $content = "Please! Please, speak English. I enjoy to speak English. It is simple and ugly.\n\n– The Dauphin of France";
        $expected = "> Please! Please, speak English. I enjoy to speak English. It is simple and ugly.\n> \n> – The Dauphin of France\n\n";
        // phpcs:enable Generic.Files.LineLength

        $result = $this->blockquoteNode->toMarkdownString($content, 0, null, null);
        $this->assertEquals($expected, $result);
    }
}
