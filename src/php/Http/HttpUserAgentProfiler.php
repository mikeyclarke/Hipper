<?php
declare(strict_types=1);

namespace Hipper\Http;

class HttpUserAgentProfiler
{
    public function getProfile(string $userAgent): array
    {
        $isMobileBrowser = $this->isMobileBrowser($userAgent);

        return [
            'is_mobile_browser' => $isMobileBrowser,
            'is_ios' => ($isMobileBrowser) ? $this->isiOs($userAgent) : false,
            'is_ipad_or_macos_safari' => (bool) preg_match(
                '/Mozilla\/.+ \(Macintosh; Intel Mac OS X .+\) AppleWebKit\/.+ Version\/.+ Safari\/.+/',
                $userAgent
            ),
        ];
    }

    private function isMobileBrowser(string $userAgent): bool
    {
        if (strpos($userAgent, ' AppleWebKit') !== false && strpos($userAgent, ' Mobile') !== false) {
            return true;
        }

        if (strpos($userAgent, 'Android') !== false) {
            return true;
        }

        if (strpos($userAgent, 'Windows Phone') !== false) {
            return true;
        }

        if (strpos($userAgent, 'Opera Mini') !== false) {
            return true;
        }

        return false;
    }

    private function isiOs(string $userAgent): bool
    {
        return (bool) preg_match(
            '/Mozilla\/.+ \((iPod( touch)|iPhone|iPad); .+ like Mac OS X\) AppleWebKit\/.+ Mobile\/.+ Safari\/.+/',
            $userAgent
        );
    }
}
