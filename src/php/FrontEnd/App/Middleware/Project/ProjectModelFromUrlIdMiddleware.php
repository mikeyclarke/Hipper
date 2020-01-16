<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Middleware\Project;

use Hipper\Project\ProjectModel;
use Hipper\Project\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class ProjectModelFromUrlIdMiddleware
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
        $person = $request->attributes->get('person');

        $urlId = $request->attributes->get('project_url_id');
        $result = $this->projectRepository->findByUrlId($urlId, $organization->getId());
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $project = ProjectModel::createFromArray($result);
        $request->attributes->set('project', $project);
        $this->twig->addGlobal('project', $project);

        $personIsInProject = $this->projectRepository->existsWithMappingForPerson($project->getId(), $person->getId());
        $request->attributes->set('personIsInProject', $personIsInProject);

        $this->twig->addGlobal(
            'search_action',
            $this->urlGeneratorInterface->generate(
                self::SEARCH_ROUTE,
                [
                    'subdomain' => $organization->getSubdomain(),
                    'project_url_id' => $urlId,
                ]
            )
        );
    }
}
