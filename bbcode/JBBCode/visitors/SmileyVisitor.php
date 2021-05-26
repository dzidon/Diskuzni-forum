<?php

namespace JBBCode\visitors;

/**
 * This visitor is an example of how to implement smiley parsing on the JBBCode
 * parse graph. It converts :) into image tags pointing to /smiley.png.
 *
 * @author jbowens
 * @since April 2013
 */
class SmileyVisitor implements \JBBCode\NodeVisitor
{

    function visitDocumentElement(\JBBCode\DocumentElement $documentElement)
    {
        foreach($documentElement->getChildren() as $child) {
            $child->accept($this);
        }
    }

    function visitTextNode(\JBBCode\TextNode $textNode)
    {
        $textNode->setValue(str_replace(':)', 
                                        '<img src="bbcode/emoticons/smile.png" alt=":)" />',
                                        $textNode->getValue()));

        $textNode->setValue(str_replace(':angel:',
            '<img src="bbcode/emoticons/angel.png" alt=":angel:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':angry:',
            '<img src="bbcode/emoticons/angry.png" alt=":angry:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace('8-)',
            '<img src="bbcode/emoticons/cool.png" alt="8-)" />',
            $textNode->getValue()));

        $emote = ":'(";
        $textNode->setValue(str_replace($emote,
            '<img src="bbcode/emoticons/cwy.png" alt="'.$emote.'" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':ermm:',
            '<img src="bbcode/emoticons/ermm.png" alt=":ermm:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':D',
            '<img src="bbcode/emoticons/grin.png" alt=":D" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace('&lt;3',
            '<img src="bbcode/emoticons/heart.png" alt="<3" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':(',
            '<img src="bbcode/emoticons/sad.png" alt=":(" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':O',
            '<img src="bbcode/emoticons/shocked.png" alt=":O" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':P',
            '<img src="bbcode/emoticons/tongue.png" alt=":P" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(';)',
            '<img src="bbcode/emoticons/wink.png" alt=";)" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':alien:',
            '<img src="bbcode/emoticons/alien.png" alt=":alien:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':blink:',
            '<img src="bbcode/emoticons/blink.png" alt=":blink:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':blush:',
            '<img src="bbcode/emoticons/blush.png" alt=":blush:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':cheerful:',
            '<img src="bbcode/emoticons/cheerful.png" alt=":cheerful:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':devil:',
            '<img src="bbcode/emoticons/devil.png" alt=":devil:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':dizzy:',
            '<img src="bbcode/emoticons/dizzy.png" alt=":dizzy:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':getlost:',
            '<img src="bbcode/emoticons/getlost.png" alt=":getlost:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':happy:',
            '<img src="bbcode/emoticons/happy.png" alt=":happy:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':kissing:',
            '<img src="bbcode/emoticons/kissing.png" alt=":kissing:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':ninja:',
            '<img src="bbcode/emoticons/ninja.png" alt=":ninja:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':pinch:',
            '<img src="bbcode/emoticons/pinch.png" alt=":pinch:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':pouty:',
            '<img src="bbcode/emoticons/pouty.png" alt=":pouty:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':sick:',
            '<img src="bbcode/emoticons/sick.png" alt=":sick:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':sideways:',
            '<img src="bbcode/emoticons/sideways.png" alt=":sideways:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':silly:',
            '<img src="bbcode/emoticons/silly.png" alt=":silly:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':sleeping:',
            '<img src="bbcode/emoticons/sleeping.png" alt=":sleeping:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':unsure:',
            '<img src="bbcode/emoticons/unsure.png" alt=":unsure:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':woot:',
            '<img src="bbcode/emoticons/w00t.png" alt=":woot:" />',
            $textNode->getValue()));

        $textNode->setValue(str_replace(':wassat:',
            '<img src="bbcode/emoticons/wassat.png" alt=":wassat:" />',
            $textNode->getValue()));
    }

    function visitElementNode(\JBBCode\ElementNode $elementNode)
    {
        /* We only want to visit text nodes within elements if the element's
         * code definition allows for its content to be parsed.
         */
        if ($elementNode->getCodeDefinition()->parseContent()) {
            foreach ($elementNode->getChildren() as $child) {
                $child->accept($this);
            }
        }
    }

}
