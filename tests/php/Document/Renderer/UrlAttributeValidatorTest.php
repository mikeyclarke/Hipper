<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer;

use Hipper\Document\Renderer\UrlAttributeValidator;
use PHPUnit\Framework\TestCase;

class UrlAttributeValidatorTest extends TestCase
{
    private $urlAttributeValidator;

    public function setUp(): void
    {
        $this->urlAttributeValidator = new UrlAttributeValidator;
    }

    /**
     * @test
     * @dataProvider validUrlProvider
     */
    public function validUrl($url)
    {
        $result = $this->urlAttributeValidator->isValid($url);
        $this->assertTrue($result);
    }

    /**
     * @test
     * @dataProvider invalidUrlProvider
     */
    public function invalidUrl($url)
    {
        $result = $this->urlAttributeValidator->isValid($url);
        $this->assertFalse($result);
    }

    public function validUrlProvider()
    {
        return [
            ['http://a.pl'],
            ['http://www.google.com'],
            ['http://www.google.com.'],
            ['http://www.google.museum'],
            ['https://google.com/'],
            ['http://www.example.coop/'],
            ['http://www.test-example.com/'],
            ['http://www.symfony.com/'],
            ['http://symfony.fake/blog/'],
            ['http://symfony.com/?'],
            ['http://symfony.com/search?type=&q=url+validator'],
            ['http://symfony.com/#'],
            ['http://symfony.com/#?'],
            ['http://www.symfony.com/doc/current/book/validation.html#supported-constraints'],
            ['http://very.long.domain.name.com/'],
            ['http://localhost/'],
            ['http://myhost123/'],
            ['http://127.0.0.1/'],
            ['http://sãopaulo.com/'],
            ['http://xn--sopaulo-xwa.com/'],
            ['http://sãopaulo.com.br/'],
            ['http://xn--sopaulo-xwa.com.br/'],
            ['http://пример.испытание/'],
            ['http://xn--e1afmkfd.xn--80akhbyknj4f/'],
            ['http://مثال.إختبار/'],
            ['http://xn--mgbh0fb.xn--kgbechtv/'],
            ['http://例子.测试/'],
            ['http://xn--fsqu00a.xn--0zwm56d/'],
            ['http://例子.測試/'],
            ['http://xn--fsqu00a.xn--g6w251d/'],
            ['http://例え.テスト/'],
            ['http://xn--r8jz45g.xn--zckzah/'],
            ['http://مثال.آزمایشی/'],
            ['http://xn--mgbh0fb.xn--hgbk6aj7f53bba/'],
            ['http://실례.테스트/'],
            ['http://xn--9n2bp8q.xn--9t4b11yi5a/'],
            ['http://العربية.idn.icann.org/'],
            ['http://xn--ogb.idn.icann.org/'],
            ['http://xn--e1afmkfd.xn--80akhbyknj4f.xn--e1afmkfd/'],
            ['http://xn--espaa-rta.xn--ca-ol-fsay5a/'],
            ['http://xn--d1abbgf6aiiy.xn--p1ai/'],
            ['http://☎.com/'],
            ['http://symfony.com?'],
            ['http://symfony.com?query=1'],
            ['http://symfony.com/?query=1'],
            ['http://symfony.com#'],
            ['http://symfony.com#fragment'],
            ['http://symfony.com/#fragment'],
            ['http://symfony.com/#one_more%20test'],
            ['http://example.com/exploit.html?hello[0]=test'],
            ['mailto:help@usehipper.com'],
            ['mailto:help@usehipper.com?subject=Foo'],
            ['mailto:help@usehipper.com?subject=Foo&body=Bar'],
            ['/'],
            ['/?'],
            ['/search?type=&q=url+validator'],
            ['/#'],
            ['/#?'],
            ['/doc/current/book/validation.html#supported-constraints'],
            ['?query=1'],
            ['/?query=1'],
            ['#'],
            ['#fragment'],
            ['/#fragment'],
            ['/#one_more%20test'],
            ['/exploit.html?hello[0]=test'],
        ];
    }

    public function invalidUrlProvider()
    {
        return [
            ['duckduckgo.com'],
            ['://duckduckgo.com'],
            ['http ://duckduckgo.com'],
            ['http:/duckduckgo.com'],
            ['http://duckduck_go.com'],
            ['http://duckduckgo.com::aa'],
            ['http://duckduckgo.com:aa'],
            ['ftp://duckduckgo.fr'],
            ['faked://duckduckgo.fr'],
            ['http://127.0.0.1:aa/'],
            ['ftp://[::1]/'],
            ['http://[::1'],
            ['http://hello.☎/'],
            ['http://:password@symfony.com'],
            ['http://:password@@symfony.com'],
            ['http://username:passwordsymfony.com'],
            ['http://usern@me:password@symfony.com'],
            ['http://example.com/exploit.html?<script>alert(1);</script>'],
            ['http://example.com/exploit.html?hel lo'],
            ['http://example.com/exploit.html?not_a%hex'],
            ['http://'],
            ['javascript:alert(1)'],
            ['   javascript:alert(1)'],
            ['JaVaScRiPt:alert(1)'],
            ['javascript:document.location="http://www.example.com"'],
        ];
    }
}
