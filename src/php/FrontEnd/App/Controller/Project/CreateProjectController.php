<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Project;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class CreateProjectController
{
    const PLACEHOLDER_PROJECT_NAMES = [
        'Growth',
        'Retention',
        'Marketing website'
    ];

    private $twig;

    public function __construct(
        Twig $twig
    ) {
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
