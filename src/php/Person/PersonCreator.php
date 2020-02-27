<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\IdGenerator\IdGenerator;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\Storage\PersonInserter;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;

class PersonCreator
{
    private PersonInserter $personInserter;
    private PersonPasswordEncoder $passwordEncoder;
    private PersonRepository $personRepository;
    private IdGenerator $idGenerator;
    private UrlIdGenerator $urlIdGenerator;
    private UrlSlugGenerator $urlSlugGenerator;

    public function __construct(
        PersonInserter $personInserter,
        PersonPasswordEncoder $passwordEncoder,
        PersonRepository $personRepository,
        IdGenerator $idGenerator,
        UrlIdGenerator $urlIdGenerator,
        UrlSlugGenerator $urlSlugGenerator
    ) {
        $this->personInserter = $personInserter;
        $this->passwordEncoder = $passwordEncoder;
        $this->personRepository = $personRepository;
        $this->idGenerator = $idGenerator;
        $this->urlIdGenerator = $urlIdGenerator;
        $this->urlSlugGenerator = $urlSlugGenerator;
    }

    public function create(
        OrganizationModel $organization,
        string $name,
        string $emailAddress,
        string $rawPassword,
        bool $emailAddressVerified = false
    ): array {
        $id = $this->idGenerator->generate();
        $encodedPassword = $this->passwordEncoder->encodePassword($rawPassword);
        $abbreviatedName = $this->getAbbreviatedName($name);
        $urlId = $this->generateUrlId();
        $username = $this->generateUsername($name);
        $organizationId = $organization->getId();

        if ($this->usernameTaken($username, $organizationId)) {
            $username = $this->incrementUsername($username, $organizationId);
        }

        $person = $this->personInserter->insert(
            $id,
            $name,
            $abbreviatedName,
            $emailAddress,
            $encodedPassword,
            $urlId,
            $username,
            $organizationId,
            $emailAddressVerified
        );

        $model = PersonModel::createFromArray($person);

        // TODO: If email address verified send welcome email

        return [$model, $person['password']];
    }

    private function incrementUsername(string $username, string $organizationId): string
    {
        $result = $this->personRepository->getUsernamesLike($username, $organizationId);
        $likeUsernames = array_values($result);

        $i = 1;
        while (in_array($username . $i, $likeUsernames)) {
            ++$i;
        }

        return sprintf('%s%d', $username, $i);
    }

    private function usernameTaken(string $username, string $organizationId): bool
    {
        return $this->personRepository->existsWithUsername($username, $organizationId);
    }

    private function generateUsername(string $name): string
    {
        $slug = $this->urlSlugGenerator->generateFromString($name, '_');
        return sprintf('@%s', $slug);
    }

    private function generateUrlId(): string
    {
        return $this->urlIdGenerator->generate();
    }

    private function getAbbreviatedName(string $name): string
    {
        $nameRepresentation = new NameRepresentation($name);
        return $nameRepresentation->abbreviated();
    }
}
