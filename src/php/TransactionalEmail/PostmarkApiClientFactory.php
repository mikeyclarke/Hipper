<?php
namespace hleo\TransactionalEmail;

use GuzzleHttp\Client;

class PostmarkApiClientFactory
{
    private $apiBaseUrl;
    private $apiToken;

    public function __construct(
        string $apiBaseUrl,
        string $apiToken
    ) {
        $this->apiBaseUrl = $apiBaseUrl;
        $this->apiToken = $apiToken;
    }

    public function create()
    {
        return new Client([
            'base_uri' => $this->apiBaseUrl,
            'headers' => [
                'Accept' => 'application/json',
                'X-Postmark-Server-Token' => $this->apiToken,
            ],
        ]);
    }
}
