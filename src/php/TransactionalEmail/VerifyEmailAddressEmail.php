<?php
declare(strict_types=1);

namespace Lithos\TransactionalEmail;

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

    public function send(string $recipientName, string $recipientEmailAddress, string $verificationPhrase): void
    {
        $client = $this->apiClientFactory->create();
        $client->request('POST', '/email', [
            'json' => [
                'From' => $this->emailFromAddress,
                'ReplyTo' => $this->emailReplyToAddress,
                'To' => $recipientEmailAddress,
                'Tag' => self::TAG,
                'Subject' => 'Lithos verification code',
                'HtmlBody' => sprintf(
                    '<html><body>' .
                    '<p>Hey! Copy the below phrase into your open browser window to prove that you’re a human.</p>' .
                    '<p>%s</p></body></html>',
                    $verificationPhrase
                ),
            ]
        ]);
    }
}
