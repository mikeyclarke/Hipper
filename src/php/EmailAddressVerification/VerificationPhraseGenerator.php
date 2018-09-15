<?php
declare(strict_types=1);

namespace hleo\EmailAddressVerification;

use GenPhrase\Password as GenPhraseGenerator;

class VerificationPhraseGenerator
{
    public function generate()
    {
        $generator = new GenPhraseGenerator;
        $generator->disableWordModifier(true);
        return $generator->generate(46);
    }
}
