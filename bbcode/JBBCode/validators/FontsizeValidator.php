<?php

namespace JBBCode\validators;

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'InputValidator.php';

/**
 * InputValidator pro velikost fontu
 *
 * @author David Zidon
 * @since May 2021
 */
class FontsizeValidator implements \JBBCode\InputValidator
{
    /**
     * Vrati true, jestli je $input validni velikost fontu.
     *
     * @param $input string na validaci
     */
    public function validate($input)
    {
        $valid = is_numeric($input) && ($input >= 1 && $input <= 7);
        return !!$valid;
    }
}