<?php
declare(strict_types=1);

namespace Hipper\Tests\Validation\Constraints;

use Hipper\Organization\OrganizationModel;
use Hipper\Validation\Constraints\ApprovedEmailDomain;
use Hipper\Validation\Constraints\ApprovedEmailDomainValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ApprovedEmailDomainValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new ApprovedEmailDomainValidator;
    }

    /**
     * @test
     */
    public function validExample()
    {
        $organization = OrganizationModel::createFromArray([
            'approved_email_domain_signup_allowed' => true,
            'approved_email_domains' => '["usehipper.com"]',
            'name' => 'Acme',
        ]);

        $value = 'mikey@usehipper.com';
        $this->validator->validate($value, new ApprovedEmailDomain(['organization' => $organization]));

        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function approvedEmailDomainSignupNotAllowed()
    {
        $organization = OrganizationModel::createFromArray([
            'approved_email_domain_signup_allowed' => false,
            'approved_email_domains' => '["usehipper.com"]',
            'name' => 'Acme',
        ]);

        $value = 'mikey@usehipper.com';
        $this->validator->validate($value, new ApprovedEmailDomain(['organization' => $organization]));

        $message = 'You’ll need an invite to join {{ organization_name }} on Hipper';
        $this->buildViolation($message)
            ->setParameter('{{ organization_name }}', 'Acme')
            ->assertRaised();
    }

    /**
     * @test
     */
    public function notApprovedEmailDomain()
    {
        $organization = OrganizationModel::createFromArray([
            'approved_email_domain_signup_allowed' => true,
            'approved_email_domains' => '["usehipper.com"]',
        ]);

        $value = 'mikey@example.com';
        $this->validator->validate($value, new ApprovedEmailDomain(['organization' => $organization]));

        $message = '“{{ domain }}” is not an approved sign-up domain';
        $this->buildViolation($message)
            ->setParameter('{{ domain }}', 'example.com')
            ->assertRaised();
    }
}
