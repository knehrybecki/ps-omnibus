<?php

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS`' . _DB_PREFIX_ . 'omnibus`';
$sql[] = 'DROP TABLE IF EXISTS`' . _DB_PREFIX_ . 'omnibus_discount_date`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}