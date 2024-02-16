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

declare(strict_types=1);

namespace cdigruttola\PaypalTracking\Admin\Api\Tracking;

use cdigruttola\PaypalTracking\Admin\Api\GenericClient;
use cdigruttola\PaypalTracking\Admin\Api\Token;
use GuzzleHttp\Exception\GuzzleException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Construct the client used to make call to maasland
 */
class TrackingClient extends GenericClient
{
    private $token;

    public function __construct()
    {
        parent::__construct();
        $this->token = new Token();
    }

    /**
     * @param $transaction_id
     * @param $tracking_number
     * @param $id_carrier
     * @param $id_country
     * @param string $status
     *
     * @throws GuzzleException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function addShippingInfo($transaction_id, $tracking_number, $id_carrier, $id_country, $status = 'IN_PROCESS')
    {
        $this->setRoute('/v1/shipping/trackers-batch');

        $paypalCarrierTracking = \PayPalCarrierTracking::getPayPalCarrierTrackingByCarrierAndCountry($id_carrier, $id_country);
        if ($paypalCarrierTracking == null) {
            \PrestaShopLogger::addLog('#PayPalTracking# Entity not found for carrier_id ' . $id_carrier . 'and country_id ' . $id_country . ', searching for worldwide');
            $paypalCarrierTracking = \PayPalCarrierTracking::getPayPalCarrierTrackingByCarrierAndCountry($id_carrier);
            if ($paypalCarrierTracking == null) {
                \PrestaShopLogger::addLog('#PayPalTracking# Entity not found for carrier_id ' . $id_carrier . 'and worldwide');

                return;
            }
        }
        $this->post([
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token->getToken(),
            ],
            'json' => [
                'trackers' => [[
                    'transaction_id' => $transaction_id,
                    'status' => $status,
                    'carrier' => $paypalCarrierTracking->paypal_carrier_enum,
                    'tracking_number' => $tracking_number,
                    'tracking_number_type' => 'CARRIER_PROVIDED',
                    'tracking_number_validated' => true,
                ]],
            ],
        ]);
    }

    /**
     * @param $transaction_id
     * @param $tracking_number
     * @param $id_carrier
     * @param $id_country
     *
     * @throws GuzzleException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function updateShippingInfo($transaction_id, $tracking_number, $id_carrier, $id_country)
    {
        $this->setRoute('/v1/shipping/trackers/' . $transaction_id . '-' . $tracking_number);

        $paypalCarrierTracking = \PayPalCarrierTracking::getPayPalCarrierTrackingByCarrierAndCountry($id_carrier, $id_country);
        if ($paypalCarrierTracking == null) {
            \PrestaShopLogger::addLog('#PayPalTracking# Entity not found for carrier_id ' . $id_carrier . 'and country_id ' . $id_country . ', searching for worldwide');
            $paypalCarrierTracking = \PayPalCarrierTracking::getPayPalCarrierTrackingByCarrierAndCountry($id_carrier);
            if ($paypalCarrierTracking == null) {
                \PrestaShopLogger::addLog('#PayPalTracking# Entity not found for carrier_id ' . $id_carrier . 'and worldwide');

                return;
            }
        }
        $this->put([
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token->getToken(),
            ],
            'json' => [
                'transaction_id' => $transaction_id,
                'status' => 'SHIPPED',
                'carrier' => $paypalCarrierTracking->paypal_carrier_enum,
                'tracking_number' => $tracking_number,
            ],
        ]);
    }

    /**
     * @param \Order[] $orderChunk
     *
     * @return bool
     *
     * @throws GuzzleException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function pool($orderChunk)
    {
        $this->setRoute('/v1/shipping/trackers-batch');

        $trackers = [];

        foreach ($orderChunk as $order) {
            $orderPayments = $order->getOrderPaymentCollection();
            /** @var \OrderPayment $orderPayment */
            $orderPayment = $orderPayments->getFirst();
            $orderCarrier = new \OrderCarrier($order->getIdOrderCarrier());
            $id_country = (new \Address($order->id_address_delivery))->id_country;

            $paypalCarrierTracking = \PayPalCarrierTracking::getPayPalCarrierTrackingByCarrierAndCountry($orderCarrier->id_carrier, $id_country);
            if ($paypalCarrierTracking == null) {
                \PrestaShopLogger::addLog('#PayPalTracking# Entity not found for carrier_id ' . $orderCarrier->id_carrier . 'and country_id ' . $id_country . ', searching for worldwide');
                $paypalCarrierTracking = \PayPalCarrierTracking::getPayPalCarrierTrackingByCarrierAndCountry($orderCarrier->id_carrier);
                if ($paypalCarrierTracking == null) {
                    \PrestaShopLogger::addLog('#PayPalTracking# Entity not found for carrier_id ' . $orderCarrier->id_carrier . 'and worldwide');

                    continue;
                }
                if (\Configuration::get(\Paypaltracking::PAYPAL_TRACKING_DEBUG)) {
                    \PrestaShopLogger::addLog('#PayPalTracking# PaypalCarrierTracking ' . var_export($paypalCarrierTracking, true));
                }

            }

            $trackers[] = [
                'transaction_id' => $orderPayment->transaction_id,
                'status' => 'SHIPPED',
                'carrier' => $paypalCarrierTracking->paypal_carrier_enum,
                'tracking_number' => $orderCarrier->tracking_number,
                'tracking_number_type' => 'CARRIER_PROVIDED',
                'tracking_number_validated' => true,
            ];
        }

        try {
            $this->post([
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token->getToken(),
                ],
                'json' => [
                    'trackers' => $trackers,
                ],
            ]);

            return true;
        } catch (\Exception $e) {
            \PrestaShopLogger::addLog('#PayPalTracking# Error during export batch - ' . $e->getMessage() . '. Exception Class ' . get_class($e) . '. Trace ' . $e->getTraceAsString());
        }

        return false;
    }
}
