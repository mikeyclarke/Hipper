<?php
declare(strict_types=1);

namespace Hipper\Command;

use Hipper\Messenger\MessageHandler\OrganizationKnowledgeExportRequestHandler;
use Hipper\Messenger\Message\OrganizationKnowledgeExportRequest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportOrganizationKnowledgeCommand extends Command
{
    protected static $defaultName = 'app:organization:export-knowledge';

    private $messageHandler;

    public function __construct(
        OrganizationKnowledgeExportRequestHandler $messageHandler
    ) {
        $this->messageHandler = $messageHandler;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Export organization knowledge');
        $this->addArgument('organizationId', InputArgument::REQUIRED);
        $this->addArgument('emailAddress', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $organizationId = $input->getArgument('organizationId');
        $emailAddress = $input->getArgument('emailAddress');

        $message = new OrganizationKnowledgeExportRequest($organizationId, [$emailAddress]);
        $this->messageHandler->__invoke($message);

        return 0;
    }
}
