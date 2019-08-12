<?php
declare(strict_types=1);

namespace Hipper\TransactionalEmail;

use Twig_Environment;

class BulkInvite
{
    const TAG = 'welcome.invite';

    private $apiClientFactory;
    private $twig;
    private $emailFromAddress;
    private $emailReplyToAddress;

    public function __construct(
        PostmarkApiClientFactory $apiClientFactory,
        Twig_Environment $twig,
        string $emailFromAddress,
        string $emailReplyToAddress
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->twig = $twig;
        $this->emailFromAddress = $emailFromAddress;
        $this->emailReplyToAddress = $emailReplyToAddress;
    }

    public function send(array $emailsToSend): void
    {
        $client = $this->apiClientFactory->create();
        $client->request('POST', '/email/batch', [
            'json' => $this->buildRequestBody($emailsToSend),
        ]);
    }

    private function buildRequestBody(array $emailsToSend)
    {
        $body = [];
        foreach ($emailsToSend as $invite) {
            $body[] = [
                'From' => $this->emailFromAddress,
                'ReplyTo' => $invite['sender_email_address'],
                'To' => $invite['recipient_email_address'],
                'Tag' => self::TAG,
                'Subject' => sprintf(
                    '%s invited you to join %s on Hipper',
                    $invite['sender_name'],
                    $invite['organization_name']
                ),
                'HtmlBody' => $this->twig->render(
                    'transactional_email/invite.twig',
                    array_merge(
                        $invite,
                        ['support_email_address' => $this->emailReplyToAddress]
                    )
                ),
            ];
        }
        return $body;
    }
}
