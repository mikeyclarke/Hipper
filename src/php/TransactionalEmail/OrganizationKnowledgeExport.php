<?php

declare(strict_types=1);

namespace Hipper\TransactionalEmail;

class OrganizationKnowledgeExport
{
    const TAG = 'export.organization_knowledge';

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

    public function send(array $recipientEmailAddresses, string $zipContent): void
    {
        $client = $this->apiClientFactory->create();
        $client->request('POST', '/email', [
            'json' => [
                'From' => $this->emailFromAddress,
                'ReplyTo' => $this->emailReplyToAddress,
                'To' => implode(',', $recipientEmailAddresses),
                'Tag' => self::TAG,
                'Attachments' => [
                    [
                        'Name' => 'docs.zip',
                        'Content' => $zipContent,
                        'ContentType' => 'application/zip',
                    ],
                ],
                'Subject' => 'Hipper data export',
                'TextBody' => "Hey there,\n\nYou recently requested an export of all of the docs your organization " .
                    "has in Hipper. You can find those docs within the zip file attached to this email.\n\nEnjoy " .
                    "your day!\n\nYour friends at Hipper.",
            ]
        ]);
    }
}
