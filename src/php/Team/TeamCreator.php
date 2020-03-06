<?php
declare(strict_types=1);

namespace Hipper\Team;

use Doctrine\DBAL\Connection;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseCreator;
use Hipper\Person\PersonModel;
use Hipper\Team\Event\TeamCreatedEvent;
use Hipper\Team\Storage\PersonToTeamMapInserter;
use Hipper\Team\Storage\TeamInserter;
use Hipper\Url\UrlSlugGenerator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TeamCreator
{
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;
    private IdGenerator $idGenerator;
    private KnowledgebaseCreator $knowledgebaseCreator;
    private PersonToTeamMapInserter $personToTeamMapInserter;
    private TeamInserter $teamInserter;
    private TeamValidator $teamValidator;
    private UrlSlugGenerator $urlSlugGenerator;

    public function __construct(
        Connection $connection,
        EventDispatcherInterface $eventDispatcher,
        IdGenerator $idGenerator,
        KnowledgebaseCreator $knowledgebaseCreator,
        PersonToTeamMapInserter $personToTeamMapInserter,
        TeamInserter $teamInserter,
        TeamValidator $teamValidator,
        UrlSlugGenerator $urlSlugGenerator
    ) {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->idGenerator = $idGenerator;
        $this->knowledgebaseCreator = $knowledgebaseCreator;
        $this->personToTeamMapInserter = $personToTeamMapInserter;
        $this->teamInserter = $teamInserter;
        $this->teamValidator = $teamValidator;
        $this->urlSlugGenerator = $urlSlugGenerator;
    }

    public function create(PersonModel $person, array $parameters): TeamModel
    {
        $this->teamValidator->validate($parameters, $person->getOrganizationId(), true);

        $id = $this->idGenerator->generate();
        $urlId = $this->urlSlugGenerator->generateFromString($parameters['name']);

        $this->connection->beginTransaction();
        try {
            $knowledgebase = $this->knowledgebaseCreator->create('team', $person->getOrganizationId());
            $result = $this->teamInserter->insert(
                $id,
                $parameters['name'],
                $parameters['description'],
                $urlId,
                $knowledgebase['id'],
                $person->getOrganizationId()
            );
            $this->createPersonTeamMap($person->getId(), $result['id']);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $team = TeamModel::createFromArray($result);

        $teamCreatedEvent = new TeamCreatedEvent($team, $person);
        $this->eventDispatcher->dispatch($teamCreatedEvent, TeamCreatedEvent::NAME);

        return $team;
    }

    private function createPersonTeamMap(string $personId, string $teamId): void
    {
        $id = $this->idGenerator->generate();
        $this->personToTeamMapInserter->insert($id, $personId, $teamId);
    }
}
