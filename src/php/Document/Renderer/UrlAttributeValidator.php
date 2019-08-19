<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

class UrlAttributeValidator
{
    /**
    * RegEx adapted from Symfony
    * https://github.com/symfony/symfony/blob/4.4/src/Symfony/Component/Validator/Constraints/UrlValidator.php
    * https://github.com/symfony/symfony/blob/4.4/src/Symfony/Component/Validator/Constraints/EmailValidator.php
    *
    * @copyright Copyright (c) 2004-2019 Fabien Potencier
    * @license   https://github.com/symfony/symfony/blob/4.4/LICENSE
    */
    const URL_REGEX = '~^
        (
            (
                (http|https)://										        # protocol
                ([\pL\pN\pS\.-])+(\.?([\pL\pN]|xn\-\-[\pL\pN-]+)+\.?)		# a domain name
            )                                                               # protocol + domain name
            |
            (
                mailto:                                                     # mailto
                [a-zA-Z0-9.!#$%&\'*+\\/=?^_`{|}\~-]+@                       # an email prefix
                ([\pL\pN\pS\.-])+(\.?([\pL\pN]|xn\-\-[\pL\pN-]+)+\.?)		# a domain name
            )                                                               # mailto + email prefix + domain name
        )?
		(?:/ (?:[\pL\pN\-._\~!$&\'()*+,;=:@]|%%[0-9A-Fa-f]{2})* )*		    # a path
		(?:\? (?:[\pL\pN\-._\~!$&\'\[\]()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?   # a query (optional)
		(?:\# (?:[\pL\pN\-._\~!$&\'()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )?	    # a fragment (optional)
    $~ixu';

    public function isValid(string $value): bool
    {
        return (bool) preg_match(self::URL_REGEX, $value);
    }
}
