<?php

namespace JBBCode\validators;

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'InputValidator.php';

/**
 * InputValidator pro youtube kod
 *
 * @author David Zidon
 * @since May 2021
 */
class YoutubeValidator implements \JBBCode\InputValidator
{
    /**
     * Vrati true, jestli je $input validni kod youtube videa
     *
     * @param $input string na validaci
     */
    public function validate($input)
    {
        return (bool) preg_match('/^[a-zA-Z0-9_-]+$/', $input);
    }
}