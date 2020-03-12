<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\DateTime\TimestampFormatter;
use Hipper\Organization\OrganizationModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PeopleListFormatter
{
    const PERSON_ROUTE_NAME = 'front_end.app.person.show';

    private TimestampFormatter $timestampFormatter;
    private UrlGeneratorInterface $router;

    public function __construct(
        TimestampFormatter $timestampFormatter,
        UrlGeneratorInterface $router
    ) {
        $this->timestampFormatter = $timestampFormatter;
        $this->router = $router;
    }

    public function format(OrganizationModel $organization, array $people, string $displayTimeZone): array
    {
        $result = array_map(
            function ($person) use ($displayTimeZone, $organization) {
                return [
                    'abbreviated_name' => $person['abbreviated_name'],
                    'created' => $this->timestampFormatter->format($person['created'], $displayTimeZone),
                    'email_address' => $person['email_address'],
                    'id' => $person['id'],
                    'job_role_or_title' => $person['job_role_or_title'],
                    'name' => $person['name'],
                    'route' => $this->router->generate(
                        self::PERSON_ROUTE_NAME,
                        [
                            'url_id' => $person['url_id'],
                            'username' => $person['username'],
                            'subdomain' => $organization->getSubdomain(),
                        ]
                    ),
                ];
            },
            $people
        );

        return $result;
    }
}
