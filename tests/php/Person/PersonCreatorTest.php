<?php
declare(strict_types=1);

namespace Hipper\Tests\Person;

use Hipper\IdGenerator\IdGenerator;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonCreator;
use Hipper\Person\Storage\PersonInserter;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonPasswordEncoder;
use Hipper\Person\PersonRepository;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PersonCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $personInserter;
    private $passwordEncoder;
    private $personRepository;
    private $idGenerator;
    private $urlIdGenerator;
    private $urlSlugGenerator;
    private $personCreator;

    public function setUp(): void
    {
        $this->personInserter = m::mock(PersonInserter::class);
        $this->passwordEncoder = m::mock(PersonPasswordEncoder::class);
        $this->personRepository = m::mock(PersonRepository::class);
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->urlIdGenerator = m::mock(UrlIdGenerator::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->personCreator = new PersonCreator(
            $this->personInserter,
            $this->passwordEncoder,
            $this->personRepository,
            $this->idGenerator,
            $this->urlIdGenerator,
            $this->urlSlugGenerator
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
        $urlId = 'abcd1234';
        $sluggifiedName = 'mikey_clarke';
        $username = '@' . $sluggifiedName;
        $personRow = [
            'id' => $personId,
            'password' => $encodedPassword,
        ];

        $this->createIdGeneratorExpectation($personId);
        $this->createPasswordEncoderExpectation($rawPassword, $encodedPassword);
        $this->createUrlIdGeneratorExpectation($urlId);
        $this->createUrlSlugGeneratorExpectation([$name, '_'], $sluggifiedName);
        $this->createPersonRepositoryExistsWithUsernameExpectation([$username, $organization->getId()], false);
        $this->createPersonInserterExpectation(
            [
                $personId,
                $name,
                $abbreviatedName,
                $emailAddress,
                $encodedPassword,
                $urlId,
                $username,
                $organization->getId(),
                false,
            ],
            $personRow
        );

        $result = $this->personCreator->create($organization, $name, $emailAddress, $rawPassword);
        $this->assertIsArray($result);
        $this->assertInstanceOf(PersonModel::class, $result[0]);
        $this->assertEquals($encodedPassword, $result[1]);
        $this->assertEquals($personId, $result[0]->getId());
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
        $urlId = 'abcd1234';
        $sluggifiedName = 'mikey_clarke';
        $username = '@' . $sluggifiedName;
        $personRow = [
            'id' => $personId,
            'password' => $encodedPassword,
        ];

        $this->createIdGeneratorExpectation($personId);
        $this->createPasswordEncoderExpectation($rawPassword, $encodedPassword);
        $this->createUrlIdGeneratorExpectation($urlId);
        $this->createUrlSlugGeneratorExpectation([$name, '_'], $sluggifiedName);
        $this->createPersonRepositoryExistsWithUsernameExpectation([$username, $organization->getId()], false);
        $this->createPersonInserterExpectation(
            [
                $personId,
                $name,
                $abbreviatedName,
                $emailAddress,
                $encodedPassword,
                $urlId,
                $username,
                $organization->getId(),
                $emailAddressVerified,
            ],
            $personRow
        );

        $result = $this->personCreator->create(
            $organization,
            $name,
            $emailAddress,
            $rawPassword,
            $emailAddressVerified
        );
        $this->assertIsArray($result);
        $this->assertInstanceOf(PersonModel::class, $result[0]);
        $this->assertEquals($encodedPassword, $result[1]);
        $this->assertEquals($personId, $result[0]->getId());
    }

    /**
     * @test
     */
    public function usernameIsIncremented()
    {
        $organization = new OrganizationModel;
        $organization->setId('16fd2706-8baf-433b-82eb-8c7fada847da');
        $name = 'Mikey Clarke';
        $abbreviatedName = 'MC';
        $emailAddress = 'mikey@usehipper.com';
        $rawPassword = 'foobar';

        $personId = '20fd0d4f-132f-43af-9280-e4565bf2a44e';
        $encodedPassword = 'encoded-password';
        $urlId = 'abcd1234';
        $sluggifiedName = 'mikey_clarke';
        $username = '@' . $sluggifiedName;
        $likeUsernames = [
            ['username' => '@mikey_clarke'],
            ['username' => '@mikey_clarke1'],
            ['username' => '@mikey_clarke2'],
        ];
        $incrementedUsername = $username . '1';
        $personRow = [
            'id' => $personId,
            'password' => $encodedPassword,
        ];

        $this->createIdGeneratorExpectation($personId);
        $this->createPasswordEncoderExpectation($rawPassword, $encodedPassword);
        $this->createUrlIdGeneratorExpectation($urlId);
        $this->createUrlSlugGeneratorExpectation([$name, '_'], $sluggifiedName);
        $this->createPersonRepositoryExistsWithUsernameExpectation([$username, $organization->getId()], true);
        $this->createPersonRepositoryGetUsernamesLikeExpectation([$username, $organization->getId()], $likeUsernames);
        $this->createPersonInserterExpectation(
            [
                $personId,
                $name,
                $abbreviatedName,
                $emailAddress,
                $encodedPassword,
                $urlId,
                $incrementedUsername,
                $organization->getId(),
                false,
            ],
            $personRow
        );

        $result = $this->personCreator->create($organization, $name, $emailAddress, $rawPassword);
        $this->assertIsArray($result);
        $this->assertInstanceOf(PersonModel::class, $result[0]);
        $this->assertEquals($encodedPassword, $result[1]);
        $this->assertEquals($personId, $result[0]->getId());
    }

    private function createPersonInserterExpectation($args, $result)
    {
        $this->personInserter
            ->shouldReceive('insert')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createPersonRepositoryGetUsernamesLikeExpectation($args, $result)
    {
        $this->personRepository
            ->shouldReceive('getUsernamesLike')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createPersonRepositoryExistsWithUsernameExpectation($args, $result)
    {
        $this->personRepository
            ->shouldReceive('existsWithUsername')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createUrlSlugGeneratorExpectation($args, $result)
    {
        $this->urlSlugGenerator
            ->shouldReceive('generateFromString')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createUrlIdGeneratorExpectation($result)
    {
        $this->urlIdGenerator
            ->shouldReceive('generate')
            ->once()
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
