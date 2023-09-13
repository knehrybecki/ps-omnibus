<?php
include_once(dirname(__FILE__) . '/../../config/config.inc.php');
include_once(dirname(__FILE__) . '/../../init.php');

$now = date('Y-m-d');

$db = Db::getInstance(_PS_USE_SQL_SLAVE_);

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

  $check_combination = "SELECT product_type  FROM " . _DB_PREFIX_ . "product where id_product = $id_product_specific";
  $disp_check = Db::getInstance()->executeS($check_combination);

  $if_combinations = $disp_check[0]['product_type'];
  // echo ("<script>console.log('wewew: " . ($from) . "');</script>");
  if ($from === $formatted_date || $from == '0000-00-00 00:00:00') {


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
        }

      } else {

        $sqlatr_discount = "SELECT `id_product_attribute`,`id_product`  FROM `" . _DB_PREFIX_ . "omnibus_discount_date` WHERE id_product = $id_product_specific  and id_product_attribute is NULL";
        $result_discount_combination = Db::getInstance()->executeS($sqlatr_discount);

        $prices = Product::getPriceStatic($id_product_specific);

        if (empty($result_discount_combination)) {
          $sqlOb_discount = "INSERT INTO `" . _DB_PREFIX_ . "omnibus_discount_date` (`id_product`,`DateF`,`discount_price`) VALUES ($id_product_specific,'$now','$prices')";
          Db::getInstance()->execute($sqlOb_discount);
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

    }

  }
}

$createColumn = "ALTER TABLE `" . _DB_PREFIX_ . "omnibus` ADD COLUMN IF NOT EXISTS `$now` DECIMAL(20,6) NOT NULL DEFAULT '0.000000' AFTER `id_product`";
if (!$addColumn = Db::getInstance()->execute($createColumn)) {
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


  $update_when_combination = "UPDATE `" . _DB_PREFIX_ . "omnibus` SET `$now`='$price' WHERE id_product = '$item[id_product]'  and id_product_attribute = '$item[id_product_attribute]'  ";
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

?>