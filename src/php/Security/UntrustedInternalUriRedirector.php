<?php
declare(strict_types=1);

namespace Hipper\Security;

class UntrustedInternalUriRedirector
{
    const OPTIONAL_URI_COMPONENTS = ['query', 'fragment'];
    const REQUIRED_URI_COMPONENTS = ['path'];

    public function generateUri(?string $untrustedUri, string $defaultUri): string
    {
        if (null === $untrustedUri) {
            return $defaultUri;
        }

        if (!$this->isValidUri($untrustedUri)) {
            return $defaultUri;
        }

        if (!$this->isSafeUri($untrustedUri)) {
            return $defaultUri;
        }

        return $untrustedUri;
    }

    private function isSafeUri(string $untrustedUri): bool
    {
        $parsedUri = parse_url($untrustedUri);
        if (false === $parsedUri) {
            return false;
        }

        if (!$this->requiredUriComponentsArePresent($parsedUri)) {
            return false;
        }

        if (!$this->onlyAllowedUriComponentsArePresent($parsedUri)) {
            return false;
        }

        return true;
    }

    private function isValidUri(string $untrustedUri): bool
    {
        return (bool) filter_var(
            'https://example.com' . $untrustedUri,
            FILTER_VALIDATE_URL,
            FILTER_FLAG_PATH_REQUIRED
        );
    }

    private function onlyAllowedUriComponentsArePresent(array $parsedUri): bool
    {
        return empty(
            array_diff_key(
                $parsedUri,
                array_flip(array_merge(self::REQUIRED_URI_COMPONENTS, self::OPTIONAL_URI_COMPONENTS))
            )
        );
    }

    private function requiredUriComponentsArePresent(array $parsedUri): bool
    {
        return empty(
            array_diff_key(
                array_flip(self::REQUIRED_URI_COMPONENTS),
                $parsedUri
            )
        );
    }
}
