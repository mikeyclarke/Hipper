<?php
declare(strict_types=1);

namespace Hipper\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Hipper\EmailAddressVerification\RequestEmailAddressVerification;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\Exception\ApprovedEmailDomainSignupNotAllowedException;
use Hipper\Person\Exception\MalformedEmailAddressException;
use Hipper\Person\PersonCreationValidator;
use Hipper\Person\PersonCreator;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateFromApprovedEmailDomain
{
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;
    private PersonCreationValidator $personCreationValidator;
    private PersonCreator $personCreator;
    private RequestEmailAddressVerification $requestEmailAddressVerification;

    public function __construct(
        Connection $connection,
        EventDispatcherInterface $eventDispatcher,
        PersonCreationValidator $personCreationValidator,
        PersonCreator $personCreator,
        RequestEmailAddressVerification $requestEmailAddressVerification
    ) {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->personCreationValidator = $personCreationValidator;
        $this->personCreator = $personCreator;
        $this->requestEmailAddressVerification = $requestEmailAddressVerification;
    }

    public function create(OrganizationModel $organization, array $input): array
    {
        $this->personCreationValidator->validate($input, 'approved_email_domain');

        if (!$organization->isApprovedEmailDomainSignupAllowed()) {
            throw new ApprovedEmailDomainSignupNotAllowedException;
        }

        if (!$this->isEmailDomainAllowed($organization, $input)) {
            throw new ValidationException([
                'email_address' => [
                    'Email address domain is not approved for sign-up.',
                ]
            ]);
        }

        $this->connection->beginTransaction();
        try {
            list($person, $encodedPassword) = $this->personCreator->create(
                $organization,
                $input['name'],
                $input['email_address'],
                $input['password']
            );
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $this->requestEmailAddressVerification->sendVerificationRequest($person);

        $personCreatedEvent = new PersonCreatedEvent($person);
        $this->eventDispatcher->dispatch($personCreatedEvent, PersonCreatedEvent::NAME);

        return [$person, $encodedPassword];
    }

    private function isEmailDomainAllowed(OrganizationModel $organization, array $input): bool
    {
        $allowedDomains = $organization->getApprovedEmailDomains();
        if (null === $allowedDomains) {
            return false;
        }

        $parts = explode('@', $input['email_address']);
        if (count($parts) !== 2) {
            throw new MalformedEmailAddressException;
        }

        $inputDomain = $parts[1];
        return in_array($inputDomain, $allowedDomains);
    }
}
