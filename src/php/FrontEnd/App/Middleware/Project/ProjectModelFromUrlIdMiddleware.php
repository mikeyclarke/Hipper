<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Middleware\Project;

use Hipper\Project\ProjectModel;
use Hipper\Project\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectModelFromUrlIdMiddleware
{
    private $projectRepository;

    public function __construct(
        ProjectRepository $projectRepository
    ) {
        $this->projectRepository = $projectRepository;
    }

    public function before(Request $request): void
    {
        $organization = $request->attributes->get('organization');
        $person = $request->attributes->get('person');

        $urlId = $request->attributes->get('project_url_id');
        $result = $this->projectRepository->findByUrlId($urlId, $organization->getId());
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $project = ProjectModel::createFromArray($result);
        $request->attributes->set('project', $project);

        $personIsInProject = $this->projectRepository->existsWithMappingForPerson($project->getId(), $person->getId());
        $request->attributes->set('personIsInProject', $personIsInProject);
    }
}
