<?php

declare(strict_types=1);

namespace Hipper\Command;

use Doctrine\DBAL\Connection;
use Hipper\Project\ProjectRepository;
use Hipper\Team\TeamRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteOrganizationCommand extends Command
{
    protected static $defaultName = 'app:organization:delete';

    public function __construct(
        private Connection $connection,
        private ProjectRepository $projectRepository,
        private TeamRepository $teamRepository,
    ) {

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Delete organization');
        $this->addArgument('organizationId', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $organizationId = $input->getArgument('organizationId');

        $this->connection->beginTransaction();
        try {
            $this->connection->update('organization', ['knowledgebase_id' => null], ['id' => $organizationId]);
            $this->connection->update('document', ['topic_id' => null], ['organization_id' => $organizationId]);
            $this->connection->update('topic', ['parent_topic_id' => null], ['organization_id' => $organizationId]);

            $this->connection->delete('activity', ['organization_id' => $organizationId]);
            $this->connection->delete('person_knowledgebase_entry_view', ['organization_id' => $organizationId]);
            $this->connection->delete('knowledgebase_route', ['organization_id' => $organizationId]);
            $this->connection->delete('topic', ['organization_id' => $organizationId]);
            $this->connection->delete('document_revision', ['organization_id' => $organizationId]);
            $this->connection->delete('document', ['organization_id' => $organizationId]);

            $teams = $this->teamRepository->getAll($organizationId);
            foreach ($teams as $team) {
                $teamId = $team['id'];
                $this->connection->delete('person_to_team_map', ['team_id' => $teamId]);
                $this->connection->delete('team', ['id' => $teamId]);
            }

            $projects = $this->projectRepository->getAll($organizationId);
            foreach ($projects as $project) {
                $projectId = $project['id'];
                $this->connection->delete('person_to_project_map', ['project_id' => $projectId]);
                $this->connection->delete('project', ['id' => $projectId]);
            }

            $this->connection->delete('knowledgebase', ['organization_id' => $organizationId]);
            $this->connection->delete('person', ['organization_id' => $organizationId]);
            $this->connection->delete('organization', ['id' => $organizationId]);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $output->writeln('Organization deleted');

        return 0;
    }
}
