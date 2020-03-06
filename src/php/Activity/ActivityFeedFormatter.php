<?php
declare(strict_types=1);

namespace Hipper\Activity;

use Hipper\Activity\Exception\StorageDecodeException;
use Hipper\DateTime\TimestampFormatter;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonModel;
use JsonException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActivityFeedFormatter
{
    private const SHOW_PERSON_ROUTE_NAME = 'front_end.app.person.show';

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
        PersonModel $currentUser,
        string $displayTimeZone,
        array $feedEntries
    ): array {
        $result = array_map(
            function ($entry) use ($organization, $currentUser, $displayTimeZone) {
                $isCurrentUserActor = ($currentUser->getId() === $entry['actor_id']);

                return [
                    'actor' => [
                        'abbreviated_name' => $entry['actor_abbreviated_name'],
                        'is_current_user' => $isCurrentUserActor,
                        'name' => $isCurrentUserActor ? 'You' : $entry['actor_name'],
                        'route' => $this->getActorRoute($organization, $entry),
                    ],
                    'created' => $this->timestampFormatter->format($entry['created'], $displayTimeZone),
                    'type' => $entry['type'],
                    'properties' => $this->getEntryProperties($entry),
                ];
            },
            $feedEntries
        );

        return $result;
    }

    private function getActorRoute(OrganizationModel $organization, array $entry): string
    {
        return $this->router->generate(
            self::SHOW_PERSON_ROUTE_NAME,
            [
                'subdomain' => $organization->getSubdomain(),
                'url_id' => $entry['actor_url_id'],
                'username' => $entry['actor_username'],
            ]
        );
    }

    private function getEntryProperties(array $entry): ?array
    {
        if (null === $entry['storage']) {
            return null;
        }

        try {
            $decoded = json_decode($entry['storage'], true, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new StorageDecodeException;
        }
        return $decoded;
    }
}
