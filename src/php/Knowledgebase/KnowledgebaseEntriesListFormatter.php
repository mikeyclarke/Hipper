<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\DateTime\TimestampFormatter;
use Hipper\Organization\OrganizationModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KnowledgebaseEntriesListFormatter
{
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
        array $documents,
        array $sections,
        string $displayTimeZone,
        string $routeName,
        array $routeParams
    ): array {
        $entries = [];

        foreach ($documents as $document) {
            $entries[] = $this->formatEntry(
                $organization,
                $document,
                $displayTimeZone,
                $routeName,
                $routeParams,
                'document'
            );
        }

        foreach ($sections as $section) {
            $entries[] = $this->formatEntry(
                $organization,
                $section,
                $displayTimeZone,
                $routeName,
                $routeParams,
                'section'
            );
        }

        usort($entries, function ($a, $b) {
            return $b['updated']['unix'] - $a['updated']['unix'];
        });

        return $entries;
    }

    private function formatEntry(
        OrganizationModel $organization,
        array $entry,
        string $displayTimeZone,
        $routeName,
        $routeParams,
        $type
    ): array {
        return [
            'description' =>
                ($type === 'section') ? $this->getSectionDescription($entry) : $this->getDocDescription($entry),
            'id' => $entry['id'],
            'name' => $entry['name'],
            'route' => $this->router->generate(
                $routeName,
                array_merge(
                    $routeParams,
                    [
                        'path' => sprintf('%s~%s', $entry['route'], $entry['url_id']),
                        'subdomain' => $organization->getSubdomain(),
                    ]
                )
            ),
            'type' => $type,
            'updated' => $this->timestampFormatter->format($entry['updated'], $displayTimeZone),
        ];
    }

    private function getSectionDescription(array $entry): string
    {
        if (null !== $entry['description'] && !empty($entry['description'])) {
            return $entry['description'];
        }

        return 'This section doesn’t have a description yet.';
    }

    private function getDocDescription(array $entry): string
    {
        if (null !== $entry['description'] && !empty($entry['description'])) {
            return $entry['description'];
        }

        if (null !== $entry['deduced_description'] && !empty($entry['deduced_description'])) {
            return $entry['deduced_description'];
        }

        return 'This doc doesn’t have a description yet.';
    }
}
