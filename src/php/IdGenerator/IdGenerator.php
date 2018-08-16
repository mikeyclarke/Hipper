<?php
namespace hleo\IdGenerator;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\Codec\TimestampFirstCombCodec;

class IdGenerator
{
    private $factory;

    public function __construct()
    {
        $this->factory = new UuidFactory;

        $generator = new CombGenerator(
            $this->factory->getRandomGenerator(),
            $this->factory->getNumberConverter()
        );
        $codec = new TimestampFirstCombCodec($this->factory->getUuidBuilder());

        $this->factory->setRandomGenerator($generator);
        $this->factory->setCodec($codec);
    }

    public function generate()
    {
        return $this->factory->uuid4()->toString();
    }
}
