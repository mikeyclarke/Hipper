<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Project\ProjectModel;
use Hipper\Project\ProjectRepository;
use Hipper\Team\TeamModel;
use Hipper\Team\TeamRepository;

class KnowledgebaseOwner
{
    private $projectRepository;
    private $teamRepository;

    public function __construct(
        ProjectRepository $projectRepository,
        TeamRepository $teamRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->teamRepository = $teamRepository;
    }

    public function get(KnowledgebaseModel $knowledgebase): KnowledgebaseOwnerModelInterface
    {
        $knowledgebaseId = $knowledgebase->getId();
        $organizationId = $knowledgebase->getOrganizationId();

        switch ($knowledgebase->getEntity()) {
            case 'team':
                $result = $this->teamRepository->findByKnowledgebaseId($knowledgebaseId, $organizationId);
                return TeamModel::createFromArray($result);
            case 'project':
                $result = $this->projectRepository->findByKnowledgebaseId($knowledgebaseId, $organizationId);
                return ProjectModel::createFromArray($result);
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }
    }
}
