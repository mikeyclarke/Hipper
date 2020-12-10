<?php

declare(strict_types=1);

namespace Hipper\Messenger\MessageHandler;

use Hipper\Messenger\Message\OrganizationKnowledgeExportRequest;
use Hipper\Organization\OrganizationKnowledgeExporter;
use Hipper\Organization\OrganizationModel;
use Hipper\Organization\OrganizationRepository;
use Hipper\TransactionalEmail\OrganizationKnowledgeExport as OrganizationKnowledgeExportEmail;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class OrganizationKnowledgeExportRequestHandler implements MessageHandlerInterface
{
    private OrganizationKnowledgeExportEmail $organizationKnowledgeExportEmail;
    private OrganizationKnowledgeExporter $organizationKnowledgeExporter;
    private OrganizationRepository $organizationRepository;

    public function __construct(
        OrganizationKnowledgeExportEmail $organizationKnowledgeExportEmail,
        OrganizationKnowledgeExporter $organizationKnowledgeExporter,
        OrganizationRepository $organizationRepository
    ) {
        $this->organizationKnowledgeExportEmail = $organizationKnowledgeExportEmail;
        $this->organizationKnowledgeExporter = $organizationKnowledgeExporter;
        $this->organizationRepository = $organizationRepository;
    }

    public function __invoke(OrganizationKnowledgeExportRequest $message): void
    {
        $recipientEmailAddresses = $message->getRecipientAddresses();
        $organization = $this->getOrganization($message->getOrganizationId());

        $zipPathname = $this->organizationKnowledgeExporter->export($organization);
        $base64Zip = base64_encode(file_get_contents($zipPathname));

        try {
            $this->organizationKnowledgeExportEmail->send($recipientEmailAddresses, $base64Zip);
        } finally {
            unlink($zipPathname);
        }
    }

    private function getOrganization(string $organizationId): OrganizationModel
    {
        $result = $this->organizationRepository->findById($organizationId);
        if (null === $result) {
            throw new \Exception('Organization does not exist');
        }
        return OrganizationModel::createFromArray($result);
    }
}
