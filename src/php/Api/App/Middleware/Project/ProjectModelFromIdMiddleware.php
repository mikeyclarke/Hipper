<?php
declare(strict_types=1);

namespace Hipper\Api\App\Middleware\Project;

use Hipper\Project\ProjectModel;
use Hipper\Project\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectModelFromIdMiddleware
{
    private ProjectRepository $projectRepository;

    public function __construct(
        ProjectRepository $projectRepository
    ) {
        $this->projectRepository = $projectRepository;
    }

    public function before(Request $request): void
    {
        $organization = $request->attributes->get('organization');

        $id = $request->attributes->get('project_id');
        $result = $this->projectRepository->findById($id, $organization->getId());
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $project = ProjectModel::createFromArray($result);
        $request->attributes->set('project', $project);
    }
}
