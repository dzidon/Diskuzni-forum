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
            //return '<div style="background-color: yellow; max-width: '.htmlspecialchars($sizes[1]).'px; max-height: '.htmlspecialchars($sizes[2]).'px;"><img class="bbcode-img2" src="'.htmlspecialchars($content).'" alt="Uživatelem vložený obrázek"></div>';
            if($sizes[1] === $sizes[2]) { //zachovat aspect ratio
                return '<div style="max-width: '.htmlspecialchars($sizes[1]).'px;"><img style="object-fit: cover; width: 100%;" src="'.htmlspecialchars($content).'" /></div>';
            }
            else { //nezachovat aspect ratio
                return '<div style="background-color: lightgray; max-width: '.htmlspecialchars($sizes[1]).'px; height: '.htmlspecialchars($sizes[2]).'px; background-size: 100% 100%; background-repeat: no-repeat; background-position: center; background-image: url('.htmlspecialchars($content).');"></div>';
            }
        }
        else {
            return $el->getAsBBCode();
        }
    }
}