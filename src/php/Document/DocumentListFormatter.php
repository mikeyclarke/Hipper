<?php
declare(strict_types=1);

namespace Hipper\Document;

use Carbon\Carbon;
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DocumentListFormatter
{
    private $router;

    public function __construct(
        UrlGeneratorInterface $router
    ) {
        $this->router = $router;
    }

    public function format(array $documents, string $displayTimeZone, string $routeName, array $routeParams): array
    {
        return array_map(
            function ($document) use ($displayTimeZone, $routeName, $routeParams) {
                $dateTime = Carbon::createFromFormat('Y-m-d H:i:s.u', $document['updated']);
                if (false === $dateTime) {
                    throw new RuntimeException('DateTime could not be created from format');
                }
                $dateTime = $dateTime->tz($displayTimeZone);
                return [
                    'description' => $this->getDescription($document),
                    'id' => $document['id'],
                    'name' => $document['name'],
                    'route' => $this->router->generate(
                        $routeName,
                        array_merge(
                            $routeParams,
                            ['path' => sprintf('%s~%s', $document['route'], $document['url_id'])]
                        )
                    ),
                    'updated' => [
                        'utc_datetime' => $dateTime->toISOString(),
                        'time_ago' => $dateTime->diffForHumans(),
                        'verbose' => $dateTime->toDayDateTimeString(),
                    ],
                ];
            },
            $documents
        );
    }

    private function getDescription(array $document): string
    {
        if (null !== $document['description'] && !empty($document['description'])) {
            return $document['description'];
        }

        if (null !== $document['deduced_description'] && !empty($document['deduced_description'])) {
            return $document['deduced_description'];
        }

        return 'This doc doesn’t have a description yet.';
    }
}
