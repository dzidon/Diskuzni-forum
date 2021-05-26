<?php

namespace JBBCode\validators;

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'InputValidator.php';

/**
 * InputValidator pro e-maily
 *
 * @author David Zidon
 * @since May 2021
 */
class EmailValidator implements \JBBCode\InputValidator
{
    /**
     * Vrati true, jestli je $input validni email.
     *
     * @param $input string na validaci
     */
    public function validate($input)
    {
        $valid = filter_var($input, FILTER_VALIDATE_EMAIL);
        return !!$valid;
    }
}