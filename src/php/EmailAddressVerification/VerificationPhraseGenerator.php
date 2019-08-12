<?php
declare(strict_types=1);

namespace Hipper\EmailAddressVerification;

use GenPhrase\Password as GenPhraseGenerator;

class VerificationPhraseGenerator
{
    public function generate(): string
    {
        $generator = new GenPhraseGenerator;
        $generator->disableWordModifier(true);
        return $generator->generate(46);
    }
}
