<?php
declare(strict_types=1);

namespace Hipper\SignUp\AuthorizationValidation;

use Hipper\Organization\OrganizationModel;
use Hipper\Validation\Constraints\ApprovedEmailDomain;
use Hipper\Validation\Constraints\UniqueEmailAddress;
use Hipper\Validation\ConstraintViolationListFormatter;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApprovedEmailDomainSignUpAuthorizationValidator
{
    private ValidatorInterface $validatorInterface;

    public function __construct(
        ValidatorInterface $validatorInterface
    ) {
        $this->validatorInterface = $validatorInterface;
    }

    public function validate(array $input, OrganizationModel $organization): void
    {
        $this->validateInput($input, $organization);
    }

    private function validateInput(array $input, OrganizationModel $organization): void
    {
        $constraints = [
            'name' => new Required([
                new Length([
                    'min' => 3,
                    'max' => 100,
                ]),
            ]),
            'email_address' => new Required([
                new NotBlank,
                new Email([
                    'mode' => 'html5',
                ]),
                new UniqueEmailAddress,
                new ApprovedEmailDomain(['organization' => $organization]),
            ]),
            'password' => new Required([
                new Length([
                    'min' => 8,
                    'max' => 160,
                ]),
            ]),
            'terms_agreed' => new Required([
                new NotBlank,
                new IsTrue,
            ]),
        ];

        $collectionConstraint = new Collection($constraints);
        $violations = $this->validatorInterface->validate($input, $collectionConstraint);

        if (count($violations) > 0) {
            throw new ValidationException(
                ConstraintViolationListFormatter::format($violations)
            );
        }
    }
}
