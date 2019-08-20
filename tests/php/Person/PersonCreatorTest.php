<?php
declare(strict_types=1);

namespace Hipper\Tests\Person;

use Hipper\IdGenerator\IdGenerator;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonInserter;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonModelMapper;
use Hipper\Person\PersonPasswordEncoder;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PersonCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $personInserter;
    private $personModelMapper;
    private $passwordEncoder;
    private $idGenerator;
    private $personCreator;

    public function setUp(): void
    {
        $this->personInserter = m::mock(PersonInserter::class);
        $this->personModelMapper = m::mock(PersonModelMapper::class);
        $this->passwordEncoder = m::mock(PersonPasswordEncoder::class);
        $this->idGenerator = m::mock(IdGenerator::class);

        $this->personCreator = new PersonCreator(
            $this->personInserter,
            $this->personModelMapper,
            $this->passwordEncoder,
            $this->idGenerator
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $organization = new OrganizationModel;
        $organization->setId('16fd2706-8baf-433b-82eb-8c7fada847da');
        $name = 'Mikey Clarke';
        $abbreviatedName = 'MC';
        $emailAddress = 'mikey@usehipper.com';
        $rawPassword = 'foobar';

        $personId = '20fd0d4f-132f-43af-9280-e4565bf2a44e';
        $encodedPassword = 'encoded-password';
        $personRow = [
            'id' => $personId,
            'password' => $encodedPassword,
        ];
        $personModel = new PersonModel;

        $this->createIdGeneratorExpectation($personId);
        $this->createPasswordEncoderExpectation($rawPassword, $encodedPassword);
        $this->createPersonInserterExpectation(
            $personId,
            $name,
            $abbreviatedName,
            $emailAddress,
            $encodedPassword,
            $organization->getId(),
            false,
            $personRow
        );
        $this->createPersonModelMapperExpectation($personRow, $personModel);

        $result = $this->personCreator->create($organization, $name, $emailAddress, $rawPassword);
        $this->assertEquals([$personModel, $encodedPassword], $result);
    }

    /**
     * @test
     */
    public function createWithEmailAddressVerified()
    {
        $organization = new OrganizationModel;
        $organization->setId('16fd2706-8baf-433b-82eb-8c7fada847da');
        $name = 'Mikey Clarke';
        $abbreviatedName = 'MC';
        $emailAddress = 'mikey@usehipper.com';
        $rawPassword = 'foobar';
        $emailAddressVerified = true;

        $personId = '20fd0d4f-132f-43af-9280-e4565bf2a44e';
        $encodedPassword = 'encoded-password';
        $personRow = [
            'id' => $personId,
            'password' => $encodedPassword,
        ];
        $personModel = new PersonModel;

        $this->createIdGeneratorExpectation($personId);
        $this->createPasswordEncoderExpectation($rawPassword, $encodedPassword);
        $this->createPersonInserterExpectation(
            $personId,
            $name,
            $abbreviatedName,
            $emailAddress,
            $encodedPassword,
            $organization->getId(),
            $emailAddressVerified,
            $personRow
        );
        $this->createPersonModelMapperExpectation($personRow, $personModel);

        $result = $this->personCreator->create(
            $organization,
            $name,
            $emailAddress,
            $rawPassword,
            $emailAddressVerified
        );
        $this->assertEquals([$personModel, $encodedPassword], $result);
    }

    private function createPersonModelMapperExpectation($personRow, $result)
    {
        $this->personModelMapper
            ->shouldReceive('createFromArray')
            ->once()
            ->with($personRow)
            ->andReturn($result);
    }

    private function createPersonInserterExpectation(
        $personId,
        $name,
        $abbreviatedName,
        $emailAddress,
        $encodedPassword,
        $organizationId,
        $emailAddressVerified,
        $result
    ) {
        $this->personInserter
            ->shouldReceive('insert')
            ->once()
            ->with(
                $personId,
                $name,
                $abbreviatedName,
                $emailAddress,
                $encodedPassword,
                $organizationId,
                $emailAddressVerified
            )
            ->andReturn($result);
    }

    private function createPasswordEncoderExpectation($rawPassword, $result)
    {
        $this->passwordEncoder
            ->shouldReceive('encodePassword')
            ->once()
            ->with($rawPassword)
            ->andReturn($result);
    }

    private function createIdGeneratorExpectation($result)
    {
        $this->idGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
    }
}
