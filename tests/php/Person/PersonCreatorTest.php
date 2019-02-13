<?php
declare(strict_types=1);

namespace Lithos\Tests\Person;

use Lithos\IdGenerator\IdGenerator;
use Lithos\Organization\OrganizationModel;
use Lithos\Person\PersonCreator;
use Lithos\Person\PersonInserter;
use Lithos\Person\PersonMetadataInserter;
use Lithos\Person\PersonModel;
use Lithos\Person\PersonModelMapper;
use Lithos\Person\PersonPasswordEncoderFactory;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\Argon2iPasswordEncoder;

class PersonCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $personInserter;
    private $personMetadataInserter;
    private $personModelMapper;
    private $encoderFactory;
    private $idGenerator;
    private $personCreator;
    private $passwordEncoder;

    public function setUp(): void
    {
        $this->personInserter = m::mock(PersonInserter::class);
        $this->personMetadataInserter = m::mock(PersonMetadataInserter::class);
        $this->personModelMapper = m::mock(PersonModelMapper::class);
        $this->encoderFactory = m::mock(PersonPasswordEncoderFactory::class);
        $this->idGenerator = m::mock(IdGenerator::class);

        $this->personCreator = new PersonCreator(
            $this->personInserter,
            $this->personMetadataInserter,
            $this->personModelMapper,
            $this->encoderFactory,
            $this->idGenerator
        );

        $this->passwordEncoder = m::mock(Argon2iPasswordEncoder::class);
    }

    /**
     * @test
     */
    public function create()
    {
        $organization = new OrganizationModel;
        $organization->setId('16fd2706-8baf-433b-82eb-8c7fada847da');
        $name = 'Mikey Clarke';
        $emailAddress = 'mikey@tryhleo.com';
        $rawPassword = 'foobar';

        $personId = '20fd0d4f-132f-43af-9280-e4565bf2a44e';
        $encodedPassword = 'encoded-password';
        $personRow = [
            'id' => $personId,
            'password' => $encodedPassword,
        ];
        $metadataId = '63a16b95-2550-438a-a6fb-90b544e2626a';
        $personModel = new PersonModel;

        $this->createEncoderFactoryExpectation();
        $this->createIdGeneratorExpectation($personId);
        $this->createPasswordEncoderExpectation($rawPassword, $encodedPassword);
        $this->createPersonInserterExpectation(
            $personId,
            $name,
            $emailAddress,
            $encodedPassword,
            $organization->getId(),
            false,
            $personRow
        );
        $this->createIdGeneratorExpectation($metadataId);
        $this->createPersonMetadataInserterExpectation($metadataId, $personId);
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
        $emailAddress = 'mikey@tryhleo.com';
        $rawPassword = 'foobar';
        $emailAddressVerified = true;

        $personId = '20fd0d4f-132f-43af-9280-e4565bf2a44e';
        $encodedPassword = 'encoded-password';
        $personRow = [
            'id' => $personId,
            'password' => $encodedPassword,
        ];
        $metadataId = '63a16b95-2550-438a-a6fb-90b544e2626a';
        $personModel = new PersonModel;

        $this->createEncoderFactoryExpectation();
        $this->createIdGeneratorExpectation($personId);
        $this->createPasswordEncoderExpectation($rawPassword, $encodedPassword);
        $this->createPersonInserterExpectation(
            $personId,
            $name,
            $emailAddress,
            $encodedPassword,
            $organization->getId(),
            $emailAddressVerified,
            $personRow
        );
        $this->createIdGeneratorExpectation($metadataId);
        $this->createPersonMetadataInserterExpectation($metadataId, $personId);
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

    private function createPersonMetadataInserterExpectation($id, $personId)
    {
        $this->personMetadataInserter
            ->shouldReceive('insert')
            ->once()
            ->with($id, $personId);
    }

    private function createPersonInserterExpectation(
        $personId,
        $name,
        $emailAddress,
        $encodedPassword,
        $organizationId,
        $emailAddressVerified,
        $result
    ) {
        $this->personInserter
            ->shouldReceive('insert')
            ->once()
            ->with($personId, $name, $emailAddress, $encodedPassword, $organizationId, $emailAddressVerified)
            ->andReturn($result);
    }

    private function createPasswordEncoderExpectation($rawPassword, $result)
    {
        $this->passwordEncoder
            ->shouldReceive('encodePassword')
            ->once()
            ->with($rawPassword, null)
            ->andReturn($result);
    }

    private function createIdGeneratorExpectation($result)
    {
        $this->idGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
    }

    private function createEncoderFactoryExpectation()
    {
        $this->encoderFactory
            ->shouldReceive('create')
            ->once()
            ->andReturn($this->passwordEncoder);
    }
}
