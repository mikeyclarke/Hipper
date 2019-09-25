<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Carbon\Carbon;
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KnowledgebaseEntriesListFormatter
{
    private $router;

    public function __construct(
        UrlGeneratorInterface $router
    ) {
        $this->router = $router;
    }

    public function format(
        array $documents,
        array $sections,
        string $displayTimeZone,
        string $routeName,
        array $routeParams
    ): array {
        $entries = [];

        foreach ($documents as $document) {
            $entries[] = $this->formatEntry($document, $displayTimeZone, $routeName, $routeParams, 'document');
        }

        foreach ($sections as $section) {
            $entries[] = $this->formatEntry($section, $displayTimeZone, $routeName, $routeParams, 'section');
        }

        usort($entries, function ($a, $b) {
            return $b['sortDateTime'] - $a['sortDateTime'];
        });

        return $entries;
    }

    private function formatEntry(array $entry, string $displayTimeZone, $routeName, $routeParams, $type): array
    {
        $dateTime = Carbon::createFromFormat('Y-m-d H:i:s.u', $entry['updated']);
        if (false === $dateTime) {
            throw new RuntimeException('DateTime could not be created from format');
        }
        $dateTime = $dateTime->tz($displayTimeZone);
        return [
            'description' =>
                ($type === 'section') ? $this->getSectionDescription($entry) : $this->getDocDescription($entry),
            'id' => $entry['id'],
            'name' => $entry['name'],
            'route' => $this->router->generate(
                $routeName,
                array_merge(
                    $routeParams,
                    ['path' => sprintf('%s~%s', $entry['route'], $entry['url_id'])]
                )
            ),
            'type' => $type,
            'updated' => [
                'utc_datetime' => $dateTime->toISOString(),
                'time_ago' => $dateTime->diffForHumans(),
                'verbose' => $dateTime->toDayDateTimeString(),
            ],
            'sortDateTime' => $dateTime->unix(),
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
