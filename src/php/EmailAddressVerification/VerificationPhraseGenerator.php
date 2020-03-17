<?php
declare(strict_types=1);

namespace Hipper\EmailAddressVerification;

use GenPhrase\Password as GenPhraseGenerator;

class VerificationPhraseGenerator
{
    public function generate(): string
    {
        $generator = new GenPhraseGenerator;
        $generator->removeWordlist('default');
        $generator->addWordlist('diceware.lst', 'diceware');
        $generator->disableSeparators(true);
        $generator->disableWordModifier(true);
        return $generator->generate(46);
    }
}
