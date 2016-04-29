<?php

define(FIRST_EL, 'CatalogObject.Номенклатура');

function isStartElement(XMLReader $reader, $el) {
    return ($reader->nodeType == XMLReader::ELEMENT && $reader->name == $el);
}

function isStartOneOfElements(XMLReader $reader, $elements = array()) {
    if ($reader->nodeType == XMLReader::ELEMENT && in_array($reader->name, $elements)) {
        return $reader->name;
    }
    return FALSE;
}

function isEndElement(XMLReader $reader, $el) {
    return ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName == $el);
}

function getNodeName(XMLReader $reader) {
    if ($reader->nodeType == XMLReader::ELEMENT) {
        return $reader->name;
    }
    return '';
}

function getTextFromEl(XMLReader $reader) {
    if ($reader->nodeType == XMLReader::TEXT) {
        return $reader->value;
    }
    return '';
}

function isTagStarted($reader, $isStartPosition, $tagName) {
    if (!$isStartPosition && isStartElement($reader, $tagName))
        $isStartPosition = TRUE;
    if ($isStartPosition && isEndElement($reader, $tagName)) {
        $isStartPosition = FALSE;
    }
    return $isStartPosition;
}

function isOneOfTagsStarted($reader, $isStartDataTag, $tag, $dataTagsArray) {
    if (!$isStartDataTag && isStartOneOfElements($reader, $dataTagsArray)) {
        $isStartDataTag = TRUE;
        $tag = $reader->name;
    }
    if ($isStartDataTag && isEndElement($reader, $tag)) {
        $isStartDataTag = FALSE;
        $tag = '';
    }

    return array($isStartDataTag, $tag);
}

$reader = new XMLReader();
$reader->open('c:\\Users\\lenovo\\Documents\\1c\\export.xml');
$isStartPosition = FALSE;
$isStartDataTag = FALSE;
$tag = '';
$position = array();
$dataTagsArray = array('Ref', 'IsFolder', 'DeletionMark', 'Parent', 'Description');

$file = fopen("c:\\xampp\\htdocs\\santehych.test\\www\\admin\\controller\\common\\category.out2","w");
fwrite($file, implode(';',$dataTagsArray)."\n");

while ($reader->read()) {

    /* if (!$isStartPosition && isStartElement($reader, FIRST_EL))
      $isStartPosition = TRUE;
      if ($isStartPosition && isEndElement($reader, FIRST_EL)) {
      $isStartPosition = FALSE;
      unset($position);
      } */
    $isStartPosition = isTagStarted($reader, $isStartPosition, FIRST_EL);
    if (!$isStartPosition) {
        if (isset($position)){
            if ($position['IsFolder']==='true'){
                $line = '"'.implode('";"',$position).'"';
                fwrite($file, $line."\n");
            }
            unset($position);
        }
        
        
    }
    if ($isStartPosition) {
        list($isStartDataTag, $tag) = isOneOfTagsStarted($reader, $isStartDataTag, $tag, $dataTagsArray);
        if ($isStartDataTag) {
            $text = getTextFromEl($reader);
            if (!empty($text) && !empty($tag)) {
                $position[$tag] = $text;
            }
        }
    }

    /* if ($isStartPosition) {
      $tagBegin = isStartOneOfElements($reader, $dataTagsArray);
      if (!$isStartDataTag && $tagBegin) {
      $isStartDataTag = TRUE;
      $tag = $tagBegin;
      }
      if ($isStartDataTag && isEndElement($reader, $tag)) {
      $isStartDataTag = FALSE;
      $tag = '';
      }

      if ($isStartDataTag) {
      $text = getTextFromEl($reader);
      if (!empty($text) && !empty($tag)) {
      $position[$tag] = $text;
      }
      }
      } */
}
fclose($file);