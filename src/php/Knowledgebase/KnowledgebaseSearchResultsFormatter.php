<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Carbon\Carbon;
use Hipper\Document\Renderer\HtmlEscaper;
use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KnowledgebaseSearchResultsFormatter
{
    private $router;

    public function __construct(
        UrlGeneratorInterface $router
    ) {
        $this->router = $router;
    }

    public function format(
        OrganizationModel $organization,
        array $knowledgebaseOwners,
        string $displayTimeZone,
        array $results
    ): array {
        return array_map(
            function ($result) use ($knowledgebaseOwners, $organization, $displayTimeZone) {
                $dateTime = Carbon::createFromFormat('Y-m-d H:i:s.u', $result['updated']);
                if (false === $dateTime) {
                    throw new RuntimeException('DateTime could not be created from format');
                }
                $dateTime = $dateTime->tz($displayTimeZone);

                $knowledgebaseOwner = $knowledgebaseOwners[$result['knowledgebase_id']] ?? null;
                if (null === $knowledgebaseOwner) {
                    throw new RuntimeException('Knowledgebase not found');
                }

                $knowledgebaseOwnerType = $this->getKnowledgebaseOwnerType($knowledgebaseOwner);
                list($routeName, $routeParams) = $this->getRouteDetails($knowledgebaseOwner);

                return [
                    'name' => $result['name'],
                    'displayBody' => $this->getDisplayBody($result),
                    'route' => $this->router->generate(
                        $routeName,
                        array_merge(
                            $routeParams,
                            [
                                'path' => sprintf('%s~%s', $result['route'], $result['url_id']),
                                'subdomain' => $organization->getSubdomain(),
                            ]
                        )
                    ),
                    'type' => $result['entry_type'],
                    'updated' => [
                        'utc_datetime' => $dateTime->toISOString(),
                        'time_ago' => $dateTime->diffForHumans(),
                        'verbose' => $dateTime->toDayDateTimeString(),
                    ],
                    'owner' => [
                        'name' => $knowledgebaseOwner->getName(),
                        'type' => $knowledgebaseOwnerType,
                    ],
                ];
            },
            $results
        );
    }

    private function getDisplayBody(array $result): string
    {
        if (!empty($result['content_snippet'])) {
            return $this->escapeText($result['content_snippet']);
        }

        if (!empty($result['description_snippet'])) {
            return $this->escapeText($result['description_snippet']);
        }

        if (!empty($result['description'])) {
            return $this->escapeText($result['description']);
        }

        if (!empty($result['deduced_description'])) {
            return $this->escapeText($result['deduced_description']);
        }

        if ($result['entry_type'] === 'section') {
            return 'This section doesn’t have a description yet.';
        }

        return 'This doc doesn’t have a description yet.';
    }

    private function escapeText(string $text): string
    {
        $result = HtmlEscaper::escapeInnerText($text);
        $result = str_replace('&lt;mark&gt;', '<mark>', $text);
        $result = str_replace('&lt;/mark&gt;', '</mark>', $text);
        return $result;
    }

    private function getRouteDetails(KnowledgebaseOwnerModelInterface $knowledgebaseOwner): array
    {
        if ($knowledgebaseOwner instanceof ProjectModel) {
            return [
                KnowledgebaseRouteUrlGenerator::SHOW_PROJECT_DOC_ROUTE_NAME,
                ['project_url_id' => $knowledgebaseOwner->getUrlId()]
            ];
        }

        if ($knowledgebaseOwner instanceof TeamModel) {
            return [
                KnowledgebaseRouteUrlGenerator::SHOW_TEAM_DOC_ROUTE_NAME,
                ['team_url_id' => $knowledgebaseOwner->getUrlId()]
            ];
        }

        throw new UnsupportedKnowledgebaseEntityException;
    }

    private function getKnowledgebaseOwnerType(KnowledgebaseOwnerModelInterface $knowledgebaseOwner): string
    {
        if ($knowledgebaseOwner instanceof ProjectModel) {
            return 'project';
        }

        if ($knowledgebaseOwner instanceof TeamModel) {
            return 'team';
        }

        throw new UnsupportedKnowledgebaseEntityException;
    }
}
