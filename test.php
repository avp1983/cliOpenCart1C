<?php

function getFisrt($line){
			$lineArray = explode(';', $line);
            $categories = str_replace('"', "", $lineArray[0]);
           // $categories=iconv("UTF-8","UTF-8//IGNORE", $categories);
			$categories = rtrim($categories, '/');
			$categoriesArray = explode('/', $categories);
			return iconv("UTF-8","UTF-8//IGNORE", $categoriesArray[0]);
}	

function strToHex($string){
    $hex = '';
    for ($i=0; $i<strlen($string); $i++){
        $ord = ord($string[$i]);
        $hexCode = dechex($ord);
        $hex .= substr('0'.$hexCode, -2);
    }
    return strToUpper($hex);
}

function loadFromFile() {
        $handle = fopen("c:\\xampp\\htdocs\\santehych.test\\www\\download\\export.test", "r");
        //$this->init();
        if ($handle) {
            $line1 = fgets($handle);
			$line2 = fgets($handle);
			$first = getFisrt($line1);
			$second = getFisrt($line2);
			//echo 'first=',$first,';second=',$second,';<br />';
			//echo 'f=',ord($first[0]),';s=',ord($second[0]),'<br />';
            
			var_dump($first==$second);
			assert($first==$second); 
            echo 'f=',strToHex($first),'<br/>s=',strToHex($second);
				
				
			

            fclose($handle);
        } else {
            die('fail');
        }
    }
	
	
	loadFromFile();