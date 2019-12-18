<?php
declare(strict_types=1);

namespace Hipper\Login;

use Symfony\Component\HttpFoundation\Request;

class LoginSuccessRedirector
{
    const DEFAULT_PATH = '/';
    const OPTIONAL_URI_COMPONENTS = ['query', 'fragment'];
    const REQUIRED_URI_COMPONENTS = ['path'];

    public function generateUri(Request $request): string
    {
        $redirect = $request->request->get('redirect');
        if (null === $redirect) {
            return self::DEFAULT_PATH;
        }

        if (!$this->isValidUri($redirect)) {
            return self::DEFAULT_PATH;
        }

        if (!$this->isSafeUri($redirect)) {
            return self::DEFAULT_PATH;
        }

        return $redirect;
    }

    private function isSafeUri(string $redirect): bool
    {
        $parsedUri = parse_url($redirect);
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

    private function isValidUri(string $redirect): bool
    {
        return (bool) filter_var(
            'https://example.com' . $redirect,
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
