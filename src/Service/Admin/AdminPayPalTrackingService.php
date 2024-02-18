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

namespace cdigruttola\PaypalTracking\Service\Admin;

use cdigruttola\PaypalTracking\Admin\Api\Tracking\TrackingClient;
use cdigruttola\PaypalTracking\Entity\PaypalCarrierTracking;
use cdigruttola\PaypalTracking\Form\DataConfiguration\PaypalTrackingConfigurationData;
use cdigruttola\PaypalTracking\Repository\OrderRepository;
use cdigruttola\PaypalTracking\Repository\PaypalCarrierTrackingRepository;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPayPalTrackingService
{
    /** @var OrderRepository */
    private $orderRepository;
    /** @var PaypalCarrierTrackingRepository */
    private $paypalCarrierTrackingRepository;
    /** @var TrackingClient */
    private $trackingService;

    /**
     * @param OrderRepository $orderRepository
     * @param TrackingClient $trackingService
     */
    public function __construct(OrderRepository $orderRepository,
        PaypalCarrierTrackingRepository $paypalCarrierTrackingRepository,
        TrackingClient $trackingService)
    {
        $this->orderRepository = $orderRepository;
        $this->paypalCarrierTrackingRepository = $paypalCarrierTrackingRepository;
        $this->trackingService = $trackingService;
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws GuzzleException
     */
    public function updateBatchOrders($dateFrom, $dateTo)
    {
        $id_shop = \Context::getContext()->shop->id;
        $res = true;
        /** @var \Order[] $orders */
        $orders = $this->orderRepository->findByStatesAndDateRange(
            $id_shop,
            [
                \Configuration::get('PS_OS_SHIPPING'),
                \Configuration::get('PS_OS_DELIVERED'),
            ],
            $dateFrom,
            $dateTo,
            $this->getPaymentModulesName())
            ->getResults();

        \PrestaShopLogger::addLog('#PayPalTracking# Found ' . count($orders) . ' orders');
        if (\Configuration::get(PaypalTrackingConfigurationData::PAYPAL_TRACKING_DEBUG, null, null, $id_shop)) {
            \PrestaShopLogger::addLog('#PayPalTracking# Orders ' . var_export($orders, true));
        }
        $orders = array_filter($orders, [$this, 'checkOrder']);
        $ordersChunk = array_chunk($orders, 20);
        \PrestaShopLogger::addLog('#PayPalTracking# Found ' . count($ordersChunk) . ' order chunk');
        if (\Configuration::get(PaypalTrackingConfigurationData::PAYPAL_TRACKING_DEBUG, null, null, $id_shop)) {
            \PrestaShopLogger::addLog('#PayPalTracking# Orders chunk ' . var_export($orders, true));
        }
        foreach ($ordersChunk as $orderChunk) {
            $res &= $this->trackingService->pool($orderChunk);
        }

        return $res;
    }

    /**
     * @param \Order $order
     *
     * @return bool
     *
     * @throws GuzzleException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function updateOrder(\Order $order): bool
    {
        if (!$this->checkOrder($order)) {
            return false;
        }

        list($orderPayment, $orderCarrier, $id_country) = $this->getOrderInformation($order);

        try {
            $this->trackingService->updateShippingInfo($orderPayment->transaction_id, $orderCarrier->tracking_number, $orderCarrier->id_carrier, $id_country);
        } catch (ClientException $e) {
            \PrestaShopLogger::addLog('#PayPalTracking# ' . $e->getMessage());
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 404) {
                try {
                    $this->trackingService->addShippingInfo($orderPayment->transaction_id, $orderCarrier->tracking_number, $orderCarrier->id_carrier, $id_country, 'SHIPPED');
                } catch (\Exception $e) {
                    \PrestaShopLogger::addLog('#PayPalTracking# ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            \PrestaShopLogger::addLog('#PayPalTracking# ' . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @param \Order $order
     *
     * @return bool
     *
     * @throws GuzzleException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function addShippingInfo(\Order $order): bool
    {
        if (!$this->checkOrder($order)) {
            return false;
        }

        list($orderPayment, $orderCarrier, $id_country) = $this->getOrderInformation($order);

        $status = 'IN_PROCESS';
        if (\Configuration::get('PS_OS_SHIPPING') == $order->getCurrentOrderState()->id) {
            $status = 'SHIPPED';
        }

        try {
            $this->trackingService->addShippingInfo($orderPayment->transaction_id, $orderCarrier->tracking_number, $orderCarrier->id_carrier, $id_country, $status);
        } catch (\Exception $e) {
            \PrestaShopLogger::addLog('#PayPalTracking# ' . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getPaymentModulesName(): array
    {
        $id_shop = \Context::getContext()->shop->id;

        return json_decode(\Configuration::get(PaypalTrackingConfigurationData::PAYPAL_TRACKING_MODULES, null, null, $id_shop), true);
    }

    /**
     * @param \Order $order
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function checkOrder(\Order $order)
    {
        $modules_name = $this->getPaymentModulesName();

        if (!in_array($order->module, $modules_name)) {
            \PrestaShopLogger::addLog('#PayPalTracking# Payment module for order ' . $order->id . ' is ' . $order->module . '. In module are associated -> ' . var_export($modules_name, true));

            return false;
        }

        $orderPayments = $order->getOrderPaymentCollection();
        if (1 !== count($orderPayments->getResults())) {
            \PrestaShopLogger::addLog('#PayPalTracking# More than one order payment on order ' . $order->id);

            return false;
        }

        /** @var \OrderPayment $orderPayment */
        $orderPayment = $orderPayments->getFirst();
        if (empty($orderPayment->transaction_id)) {
            \PrestaShopLogger::addLog('#PayPalTracking# Empty transaction Id on order ' . $order->id);

            return false;
        }

        $orderCarrier = new \OrderCarrier($order->getIdOrderCarrier());

        if (empty($orderCarrier->tracking_number)) {
            \PrestaShopLogger::addLog('#PayPalTracking# Empty tracking number on order ' . $order->id);

            return false;
        }

        $id_country = (new \Address($order->id_address_delivery))->id_country;

        /** @var PaypalCarrierTracking[] $paypalCarrierTrackings */
        $paypalCarrierTrackings = $this->paypalCarrierTrackingRepository->findBy(
            [
                'id_carrier' => $orderCarrier->id_carrier,
                'id_country' => $id_country,
            ]
        );

        if (empty($paypalCarrierTrackings)) {
            \PrestaShopLogger::addLog('#PayPalTracking# Carrier ' . $orderCarrier->id_carrier . ' not associated to Paypal Carrier Tracking on order ' . $order->id . ' for country ' . $id_country . ', searching for worldwide');
            $paypalCarrierTrackings = $this->paypalCarrierTrackingRepository->findBy(
                [
                    'id_carrier' => $orderCarrier->id_carrier,
                    'worldwide' => true,
                ]
            );
            if (empty($paypalCarrierTrackings)) {
                \PrestaShopLogger::addLog('#PayPalTracking# Carrier ' . $orderCarrier->id_carrier . ' not associated to Paypal Carrier Tracking on order ' . $order->id . ' for worldwide');

                return false;
            } else {
                $id_shop = \Context::getContext()->shop->id;
                if (\Configuration::get(PaypalTrackingConfigurationData::PAYPAL_TRACKING_DEBUG, null, null, $id_shop)) {
                    \PrestaShopLogger::addLog('#PayPalTracking# Found Order to export ' . var_export($order, true));
                }

                return true;
            }
        }

        return true;
    }

    /**
     * @param \Order $order
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function getOrderInformation(\Order $order): array
    {
        $orderPayments = $order->getOrderPaymentCollection();
        /** @var \OrderPayment $orderPayment */
        $orderPayment = $orderPayments->getFirst();
        $orderCarrier = new \OrderCarrier($order->getIdOrderCarrier());
        $id_country = (new \Address($order->id_address_delivery))->id_country;

        return [$orderPayment, $orderCarrier, $id_country];
    }
}
