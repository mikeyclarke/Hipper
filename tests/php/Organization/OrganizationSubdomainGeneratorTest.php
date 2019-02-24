<?php
declare(strict_types=1);

namespace Lithos\Tests\Organization;

use Lithos\Organization\OrganizationSubdomainGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrganizationSubdomainGeneratorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $validatorInterface;
    private $subdomainGenerator;

    public function setUp(): void
    {
        $this->validatorInterface = m::mock(ValidatorInterface::class);

        $this->subdomainGenerator = new OrganizationSubdomainGenerator(
            $this->validatorInterface
        );
    }

    /**
     * @test
     */
    public function invalidCharactersAreStripped()
    {
        $input = '🤔 achme= ā_ #';
        $expected = 'achme';

        $this->createValidatorInterfaceExpectation('achme', []);

        $result = $this->subdomainGenerator->generate($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function outerWhitespaceAndDashesAreStripped()
    {
        $input = '   achme----';
        $expected = 'achme';

        $this->createValidatorInterfaceExpectation('achme', []);

        $result = $this->subdomainGenerator->generate($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function duplicateWhitespaceIsStripped()
    {
        $input = 'acme   llp';
        $expected = 'acme-llp';

        $this->createValidatorInterfaceExpectation('acme-llp', []);

        $result = $this->subdomainGenerator->generate($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function whitespaceIsReplaced()
    {
        $input = 'acme limited liability partnership';
        $expected = 'acme-limited-liability-partnership';

        $this->createValidatorInterfaceExpectation('acme-limited-liability-partnership', []);

        $result = $this->subdomainGenerator->generate($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function isLowercased()
    {
        $input = 'acme LLP';
        $expected = 'acme-llp';

        $this->createValidatorInterfaceExpectation('acme-llp', []);

        $result = $this->subdomainGenerator->generate($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function isCheckedAgainstBlacklist()
    {
        $input = 'apple';
        $expected = '';

        $this->createValidatorInterfaceExpectation('apple', [['constraint-violation']]);

        $result = $this->subdomainGenerator->generate($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function noDuplicateDashes()
    {
        $input = 'achme- limited --liability partnership';
        $expected = 'achme-limited-liability-partnership';

        $this->createValidatorInterfaceExpectation('achme-limited-liability-partnership', []);

        $result = $this->subdomainGenerator->generate($input);
        $this->assertEquals($expected, $result);
    }

    private function createValidatorInterfaceExpectation($value, $result)
    {
        $this->validatorInterface
            ->shouldReceive('validate')
            ->once()
            ->with($value, m::type('array'))
            ->andReturn($result);
    }
}
