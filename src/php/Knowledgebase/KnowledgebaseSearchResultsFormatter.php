<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\DateTime\TimestampFormatter;
use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KnowledgebaseSearchResultsFormatter
{
    use \Hipper\Search\SearchResultsFormatterTrait;

    private TimestampFormatter $timestampFormatter;
    private UrlGeneratorInterface $router;

    public function __construct(
        TimestampFormatter $timestampFormatter,
        UrlGeneratorInterface $router
    ) {
        $this->timestampFormatter = $timestampFormatter;
        $this->router = $router;
    }

    public function format(
        OrganizationModel $organization,
        array $knowledgebaseOwners,
        array $ancestorTree,
        string $displayTimeZone,
        array $results
    ): array {
        return array_map(
            function ($result) use ($knowledgebaseOwners, $ancestorTree, $organization, $displayTimeZone) {
                $knowledgebaseOwner = $knowledgebaseOwners[$result['knowledgebase_id']] ?? null;
                if (null === $knowledgebaseOwner) {
                    throw new RuntimeException('Knowledgebase not found');
                }

                $knowledgebaseOwnerType = $this->getKnowledgebaseOwnerType($knowledgebaseOwner);
                list($routeName, $routeParams) = $this->getRouteDetails($knowledgebaseOwner);

                if (null === $knowledgebaseOwnerType) {
                    $owners = ["{$knowledgebaseOwner->getName()} docs"];
                } else {
                    $owners = ["{$knowledgebaseOwner->getName()} {$knowledgebaseOwnerType} docs"];
                }

                $parentTopicId = $result['parent_topic_id'];
                if (null !== $parentTopicId) {
                    if (!isset($ancestorTree[$parentTopicId])) {
                        throw new RuntimeException(sprintf('Topic “%s” not found', $parentTopicId));
                    }
                    $owners = [...$owners, ...$ancestorTree[$parentTopicId]];
                }

                return [
                    'name' => $result['name'],
                    'raw_snippet' => $this->getSnippet($result, ['content_snippet', 'description_snippet']),
                    'description' => $this->getDescription($result),
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
                    'timestamp' => $this->timestampFormatter->format($result['updated'], $displayTimeZone),
                    'owners' => $owners,
                ];
            },
            $results
        );
    }

    private function getDescription(array $result): string
    {
        if (!empty($result['description'])) {
            return $result['description'];
        }

        if (!empty($result['deduced_description'])) {
            return $result['deduced_description'];
        }

        if ($result['entry_type'] === 'topic') {
            return 'This topic doesn’t have a description yet.';
        }

        return 'This doc doesn’t have a description yet.';
    }

    private function getRouteDetails(KnowledgebaseOwnerModelInterface $knowledgebaseOwner): array
    {
        if ($knowledgebaseOwner instanceof ProjectModel) {
            return [
                KnowledgebaseRouteUrlGenerator::SHOW_PROJECT_DOC_ROUTE_NAME,
                ['project_url_slug' => $knowledgebaseOwner->getUrlSlug()]
            ];
        }

        if ($knowledgebaseOwner instanceof TeamModel) {
            return [
                KnowledgebaseRouteUrlGenerator::SHOW_TEAM_DOC_ROUTE_NAME,
                ['team_url_slug' => $knowledgebaseOwner->getUrlSlug()]
            ];
        }

        if ($knowledgebaseOwner instanceof OrganizationModel) {
            return [
                KnowledgebaseRouteUrlGenerator::SHOW_ORGANIZATION_DOC_ROUTE_NAME,
                []
            ];
        }

        throw new UnsupportedKnowledgebaseEntityException;
    }

    private function getKnowledgebaseOwnerType(KnowledgebaseOwnerModelInterface $knowledgebaseOwner): ?string
    {
        if ($knowledgebaseOwner instanceof ProjectModel) {
            return 'project';
        }

        if ($knowledgebaseOwner instanceof TeamModel) {
            return 'team';
        }

        if ($knowledgebaseOwner instanceof OrganizationModel) {
            return null;
        }

        throw new UnsupportedKnowledgebaseEntityException;
    }
}
