<?php

class ModelCatalogImport extends Model {

    public function load() {
        $this->load->model('catalog/category');
    }

    private function getParentCatId($parent) {
        $query = $this->db->query("SELECT category_id FROM imp_categories WHERE Ref = '" . $parent . "' ");
        return $query->row['category_id'];
    }

    private function updateCatId($ref, $categoryId) {
        $query = $this->db->query("UPDATE imp_categories SET category_id='".(int)$categoryId."'  WHERE Ref = '" . $ref . "' ");
        //return $query->row['category_id'];
    }

    private function addCategory($result) {
        //$parent_id = ($parentid === '00000000-0000-0000-0000-000000000000') ? 0 : $this->categoriesCacheArray[$level - 1][$categoriesArray[$level - 1]];
        if ($result['Parent'] === '00000000-0000-0000-0000-000000000000') {
            $parent_id = 0;
        } else {
            $parent_id = $this->getParentCatId($result['Parent']);
        }

        $post = $this->makeCategoryPost(array(
            'name' => $result['Description'],
            'parent_id' => $parent_id,
            'keyword' => $this->transliterate($result['Description']),
            'top' => ($parent_id == 0) ? 1 : 0
        ));
        $categoryId = $this->model_catalog_category->addCategory($post);
        $this->updateCatId($result['Ref'], $categoryId);
    }

    public function createCategoriesRecursive($parentid) {
        // Get the nodes new parents
        $query = $this->db->query("SELECT * FROM imp_categories WHERE Parent = '" . $parentid . "' ");

        foreach ($query->rows as $result) {

            $this->addCategory($result);
            $this->createCategoriesRecursive($result['Ref']);
        }

  
    }

    public function makeCategoryPost($params) {
        return array(
            'category_description' =>
            array(
                2 =>
                array(
                    'name' => $params['name'],
                    'description' => '',
                    'meta_title' => $params['name'],
                    'meta_description' => '',
                    'meta_keyword' => ''
                ),
                1 =>
                array(
                    'name' => $params['name'],
                    'description' => '',
                    'meta_title' => $params['name'],
                    'meta_description' => '',
                    'meta_keyword' => '',
                )
            ),
            'path' => '',
            'parent_id' => $params['parent_id'],
            'top' => $params['top'], // Для parent_id=0 Показывать в главном меню
            'filter' => '',
            'category_store' =>
            array(
                0 => '0'
            ),
            'keyword' => $params['keyword'],
            'image' => '',
            'column' => '1',
            'sort_order' => '0',
            'status' => '1',
            'category_layout' => array(0 => '')
        );
    }

    public function makeProductPost($params) {
        return
                array(
                    'product_description' =>
                    array(
                        2 =>
                        array(
                            'name' => $params['name'],
                            'description' => $params['description'],
                            'meta_title' => $params['name'],
                            'meta_description' => $params['name'],
                            'meta_keyword' => $params['name'],
                            'tag' => ''
                        ),
                        1 =>
                        array(
                            'name' => $params['name'],
                            'description' => $params['description'],
                            'meta_title' => $params['name'],
                            'meta_description' => $params['name'],
                            'meta_keyword' => $params['name'],
                            'tag' => ''
                        )
                    ),
                    'image' => $params['img'],
                    'model' => $params['name'],
                    'sku' => $params['sku'],
                    'upc' => '',
                    'ean' => '',
                    'jan' => '',
                    'isbn' => '',
                    'mpn' => '',
                    'location' => '',
                    'price' => $params['price'],
                    'tax_class_id' => '0',
                    'quantity' => '1',
                    'minimum' => '1',
                    'subtract' => '1',
                    'stock_status_id' => '6',
                    'shipping' => '1',
                    'keyword' => $params['keyword'],
                    'date_available' => '2016-04-27',
                    'length' => '',
                    'width' => '',
                    'height' => '',
                    'length_class_id' => '1',
                    'weight' => '',
                    'weight_class_id' => '1',
                    'status' => '1',
                    'sort_order' => '1',
                    'manufacturer' => '',
                    'manufacturer_id' => '0',
                    'category' => '',
                    'product_category' =>
                    array(
                        0 => $params['product_category_id'],
                    ),
                    'filter' => '',
                    'product_store' =>
                    array(
                        0 => '0',
                    ),
                    'download' => '',
                    'related' => '',
                    'option' => '',
                    'points' => '',
                    'product_reward' =>
                    array(
                        1 =>
                        array(
                            'points' => ''
                        )
                    ),
                    'product_layout' =>
                    array(
                        0 => ''
                    )
        );
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

}
