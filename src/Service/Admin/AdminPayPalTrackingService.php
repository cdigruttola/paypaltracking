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

namespace cdigruttola\Module\PaypalTracking\Service\Admin;

use cdigruttola\Module\PaypalTracking\Admin\Api\Tracking\TrackingClient;
use cdigruttola\Module\PaypalTracking\Repository\OrderRepository;
use Configuration;
use Context;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Module;
use Order;
use OrderCarrier;
use OrderPayment;
use PayPalCarrierTracking;
use Paypaltracking;
use PrestaShopLogger;

class AdminPayPalTrackingService
{
    /** @var OrderRepository */
    private $orderRepository;
    /** @var TrackingClient */
    private $trackingService;

    /**
     * @param OrderRepository $orderRepository
     * @param TrackingClient $trackingService
     */
    public function __construct(OrderRepository $orderRepository, TrackingClient $trackingService)
    {
        $this->orderRepository = $orderRepository;
        $this->trackingService = $trackingService;
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws GuzzleException
     */
    public function updateBatchOrders($dateFrom, $dateTo) {
       $res = true;
        $orders = $this->orderRepository->findByStatesAndDateRange(Context::getContext()->shop->id, [Configuration::get('PS_OS_SHIPPING'),Configuration::get('PS_OS_DELIVERED')],$dateFrom, $dateTo, $this->getPaymentModulesName());
        foreach ($orders as $order) {
            /** @var Order $order */
            $res &= $this->updateOrder($order);
        }
        return $res;
    }

    /**
     * @param Order $order
     * @return bool
     * @throws GuzzleException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function updateOrder(Order $order): bool
    {
        $orderPayments = $order->getOrderPaymentCollection();
        if (1 !== count($orderPayments->getResults())) {
            PrestaShopLogger::addLog('More than one order payment on order ' . $order->id);
            return false;
        }

        /** @var OrderPayment $orderPayment */
        $orderPayment = $orderPayments->getFirst();
        if (empty($orderPayment->transaction_id)) {
            PrestaShopLogger::addLog('Empty transaction Id on order ' . $order->id);
            return false;
        }

        $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());

        if (empty($orderCarrier->tracking_number)) {
            PrestaShopLogger::addLog('Empty tracking number on order ' . $order->id);
            return false;
        }

        if (!PayPalCarrierTracking::checkAssociatedPayPalCarrierTracking($orderCarrier->id_carrier)) {
            PrestaShopLogger::addLog('Carrier '. $orderCarrier->id_carrier . ' not associated to Paypal Carrier Tracking on order ' . $order->id);
            return false;
        }

        try {
            $trackingService = new TrackingClient();
            $trackingService->updateShippingInfo($orderPayment->transaction_id, $orderCarrier->tracking_number, $orderCarrier->id_carrier);
        } catch (ClientException $e) {
            PrestaShopLogger::addLog($e->getMessage());
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 404) {
                try {
                    $trackingService->addShippingInfo($orderPayment->transaction_id, $orderCarrier->tracking_number, $orderCarrier->id_carrier);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog($e->getMessage());
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @param Order $order
     * @return bool
     * @throws GuzzleException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function addShippingInfo(Order $order): bool
    {
        $orderPayments = $order->getOrderPaymentCollection();
        if (1 !== count($orderPayments->getResults())) {
            PrestaShopLogger::addLog('More than one order payment on order ' . $order->id);
            return false;
        }

        /** @var OrderPayment $orderPayment */
        $orderPayment = $orderPayments->getFirst();
        if (empty($orderPayment->transaction_id)) {
            PrestaShopLogger::addLog('Empty transaction Id on order ' . $order->id);
            return false;
        }

        $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());

        if (empty($orderCarrier->tracking_number)) {
            PrestaShopLogger::addLog('Empty tracking number on order ' . $order->id);
            return false;
        }

        if (!PayPalCarrierTracking::checkAssociatedPayPalCarrierTracking($orderCarrier->id_carrier)) {
            PrestaShopLogger::addLog('Carrier '. $orderCarrier->id_carrier . ' not associated to Paypal Carrier Tracking on order ' . $order->id);
            return false;
        }

        try {
            $trackingService = new TrackingClient();
            $trackingService->addShippingInfo($orderPayment->transaction_id, $orderCarrier->tracking_number, $orderCarrier->id_carrier);
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getPaymentModulesName(): array
    {
        $id_shop = Context::getContext()->shop->id;
        $modules_id = json_decode(Configuration::get(Paypaltracking::PAYPAL_TRACKING_MODULES, null, null, $id_shop), true);
        $modules_name = [];
        foreach ($modules_id as $id) {
            $modules_name[] = Module::getInstanceById($id)->name;
        }

        return $modules_name;
    }

}
