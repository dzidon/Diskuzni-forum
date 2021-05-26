<?php

namespace JBBCode\validators;

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'InputValidator.php';

/**
 * InputValidator pro fonty
 *
 * @author David Zidon
 * @since May 2021
 */
class FontValidator implements \JBBCode\InputValidator
{
    /**
     * Vrati true, jestli je $input validni font.
     *
     * @param $input string na validaci
     */
    public function validate($input)
    {
        $validFonts = array('Arial', 'Georgia', 'Impact', 'Sans-serif', 'Serif', 'Verdana');
        $valid = in_array($input, $validFonts);
        return !!$valid;
    }
}