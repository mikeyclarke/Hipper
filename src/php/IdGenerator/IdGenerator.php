<?php
declare(strict_types=1);

namespace Hipper\IdGenerator;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\Codec\TimestampFirstCombCodec;

class IdGenerator
{
    // This class generates monotonically increasing UUIDs using the timestamp first strategy so that they’re sorted
    // in the database
    // See https://web.archive.org/web/20200324203214/https://uuid.ramsey.dev/en/latest/customize/timestamp-first-comb-codec.html

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

    public function generate(): string
    {
        return $this->factory->uuid4()->toString();
    }
}
