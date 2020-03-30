<?php
declare(strict_types=1);

namespace Hipper\Tests\Validation\Constraints;

use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Hipper\Validation\Constraints\SignUpAuthorizationPhrase;
use Hipper\Validation\Constraints\SignUpAuthorizationPhraseValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class SignUpAuthorizationPhraseValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new SignUpAuthorizationPhraseValidator;
    }

    /**
     * @test
     */
    public function validExample()
    {
        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray([
            'verification_phrase' => 'foo bar baz qux',
        ]);

        $value = 'foo bar baz qux';
        $this->validator->validate(
            $value,
            new SignUpAuthorizationPhrase(['authorizationRequest' => $authorizationRequest])
        );

        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function invalidExample()
    {
        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray([
            'verification_phrase' => 'foo bar baz qux',
        ]);

        $value = 'not the right phrase';
        $this->validator->validate(
            $value,
            new SignUpAuthorizationPhrase(['authorizationRequest' => $authorizationRequest])
        );

        $message = 'Incorrect verification phrase.';
        $this->buildViolation($message)
            ->assertRaised();
    }
}
