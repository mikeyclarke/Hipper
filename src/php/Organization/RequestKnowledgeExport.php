<?php

declare(strict_types=1);

namespace Hipper\Organization;

use Hipper\Messenger\MessageBus;
use Hipper\Messenger\Message\OrganizationKnowledgeExportRequest;
use Hipper\Organization\OrganizationModel;
use Hipper\Validation\ConstraintViolationListFormatter;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestKnowledgeExport
{
    private const MAX_RECIPIENTS = 20;

    private MessageBus $messageBus;
    private ValidatorInterface $validator;

    public function __construct(
        MessageBus $messageBus,
        ValidatorInterface $validator
    ) {
        $this->messageBus = $messageBus;
        $this->validator = $validator;
    }

    public function createRequest(OrganizationModel $organization, array $input): void
    {
        $this->validate($input);

        $message = new OrganizationKnowledgeExportRequest($organization->getId(), $input['recipient_email_addresses']);
        $this->messageBus->dispatch($message);
    }

    private function validate(array $input): void
    {
        $constraints = [
            'recipient_email_addresses' => [
                new Count([
                    'min' => 1,
                    'max' => self::MAX_RECIPIENTS,
                    'minMessage' => 'At least one recipient email address must be provided.',
                    'maxMessage' => 'You cannot send the export to more than {{ limit }} recipients.',
                ]),
                new All([
                    new NotBlank,
                    new Email,
                ]),
            ],
        ];

        $collectionConstraint = new Collection($constraints);

        $violations = $this->validator->validate($input, $collectionConstraint);

        if (count($violations) > 0) {
            throw new ValidationException(
                ConstraintViolationListFormatter::format($violations)
            );
        }
    }
}
