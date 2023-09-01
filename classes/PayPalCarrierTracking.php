<?php
/**
 * Copyright since 2007 Carmine Di Gruttola
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    cdigruttola <c.digruttola@hotmail.it>
 * @copyright Copyright since 2007 Carmine Di Gruttola
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class PayPalCarrierTracking extends ObjectModel
{
    /** @var int */
    public $id_paypal_carrier_tracking;
    /** @var int */
    public $id_carrier;
    /** @var int */
    public $id_country;

    /** @var string */
    public $paypal_carrier_enum;
    /**
     * @var string
     */
    public $date_add;
    /**
     * @var string
     */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'paypal_carrier_tracking',
        'primary' => 'id_paypal_carrier_tracking',
        'fields' => [
            'id_paypal_carrier_tracking' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'id_carrier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'id_country' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'paypal_carrier_enum' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public static function checkAssociatedPayPalCarrierTracking($carrierId, $countryId)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT COUNT(DISTINCT a.id_carrier)
		FROM `' . _DB_PREFIX_ . 'paypal_carrier_tracking` a
		WHERE a.`id_carrier` = ' . $carrierId . ' AND a.`id_country` = ' . $countryId);

        return $result > 0;
    }

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    public static function getPayPalCarrierTrackingByCarrierAndCountry($carrierId, $countryId): ?PayPalCarrierTracking
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT DISTINCT a.id_paypal_carrier_tracking
		FROM `' . _DB_PREFIX_ . 'paypal_carrier_tracking` a
		WHERE a.`id_carrier` = ' . $carrierId . ' AND a.`id_country` = ' . $countryId);

        foreach ($result as $row) {
            if ((int) $row['id_paypal_carrier_tracking'] != 0) {
                return new self((int) $row['id_paypal_carrier_tracking']);
            }
        }

        return null;
    }

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    public static function getPayPalCarrierTrackingByCarrier($carrierId)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT DISTINCT a.id_paypal_carrier_tracking
		FROM `' . _DB_PREFIX_ . 'paypal_carrier_tracking` a
		WHERE a.`id_carrier` = ' . $carrierId);

        $collection = [];

        foreach ($result as $row) {
            if ((int) $row['id_paypal_carrier_tracking'] != 0) {
                $collection[] = new self((int) $row['id_paypal_carrier_tracking']);
            }
        }

        return $collection;
    }
}
