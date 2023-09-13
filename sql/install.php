<?php

$sql = array();

$sql[] = ' CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'omnibus` (
	`id_product_attribute` int(10) unsigned NULL,
	`id_product` int(10) unsigned NOT NULL
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';

$sql[] = ' CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'omnibus_discount_date` (`id_product_attribute` INT NULL  DEFAULT NULL, `id_product` INT NOT NULL , `DateF` DATE NULL DEFAULT NULL , `discount_price` DECIMAL(20,6) NULL DEFAULT 0.000000 ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';




foreach ($sql as $query) {
	if (Db::getInstance()->execute($query) == false) {
		return false;
	}
}