<?php
declare(strict_types=1);

namespace Hipper\Security;

class ContentSecurityPolicyBuilder
{
    const KEYWORDS = ['none', 'self', 'unsafe-inline'];

    private $assetDomain;

    public function __construct(
        string $assetDomain
    ) {
        $this->assetDomain = $assetDomain;
    }

    public function build(): string
    {
        $directives = [
            'default-src' => ['none'],
            'base-uri' => ['self'],
            'block-all-mixed-content' => null,
            'connect-src' => ['self', $this->assetDomain],
            'font-src' => ['self', 'data:', $this->assetDomain],
            'form-action' => ['self'],
            'frame-ancestors' => ['none'],
            'img-src' => ['self', 'data:', 'https:'],
            'script-src' => ['self', $this->assetDomain],
            'style-src' => ['self', $this->assetDomain],
        ];

        $policy = [];
        foreach ($directives as $directiveName => $directiveValue) {
            $policy[] = $this->formatDirective($directiveName, $directiveValue);
        }
        return implode('; ', $policy);
    }

    private function formatDirective(string $directiveName, ?array $directiveValue): string
    {
        if (null === $directiveValue) {
            return $directiveName;
        }

        $sources = [];
        foreach ($directiveValue as $source) {
            if (in_array($source, self::KEYWORDS)) {
                $sources[] = "'$source'";
            } else {
                $sources[] = $source;
            }
        }

        return sprintf('%s %s', $directiveName, implode(' ', $sources));
    }
}
