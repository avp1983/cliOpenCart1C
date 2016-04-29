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
$dataTagsArray = array('Ref', 'IsFolder', 'DeletionMark', 'Parent', 'Description', 'Артикул', 'СтавкаНДС', 'ФайлКартинки');

//$file = fopen("c:\\xampp\\htdocs\\santehych.test\\www\\admin\\controller\\common\\category.out2","w");
//fwrite($file, implode(';',$dataTagsArray)."\n");
class DB {

    private $mysqli;

    private function log($msg) {
        error_log($msg, 3, "c:\\xampp\\htdocs\\santehych.test\\www\\admin\\controller\\common\\error.log");
    }

    public function __construct() {
        $this->mysqli = new mysqli("localhost", "root", "", "opencartdb996");
        if (mysqli_connect_errno()) {
            printf("Не удалось подключиться: %s\n", mysqli_connect_error());
            exit();
        }
        /* изменение набора символов на utf8 */
        if (!$this->mysqli->set_charset("utf8")) {
            printf("Ошибка при загрузке набора символов utf8: %s\n", $this->mysqli->error);
        } else {
            printf("Текущий набор символов: %s\n", $this->mysqli->character_set_name());
        }
    }

    public function strBoolToInt($str) {
        return ($str === 'true') ? 1 : 0;
    }

    public function insertFolder($position) {
        $stmt = $this->mysqli->prepare("INSERT INTO imp_categories (Ref,IsFolder,DeletionMark,Parent,Description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('siiss', $position['Ref'], $this->strBoolToInt($position['IsFolder']), $this->strBoolToInt($position['DeletionMark']), $position['Parent'], $position['Description']);
        if (!$stmt->execute()) {
            $this->log("Errormessage: %s\n", $stmt->error);
            $this->log(print_r($position, true));
        }
        $stmt->close();
    }

    public function insertProduct($position) {
        $stmt = $this->mysqli->prepare("INSERT INTO imp_products (Ref,IsFolder,DeletionMark,Parent,Description,	Articul, NDS, img_ref) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('siisssss', $position['Ref'], $this->strBoolToInt($position['IsFolder']), $this->strBoolToInt($position['DeletionMark']), $position['Parent'], $position['Description'], $position['Артикул'], $position['СтавкаНДС'], $position['ФайлКартинки']
        );
        if (!$stmt->execute()) {
            $this->log("Errormessage: %s\n", $stmt->error);
            $this->log(print_r($position, true));
        }
        $stmt->close();
    }

    public function close() {
        $this->mysqli->close();
    }

}

$db = new DB();



while ($reader->read()) {


    $isStartPosition = isTagStarted($reader, $isStartPosition, FIRST_EL);
    if (!$isStartPosition) {
        if ($position) {
            if (!isset($position['IsFolder'])) error_log('null exepcion: '.print_r($position, true), 3, "c:\\xampp\\htdocs\\santehych.test\\www\\admin\\controller\\common\\error.log");
            if ($position['IsFolder'] === 'true') {
                $db->insertFolder($position);
            }
            if ($position['IsFolder'] === 'false') {
                $db->insertProduct($position);
            }

            $position = array();
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
}
//fclose($file);