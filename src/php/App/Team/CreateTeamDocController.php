<?php
declare(strict_types=1);

namespace Hipper\App\Team;

use Hipper\Document\Document;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class CreateTeamDocController
{
    private $document;
    private $twig;
    private $documentAllowedMarks;
    private $documentAllowedNodes;

    public function __construct(
        Document $document,
        Twig_Environment $twig,
        array $documentAllowedMarks,
        array $documentAllowedNodes
    ) {
        $this->document = $document;
        $this->twig = $twig;
        $this->documentAllowedMarks = $documentAllowedMarks;
        $this->documentAllowedNodes = $documentAllowedNodes;
    }

    public function getAction(Request $request): Response
    {
        $team = $request->attributes->get('team');

        $context = [
            'allowed_marks' => $this->documentAllowedMarks,
            'allowed_nodes' => $this->documentAllowedNodes,
            'html_title' => sprintf('New doc – %s', $team->getName()),
            'team' => $team,
        ];

        return new Response(
            $this->twig->render('document/create_document.twig', $context)
        );
    }

    public function postAction(Request $request): JsonResponse
    {
        $person = $request->attributes->get('person');

        try {
            $model = $this->document->create($person, $request->request->all());
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

        return new JsonResponse([], 201);
    }
}
