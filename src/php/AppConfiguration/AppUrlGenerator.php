<?php
namespace hleo\AppConfiguration;

class AppUrlGenerator
{
    private $protocol;
    private $domain;

    public function __construct(
        string $protocol,
        string $domain
    ) {
        $this->protocol = $protocol;
        $this->domain = $domain;
    }

    public function generate(string $path = '/', array $queryParameters = [])
    {
        $url = sprintf('%s://%s%s', $this->protocol, $this->domain, $path);

        if (!empty($queryParameters)) {
            $url = sprintf('%s?%s', $url, http_build_query($queryParameters));
        }

        return $url;
    }
}
