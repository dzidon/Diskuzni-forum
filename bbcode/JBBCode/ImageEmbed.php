<?php

class ImageEmbed extends JBBCode\CodeDefinition {

    public function __construct()
    {
        parent::__construct();
        $this->parseContent = false;
        $this->useOption = true;
        $this->setTagName('img');
    }

    public function asHtml(JBBCode\ElementNode $el)
    {
        $elementAttribute = $el->getAttribute();
        $sizesOk = false;

        if (isset ($elementAttribute)) {
            $elementAttributeValid = preg_match('/^(\d+)x(\d+)$/', $elementAttribute['img'], $sizes);
            if($elementAttributeValid) {
                if($sizes[1] > 0 && $sizes[2] > 0) {
                    $sizesOk = true;
                }
            }
        }

        $content = "";
        foreach($el->getChildren() as $child)
            $content .= $child->getAsBBCode();

        $imgUrlValid = (bool) filter_var($content, FILTER_VALIDATE_URL);
        if($imgUrlValid && $sizesOk) {
            return '<img src="'.htmlspecialchars($content).'" alt="Uživatelem vložený obrázek" width="'.htmlspecialchars($sizes[1]).'" height="'.htmlspecialchars($sizes[2]).'">';
        }
        else {
            return $el->getAsBBCode();
        }
    }
}