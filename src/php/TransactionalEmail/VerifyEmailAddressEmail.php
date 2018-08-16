<?php
namespace hleo\TransactionalEmail;

class VerifyEmailAddressEmail
{
    const TAG = 'welcome.verify_email_address';

    private $apiClientFactory;
    private $emailFromAddress;
    private $emailReplyToAddress;

    public function __construct(
        PostmarkApiClientFactory $apiClientFactory,
        string $emailFromAddress,
        string $emailReplyToAddress
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->emailFromAddress = $emailFromAddress;
        $this->emailReplyToAddress = $emailReplyToAddress;
    }

    public function send(string $recipientName, string $recipientEmailAddress, string $verifyLink)
    {
        $client = $this->apiClientFactory->create();
        $client->request('POST', '/email', [
            'json' => [
                'From' => $this->emailFromAddress,
                'ReplyTo' => $this->emailReplyToAddress,
                'To' => $recipientEmailAddress,
                'Tag' => self::TAG,
                'HtmlBody' => sprintf(
                    '<html><body><a href="%s">Verify your email address</a></body></html>',
                    $verifyLink
                ),
            ]
        ]);
    }
}
