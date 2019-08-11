<?php
declare(strict_types=1);

namespace Lithos\Tests\Http;

use Lithos\Http\HttpUserAgentProfiler;
use PHPUnit\Framework\TestCase;

class HttpUserAgentProfilerTest extends TestCase
{
    private $httpUserAgentProfiler;

    public function setUp(): void
    {
        $this->httpUserAgentProfiler = new HttpUserAgentProfiler;
    }

    /**
     * @test
     * @dataProvider userAgentProvider
     */
    public function getProfile($userAgentString, $isMobileBrowser, $isiOs, $isSafari)
    {
        $expected = [
            'is_mobile_browser' => $isMobileBrowser,
            'is_ios' => $isiOs,
            'is_ipad_or_macos_safari' => $isSafari,
        ];
        $result = $this->httpUserAgentProfiler->getProfile($userAgentString);
        $this->assertEquals($expected, $result);
    }

    public function userAgentProvider()
    {
        return [
            // phpcs:disable Generic.Files.LineLength
            'Android browser' => [
                'Mozilla/5.0 (Linux; U; Android 4.4.2; en-us; SCH-I535 Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
                true,
                false,
                false,
            ],
            'BlackBerry Browser' => [
                'Mozilla/5.0 (BB10; Kbd) AppleWebKit/537.35+ (KHTML, like Gecko) Version/10.3.3.2205 Mobile Safari/537.35+',
                true,
                false,
                false,
            ],
            'Chrome for Android' => [
                'Mozilla/5.0 (Linux; Android 7.0; SM-G930V Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.125 Mobile Safari/537.36',
                true,
                false,
                false,
            ],
            'Chrome for Chrome OS' => [
                'Mozilla/5.0 (X11; CrOS x86_64 8172.45.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.64 Safari/537.36',
                false,
                false,
                false,
            ],
            'Chrome for iOS' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) CriOS/56.0.2924.75 Mobile/14E5239e Safari/602.1',
                true,
                true,
                false,
            ],
            'Chrome for macOS' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.87 Safari/537.36',
                false,
                false,
                false,
            ],
            'Chrome for Windows (Windows 7)' => [
                'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
                false,
                false,
                false,
            ],
            'Edge for Android' => [
                'Mozilla/5.0 (Linux; Android 8.0; Pixel XL Build/OPP3.170518.006) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.0 Mobile Safari/537.36 EdgA/41.1.35.1',
                true,
                false,
                false,
            ],
            'Edge for iOS' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_2 like Mac OS X) AppleWebKit/603.2.4 (KHTML, like Gecko) Mobile/14F89 Safari/603.2.4 EdgiOS/41.1.35.1',
                true,
                true,
                false,
            ],
            'Edge for Windows (Windows 10)' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246',
                false,
                false,
                false,
            ],
            'Edge for Windows Phone' => [
                'Mozilla/5.0 (Windows Phone 10.0; Android 6.0.1; Microsoft; Lumia 950) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Mobile Safari/537.36 Edge/15.14977',
                true,
                false,
                false,
            ],
            'Firefox Focus for Android' => [
                'Mozilla/5.0 (Linux; Android 7.0) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Focus/1.0 Chrome/59.0.3029.83 Mobile Safari/537.36',
                true,
                false,
                false,
            ],
            'Firefox Focus for iOS' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 12_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/7.0.4 Mobile/16B91 Safari/605.1.15',
                true,
                true,
                false,
            ],
            'Firefox for Android' => [
                'Mozilla/5.0 (Android 7.0; Mobile; rv:54.0) Gecko/54.0 Firefox/54.0',
                true,
                false,
                false,
            ],
            'Firefox for iOS' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_2 like Mac OS X) AppleWebKit/603.2.4 (KHTML, like Gecko) FxiOS/7.5b3349 Mobile/14F89 Safari/603.2.4',
                true,
                true,
                false,
            ],
            'Firefox for iOS (iPod touch)' => [
                'Mozilla/5.0 (iPod touch; CPU iPhone OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) FxiOS/1.0 Mobile/12F69 Safari/600.1.4',
                true,
                true,
                false,
            ],
            'Firefox for Linux' => [
                'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:15.0) Gecko/20100101 Firefox/15.0.1',
                false,
                false,
                false,
            ],
            'Firefox for macOS' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:68.0) Gecko/20100101 Firefox/68.0',
                false,
                false,
                false,
            ],
            'IE Mobile' => [
                'Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; Microsoft; Lumia 950)',
                true,
                false,
                false,
            ],
            'Opera for macOS' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36 OPR/62.0.3331.101',
                false,
                false,
                false,
            ],
            'Opera Mini' => [
                'Opera/9.80 (J2ME/MIDP; Opera Mini/5.1.21214/28.2725; U; ru) Presto/2.8.119 Version/11.10',
                true,
                false,
                false,
            ],
            'Opera Mini (iOS WebKit)' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) OPiOS/10.2.0.93022 Mobile/11D257 Safari/9537.53',
                true,
                true,
                false,
            ],
            'Opera Mobile (Blink)' => [
                'Mozilla/5.0 (Linux; Android 7.0; SM-A310F Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.91 Mobile Safari/537.36 OPR/42.7.2246.114996',
                true,
                false,
                false,
            ],
            'Opera Mobile (Presto)' => [
                'Opera/9.80 (Android 4.1.2; Linux; Opera Mobi/ADR-1305251841) Presto/2.11.355 Version/12.10',
                true,
                false,
                false,
            ],
            'Safari iOS' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1',
                true,
                true,
                false,
            ],
            'Safari for macOS' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15',
                false,
                false,
                true,
            ],
            'Samsung Browser' => [
                'Mozilla/5.0 (Linux; Android 7.0; SAMSUNG SM-G955U Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/5.4 Chrome/51.0.2704.106 Mobile Safari/537.36',
                true,
                false,
                false,
            ],
            'Yandex Browser' => [
                'Mozilla/5.0 (Linux; Android 6.0; Lenovo K50a40 Build/MRA58K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.137 YaBrowser/17.4.1.352.00 Mobile Safari/537.36',
                true,
                false,
                false,
            ],
            // phpcs:enable
        ];
    }
}
