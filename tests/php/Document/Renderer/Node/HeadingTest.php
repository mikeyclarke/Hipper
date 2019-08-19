<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\Heading;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class HeadingTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $headingNode;

    public function setUp(): void
    {
        $context = m::mock(HtmlFragmentRendererContext::class);
        $this->headingNode = new Heading($context);
    }

    /**
     * @test
     */
    public function defaultsToH1()
    {
        $expected = ['<h1>', '</h1>'];

        $result = $this->headingNode->getHtmlTags(null);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function headingLevelUsedFromAttributes()
    {
        $attributes = ['level' => 4];

        $expected = ['<h4>', '</h4>'];

        $result = $this->headingNode->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function nonIntegerLevelAttributeValueIsDiscarded()
    {
        $attributes = ['level' => '1></h1><script>window.location.assign("http://scammy-site.example.com");</script>'];

        $expected = ['<h1>', '</h1>'];

        $result = $this->headingNode->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }
}
