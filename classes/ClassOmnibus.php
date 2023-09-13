<?php

if (!defined('_PS_VERSION_')) {
    exit;
}
require_once(_PS_CLASS_DIR_ . '/Product.php');

class ClassOmnibus extends ObjectModel
{
    public $id_shop;

    public static $definition = array(
        'table' => 'omnibus',
        'primary' => 'id',
        'multilang' => true,
        'fields' => array(),
    );

    public function addDateWhenAddedDiscount()
    {
        $now = date('Y-m-d');

        $timestamp = strtotime($now);
        $date = new DateTime();
        $date->setTimestamp($timestamp);
        $formatted_date = $date->format('Y-m-d H:i:s');

        $sql_specific_price = "SELECT id_product,id_product_attribute,`from`  FROM " . _DB_PREFIX_ . "specific_price";
        $result_specific = Db::getInstance()->executeS($sql_specific_price);

        foreach ($result_specific as $specific_price) {
            $id_product_specific = $specific_price['id_product'];
            $id_product_attribute_specific = $specific_price['id_product_attribute'];
            $from = $specific_price['from'];
            // echo ("<script>console.log('wewew: " . ($from) . "');</script>");
            $check_combination = "SELECT product_type  FROM " . _DB_PREFIX_ . "product where id_product = $id_product_specific";
            $disp_check = Db::getInstance()->executeS($check_combination);

            $if_combinations = $disp_check[0]['product_type'];

            if ($from == $formatted_date || $from == '0000-00-00 00:00:00') {
                if ($id_product_attribute_specific == 0) {
                    $sql_attr = "SELECT `id_product`,`id_product_attribute` FROM " . _DB_PREFIX_ . "product_attribute where id_product=$specific_price[id_product] ";
                    $result_attr = Db::getInstance()->executeS($sql_attr);

                    if ($if_combinations === 'combinations') {
                        foreach ($result_attr as $item) {
                            $id_product = $item['id_product'];
                            $id_attribute = $item['id_product_attribute'];
                            $product = new Product($id_product, false);

                            $price = $product->getPrice(true, $id_attribute);


                            $sqlatr_discount = "SELECT `id_product_attribute`,`id_product`  FROM `" . _DB_PREFIX_ . "omnibus_discount_date` WHERE id_product = $id_product and id_product_attribute =$id_attribute";
                            $result_discount_combination = Db::getInstance()->executeS($sqlatr_discount);

                            if (empty($result_discount_combination)) {
                                $sqlOb_discount = "INSERT INTO `" . _DB_PREFIX_ . "omnibus_discount_date` (`id_product_attribute`,`id_product`,`DateF`,`discount_price`) VALUES ('$id_attribute','$id_product','$now','$price')";
                                Db::getInstance()->execute($sqlOb_discount);
                            }

                            if ($result_discount_combination && $from !== '0000-00-00 00:00:00') {
                                $sqlOb_discount_up = "UPDATE `" . _DB_PREFIX_ . "omnibus_discount_date`
                                SET `DateF` = '$from', `discount_price` = '$price'
                                WHERE `id_product_attribute` = '$id_attribute' AND `id_product` = '$id_product'";
                                Db::getInstance()->execute($sqlOb_discount_up);
                            }

                        }
                    } else {

                        $sqlatr_discount = "SELECT `id_product_attribute`,`id_product`  FROM `" . _DB_PREFIX_ . "omnibus_discount_date` WHERE id_product = $id_product_specific  and id_product_attribute is NULL";
                        $result_discount_combination = Db::getInstance()->executeS($sqlatr_discount);

                        $prices = Product::getPriceStatic($id_product_specific);

                        if (empty($result_discount_combination)) {
                            $sqlOb_discount = "INSERT INTO `ps_omnibus_discount_date` (`id_product`,`DateF`,`discount_price`) VALUES ($id_product_specific,'$now','$prices')";
                            Db::getInstance()->execute($sqlOb_discount);
                        }

                        if ($result_discount_combination && $from !== '0000-00-00 00:00:00') {
                            $sqlOb_discount_up = "UPDATE `" . _DB_PREFIX_ . "omnibus_discount_date`
                            SET `DateF` = '$from', `discount_price` = '$prices'
                            WHERE  `id_product` = '$id_product_specific'";
                            Db::getInstance()->execute($sqlOb_discount_up);
                        }
                    }
                } else {
                    $product = new Product($id_product_specific, false);

                    $price = $product->getPrice(true, $id_product_attribute_specific);

                    $sqlatr_discount = "SELECT `id_product_attribute`,`id_product`  FROM `" . _DB_PREFIX_ . "omnibus_discount_date` WHERE id_product = $id_product_specific and id_product_attribute = $id_product_attribute_specific";
                    $result_discount_combination = Db::getInstance()->executeS($sqlatr_discount);

                    if (empty($result_discount_combination)) {
                        $sqlOb_discount = "INSERT INTO `" . _DB_PREFIX_ . "omnibus_discount_date` (`id_product_attribute`,`id_product`,`DateF`,`discount_price`) VALUES ('$id_product_attribute_specific','$id_product_specific','$now','$price')";
                        Db::getInstance()->execute($sqlOb_discount);
                    }
                    if ($result_discount_combination && $from !== '0000-00-00 00:00:00') {
                        $sqlOb_discount_up = "UPDATE `" . _DB_PREFIX_ . "omnibus_discount_date`
                        SET `DateF` = '$from', `discount_price` = '$price'
                        WHERE `id_product_attribute` = '$id_product_attribute_specific' AND `id_product` = '$id_product_specific'";
                        Db::getInstance()->execute($sqlOb_discount_up);
                    }
                }
            }
        }
    }
    public function CheckAddProduct()
    {
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $sql = 'SELECT `id_product`,`price` FROM ' . _DB_PREFIX_ . 'product';

        $sqlAtrr = 'SELECT `id_product_attribute`, `id_product` FROM ' . _DB_PREFIX_ . 'product_attribute';

        if (!$sqlAtrrResult = $db->executeS($sqlAtrr)) {
            return $sqlAtrrResult;
        }

        foreach ($sqlAtrrResult as $object2) {
            $sqlatr = "SELECT `id_product_attribute`,`id_product`  FROM `ps_omnibus` WHERE id_product_attribute = '$object2[id_product_attribute]' AND id_product = '$object2[id_product]'  ";

            $result = Db::getInstance()->executeS($sqlatr);

            if (empty($result)) {
                $sqlOb = "INSERT INTO `" . _DB_PREFIX_ . "omnibus` (`id_product_attribute`,`id_product`) VALUES ($object2[id_product_attribute],'$object2[id_product]')";
                Db::getInstance()->execute($sqlOb);
            }
        }

        if (!$result = $db->executeS($sql)) {
            return $result;
        }

        foreach ($result as $object) {
            $sqlp = "SELECT `id_product`,`id_product_attribute` FROM `" . _DB_PREFIX_ . "omnibus` WHERE  id_product = '$object[id_product]' and id_product_attribute IS NULL";

            $result = Db::getInstance()->executeS($sqlp);

            if (empty($result)) {
                $sqlOb = "INSERT INTO `" . _DB_PREFIX_ . "omnibus` (`id_product`) VALUES ('$object[id_product]')";

                Db::getInstance()->execute($sqlOb);
            }
        }
    }
    public function createTablewhenStandardPRoduct()
    {
        $now = date('Y-m-d');

        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);

        $createColumn = "ALTER TABLE `" . _DB_PREFIX_ . "omnibus` ADD COLUMN IF NOT EXISTS `$now` DECIMAL(20,6) NOT NULL DEFAULT '0.000000' AFTER `id_product`";
        if (!$addColumn = $db->execute($createColumn)) {
            return $addColumn;
        }

        $sql = 'SELECT `id_product` FROM ' . _DB_PREFIX_ . 'product';

        $sql_attr = 'SELECT `id_product`,`id_product_attribute` FROM ' . _DB_PREFIX_ . 'product_attribute';

        if (!$result_attr = $db->executeS($sql_attr)) {
            return $result_attr;
        }

        foreach ($result_attr as $item) {
            $id_product = $item['id_product'];
            $id_attribute = $item['id_product_attribute'];
            $product = new Product($id_product, false);

            $price = $product->getPrice(true, $id_attribute);


            $update_when_combination = "UPDATE " . _DB_PREFIX_ . "omnibus SET `$now`='$price' WHERE id_product = '$item[id_product]'  and id_product_attribute = '$item[id_product_attribute]'  ";
            $db->execute($update_when_combination);
        }

        if (!$result = $db->executeS($sql)) {
            return $result;
        }

        foreach ($result as $object) {
            $prices = Product::getPriceStatic($object['id_product']);

            $update_not_combination = "UPDATE `" . _DB_PREFIX_ . "omnibus` SET `$now`='$prices' WHERE id_product = '$object[id_product]'  and id_product_attribute is null ";
            $db->execute($update_not_combination);
        }
    }

    public function deleteOldDataBase()
    {
        $checkCount = "SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'ps_omnibus'";
        $result = Db::getInstance()->executeS($checkCount);

        if ($result[0]['COUNT(*)'] >= 90) {
            $checkName = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='ps_omnibus' AND ORDINAL_POSITION=90";
            $getLastColumn = Db::getInstance()->executeS($checkName);

            $column = $getLastColumn[0]['COLUMN_NAME'];

            $delColumn = "ALTER TABLE ps_omnibus DROP `$column`";
            Db::getInstance()->execute($delColumn);
        }
    }
    public function deleteCombination()
    {
        $sqlatr2 = "SELECT id_product_attribute,id_product FROm " . _DB_PREFIX_ . "omnibus
        WHERE (id_product_attribute,id_product) NOT IN (SELECT id_product_attribute,id_product FROM ps_product_attribute) AND id_product_attribute is not null ";
        if (!$result2 = Db::getInstance()->executeS($sqlatr2)) {
            return $result2;
        }

        $idAtr = $result2[0]['id_product_attribute'];
        $idProd = $result2[0]['id_product'];


        $deleted = "DELETE FROM `ps_omnibus` WHERE id_product_attribute = $idAtr AND id_product = $idProd";
        Db::getInstance()->execute($deleted);
    }
    public function deletedPRoduct()
    {
        $product = "SELECT id_product,id_product_attribute FROm " . _DB_PREFIX_ . "omnibus WHERE id_product NOT IN (SELECT id_product FROM ps_product) and id_product_attribute is NULL ";
        if (!$product_del = Db::getInstance()->executeS($product)) {
            return $product_del;
        }

        $deltedProduct = $product_del[0]['id_product'];

        $deleted = "DELETE FROM `ps_omnibus` WHERE id_product = $deltedProduct";
        Db::getInstance()->execute($deleted);
    }
    public function deletedFromDiscountDB()
    {
        $productDiscount = "SELECT id_product FROm " . _DB_PREFIX_ . "omnibus_discount_date WHERE id_product NOT IN (SELECT id_product FROM ps_specific_price)";

        if (!$delete_from_specific = Db::getInstance()->executeS($productDiscount)) {
            return $delete_from_specific;
        }
        $deltedProduct = $delete_from_specific[0]['id_product'];

        if (!$deltedProduct) {
            return;
        }

        $deletedFromDiscount = "DELETE FROM " . _DB_PREFIX_ . "omnibus_discount_date WHERE id_product = $deltedProduct";

        Db::getInstance()->execute($deletedFromDiscount);
    }
}