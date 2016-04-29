<?php

class ControllerCommonImport extends Controller {

    public function index() {
        die('Импорт из 1С');
    }

    public function init() {

        $this->load->model('catalog/import');
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
    }

    private function addCategory($params) {
        $post = $this->model_catalog_import->makeCategoryPost($params);
        return $this->model_catalog_category->addCategory($post);
    }

    private function addProduct($params) {
        $post = $this->model_catalog_import->makeProductPost($params);
        $this->model_catalog_product->addProduct($post);
    }

    public function loadFromFile() {
        $handle = fopen("c:\\xampp\\htdocs\\santehych.test\\www\\download\\export.test", "r");
        $this->init();
        if ($handle) {
            while (($line = fgets($handle)) !== false) {

                $lineArray = explode(';', $line);
                $categories = str_replace('"', "", $lineArray[0]);
                $categories = iconv("UTF-8", "UTF-8//IGNORE", $categories);

                $name = str_replace('"', "", $lineArray[1]);
                $categoryId = $this->createCategories($categories);
                $this->addProduct(array(
                    'name' => $name,
                    'description' => $name,
                    'img' => '',
                    'sku' => '',
                    'keyword' => $this->transliterate($name),
                    'product_category_id' => $categoryId,
                    'price' => 100
                ));
            }

            fclose($handle);
        } else {
            die('fail');
        }
    }

    private $categoriesCacheArray = array();

    /*
     * $this->categoriesCacheArray = array(
     *                                  [level]=>array('category_name'=>category_id,...)
     *                                  ....
     *                              )
     */

    private function checkCacheExist($level, $category) {
        if (isset($this->categoriesCacheArray[$level][$category])) {
            return $this->categoriesCacheArray[$level][$category];
        }
        return FALSE;
    }

    private function createCategory($level, $category, $categoriesArray) {
        $parent_id = ($level === 0) ? 0 : $this->categoriesCacheArray[$level - 1][$categoriesArray[$level - 1]];
        $categoryId = $this->addCategory(array(
            'name' => $category,
            'parent_id' => $parent_id,
            'keyword' => $category,
            'top' => 1
        ));
        $this->categoriesCacheArray[$level][$category] = $categoryId;
        return $categoryId;
    }

    private function createCategories($categories) {
        $categories = rtrim($categories, '/');
        $categoriesArray = explode('/', $categories);
        foreach ($categoriesArray as $level => $category) {
            if (!$this->checkCacheExist($level, $category)) {
                //echo $category,":",$level,";"; 
                $this->createCategory($level, $category, $categoriesArray);
            }
        }
        return $this->categoriesCacheArray[$level][$category];
    }

    private function transliterate($string) {
        $replace = array(
            "'" => "",
            "`" => "",
            "а" => "a", "А" => "a",
            "б" => "b", "Б" => "b",
            "в" => "v", "В" => "v",
            "г" => "g", "Г" => "g",
            "д" => "d", "Д" => "d",
            "е" => "e", "Е" => "e",
            "ж" => "zh", "Ж" => "zh",
            "з" => "z", "З" => "z",
            "и" => "i", "И" => "i",
            "й" => "y", "Й" => "y",
            "к" => "k", "К" => "k",
            "л" => "l", "Л" => "l",
            "м" => "m", "М" => "m",
            "н" => "n", "Н" => "n",
            "о" => "o", "О" => "o",
            "п" => "p", "П" => "p",
            "р" => "r", "Р" => "r",
            "с" => "s", "С" => "s",
            "т" => "t", "Т" => "t",
            "у" => "u", "У" => "u",
            "ф" => "f", "Ф" => "f",
            "х" => "h", "Х" => "h",
            "ц" => "c", "Ц" => "c",
            "ч" => "ch", "Ч" => "ch",
            "ш" => "sh", "Ш" => "sh",
            "щ" => "sch", "Щ" => "sch",
            "ъ" => "", "Ъ" => "",
            "ы" => "y", "Ы" => "y",
            "ь" => "", "Ь" => "",
            "э" => "e", "Э" => "e",
            "ю" => "yu", "Ю" => "yu",
            "я" => "ya", "Я" => "ya",
            "і" => "i", "І" => "i",
            "ї" => "yi", "Ї" => "yi",
            "є" => "e", "Є" => "e"
        );
        return $str = iconv("UTF-8", "UTF-8//IGNORE", strtr($string, $replace));
    }

    public function cliImportCategories() {
        echo "cliImportSQL\n";
        $this->load->model('catalog/import');
        $this->model_catalog_import->load();
        $this->model_catalog_import->createCategoriesRecursive('00000000-0000-0000-0000-000000000000');
    }

    public function cliImportProducts() {
        echo "cliImportProducts\n";
        $this->load->model('catalog/import');
        $this->model_catalog_import->importProducts();
    }

}
