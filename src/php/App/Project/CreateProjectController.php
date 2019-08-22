<?php
declare(strict_types=1);

namespace Hipper\App\Project;

use Hipper\Project\Project;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

class CreateProjectController
{
    const PLACEHOLDER_PROJECT_NAMES = [
        'Growth',
        'Retention',
        'Marketing website'
    ];

    private $project;
    private $router;
    private $twig;

    public function __construct(
        Project $project,
        UrlGeneratorInterface $router,
        Twig_Environment $twig
    ) {
        $this->project = $project;
        $this->router = $router;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $context = [
            'backLink' => $this->getBackLink($request),
            'bodyClassList' => [
                'l-sheet',
            ],
            'html_title' => 'New project',
            'placeholderProjectName' => $this->getPlaceholderProjectName(),
        ];

        return new Response(
            $this->twig->render(
                'project/create_project.twig',
                $context
            )
        );
    }

    public function postAction(Request $request): JsonResponse
    {
        $person = $request->attributes->get('person');

        try {
            $projectModel = $this->project->create($person, $request->request->all());
        } catch (ValidationException $e) {
            return new JsonResponse(
                [
                    'name' => $e->getName(),
                    'message' => $e->getMessage(),
                    'violations' => $e->getViolations(),
                ],
                400
            );
        }

        return new JsonResponse([
            'project_url' => $this->router->generate('project.get', ['project_url_id' => $projectModel->getUrlId()]),
        ], 201);
    }

    private function getPlaceholderProjectName(): string
    {
        $names = self::PLACEHOLDER_PROJECT_NAMES;
        return $names[array_rand($names)];
    }

    private function getBackLink(Request $request): string
    {
        if (!$request->server->has('HTTP_REFERER')) {
            return '/';
        }

        $referrer = $request->server->get('HTTP_REFERER');
        $origin = $request->getSchemeAndHttpHost();
        if (substr($referrer, 0, strlen($origin)) !== $origin) {
            return '/';
        }

        $path = substr($referrer, strlen($origin));
        if ($path === 'projects/new') {
            return '/';
        }

        return $path;
    }
}
