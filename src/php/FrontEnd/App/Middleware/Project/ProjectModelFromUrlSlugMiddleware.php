<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Middleware\Project;

use Hipper\Project\ProjectModel;
use Hipper\Project\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class ProjectModelFromUrlSlugMiddleware
{
    const SEARCH_ROUTE = 'front_end.app.project.search';

    private $projectRepository;
    private $twig;
    private $urlGeneratorInterface;

    public function __construct(
        ProjectRepository $projectRepository,
        Twig $twig,
        UrlGeneratorInterface $urlGeneratorInterface
    ) {
        $this->projectRepository = $projectRepository;
        $this->twig = $twig;
        $this->urlGeneratorInterface = $urlGeneratorInterface;
    }

    public function before(Request $request): void
    {
        $organization = $request->attributes->get('organization');
        $currentUser = $request->attributes->get('current_user');

        $urlSlug = $request->attributes->get('project_url_slug');
        $result = $this->projectRepository->findByUrlSlug($urlSlug, $organization->getId());
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $project = ProjectModel::createFromArray($result);
        $request->attributes->set('project', $project);
        $this->twig->addGlobal('project', $project);

        $currentUserIsInProject = $this->projectRepository->existsWithMappingForPerson(
            $project->getId(),
            $currentUser->getId()
        );
        $request->attributes->set('current_user_is_in_project', $currentUserIsInProject);

        $this->twig->addGlobal(
            'search_action',
            $this->urlGeneratorInterface->generate(
                self::SEARCH_ROUTE,
                [
                    'subdomain' => $organization->getSubdomain(),
                    'project_url_slug' => $urlSlug,
                ]
            )
        );
    }
}
