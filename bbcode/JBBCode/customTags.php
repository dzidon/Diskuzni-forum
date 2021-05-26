<?php

require_once 'validators/EmailValidator.php';
require_once 'validators/FontsizeValidator.php';
require_once 'validators/FontValidator.php';

//preskrtnuti
$builder = new JBBCode\CodeDefinitionBuilder('s', '<s>{param}</s>');
$parser->addCodeDefinition($builder->build());

//dolni index
$builder = new JBBCode\CodeDefinitionBuilder('sub', '<sub>{param}</sub>');
$parser->addCodeDefinition($builder->build());

//horni index
$builder = new JBBCode\CodeDefinitionBuilder('sup', '<sup>{param}</sup>');
$parser->addCodeDefinition($builder->build());

//vycentrovani
$builder = new JBBCode\CodeDefinitionBuilder('center', '<div align="center">{param}</div>');
$parser->addCodeDefinition($builder->build());

//align left
$builder = new JBBCode\CodeDefinitionBuilder('left', '<div align="left">{param}</div>');
$parser->addCodeDefinition($builder->build());

//align right
$builder = new JBBCode\CodeDefinitionBuilder('right', '<div align="right">{param}</div>');
$parser->addCodeDefinition($builder->build());

//zarovnat do bloku
$builder = new JBBCode\CodeDefinitionBuilder('justify', '<div class="bbcode-justify">{param}</div>');
$parser->addCodeDefinition($builder->build());

//pismo
$builder = new JBBCode\CodeDefinitionBuilder('font', '<span style="font-family: {option};">{param}</span>');
$builder->setUseOption(true)->setOptionValidator(new \JBBCode\validators\FontValidator());
$parser->addCodeDefinition($builder->build());

//velikost pisma
$builder = new JBBCode\CodeDefinitionBuilder('size', '<span style="font-size: {option}ex;">{param}</span>');
$builder->setUseOption(true)->setOptionValidator(new \JBBCode\validators\FontsizeValidator());
$parser->addCodeDefinition($builder->build());

//kod
$builder = new JBBCode\CodeDefinitionBuilder('code', '<div class="bbcode-code">{param}</div>');
$builder->setParseContent(false);
$parser->addCodeDefinition($builder->build());

//citace
$builder = new JBBCode\CodeDefinitionBuilder('quote', '<div class="bbcode-quote">{param}</div>');
$parser->addCodeDefinition($builder->build());

//email
$builder = new JBBCode\CodeDefinitionBuilder('email', '<a href="mailto:{option}" class="text-link">{param}</a>');
$builder->setUseOption(true)->setOptionValidator(new \JBBCode\validators\EmailValidator());
$parser->addCodeDefinition($builder->build());

//youtube
$builder = new JBBCode\CodeDefinitionBuilder('youtube', "<div class=\"video-container\"><div class=\"video-wrap\"><iframe class=\"video\" width=\"640\" height=\"390\" src=\"http://www.youtube.com/embed/{param}\" frameborder=\"0\" allowfullscreen></iframe></div></div>");
$parser->addCodeDefinition($builder->build());