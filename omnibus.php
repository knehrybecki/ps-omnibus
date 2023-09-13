<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . 'omnibus/classes/ClassOmnibus.php');
include_once(dirname(__FILE__) . '/../../config/config.inc.php');
include_once(dirname(__FILE__) . '/../../init.php');



class omnibus extends Module
{
    public function __construct()
    {
        $this->name = 'omnibus';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Kamil Nehrybecki';
        $this->need_instance = 1;
        $this->bootstrap = true;
        parent::__construct();
        $this->default_language = Language::getLanguage(Configuration::get('PS_LANG_DEFAULT'));
        $this->id_shop = Context::getContext()->shop->id;
        $this->displayName = $this->l('Omnibus');
        $this->description = $this->l('Displays last price from 30 days ');
        $this->confirmUninstall = $this->l('This module  Uninstall');
    }


    public function install()
    {
        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayAdminAfterHeader') &&
            $this->registerHook('actionProductUpdate') &&
            $this->registerHook('CronJob') &&
            $this->registerHook('displayProductPopup') &&
            $this->registerHook('displayOmnibusPrice');
    }

    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall();
    }
    public function hookdisplayOmnibusPrice($params)
    {

        $productID = $params['product']['id_product'];
        $productIDAttr = $params['product']['id_product_attribute'];
        $min_value = null;
        // echo ("<script>console.log('wewew: " . ($productID) . "');</script>"); 

        if ($productIDAttr === '0') {
            $get_last_discount_date = "SELECT `DateF` FROM `" . _DB_PREFIX_ . "omnibus_discount_date` WHERE id_product = $productID";
            $date_discount = Db::getInstance()->executeS($get_last_discount_date);

            foreach ($date_discount as $element) {

                foreach ($element as $key => $value) {
                    $date_value_discount = $value;
                }
            }

            $date_after_subtraction = date('Y-m-d', strtotime($date_value_discount . ' -30 days'));

            $sql = "SELECT * FROM `" . _DB_PREFIX_ . "omnibus` WHERE id_product = $productID ";
            $result = Db::getInstance()->executeS($sql);

            foreach ($result as $object) {
                foreach ($object as $key => $value) {
                    if ($key != 'id_product_attribute' && $key != 'id_product' && $key <= $date_value_discount && $key >= $date_after_subtraction && $value != 0) {
                        if ($min_value === null || $value < $min_value) {
                            $min_value = $value;
                        }
                    }
                }
            }

            $min_value = round($min_value, 2);
            $min_value = number_format($min_value, 2, '.', '');
        }

        if ($productIDAttr > 0) {

            $get_last_discount_date_attr = "SELECT `DateF` FROM `" . _DB_PREFIX_ . "omnibus_discount_date` WHERE id_product = $productID AND id_product_attribute = $productIDAttr";
            $date_discount_attr = Db::getInstance()->executeS($get_last_discount_date_attr);

            foreach ($date_discount_attr as $value_attr) {
                foreach ($value_attr as $key => $values) {
                    $date_value_discount_attr = $values;
                }
            }

            $date_after_subtraction_attr = date('Y-m-d', strtotime($date_value_discount_attr . ' -30 days'));


            $sqlCombinations = "SELECT * FROM " . _DB_PREFIX_ . "omnibus WHERE id_product= $productID and id_product_attribute= $productIDAttr ";
            $resultCom = Db::getInstance()->executeS($sqlCombinations);

            foreach ($resultCom as $object) {
                foreach ($object as $key => $value) {
                    if (
                        $key != 'id_product_attribute' && $key != 'id_product' && $key <= $date_value_discount_attr && $key >= $date_after_subtraction_attr && $value != 0
                    ) {

                        if ($min_value === null || $value < $min_value) {
                            $min_value = $value;
                        }
                    }
                }
            }
            $min_value = round($min_value, 2);
            $min_value = number_format($min_value, 2, '.', '');
        }

        $min_value_zl = $min_value . ' zł';

        $this->context->smarty->assign('min_value_zl', $min_value_zl);

        return $this->display(__FILE__, 'views/templates/hook/omnibus-front.tpl');
    }

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/omnibus_front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/omnibus_front.css');
    }

    public function hookdisplayAdminAfterHeader()
    {
        $omnibus = new ClassOmnibus();
        $omnibus->addDateWhenAddedDiscount();
        $omnibus->CheckAddProduct();
        $omnibus->createTablewhenStandardPRoduct();
        $omnibus->deleteOldDataBase();
        $omnibus->deleteCombination();
        $omnibus->deletedPRoduct();
        $omnibus->deletedFromDiscountDB();
    }
    public function hookDisplayProductPopup()
    {
        return $this->display(__FILE__, 'views/templates/hook/omnibus-modal.tpl');
    }
    public function hookCronJob()
    {
    }
}