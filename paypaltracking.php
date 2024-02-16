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

use cdigruttola\PaypalTracking\Admin\Api\Tracking\TrackingClient;
use cdigruttola\PaypalTracking\Form\DataConfiguration\PaypalTrackingConfigurationData;
use cdigruttola\PaypalTracking\Installer\DatabaseYamlParser;
use cdigruttola\PaypalTracking\Installer\PaypalTrackingInstaller;
use cdigruttola\PaypalTracking\Installer\Provider\DatabaseYamlProvider;
use GuzzleHttp\Exception\ClientException;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class Paypaltracking extends Module
{
    private bool $github;
    private $product_id;

    public function __construct()
    {
        $this->name = 'paypaltracking';
        $this->tab = 'payments_gateways';
        $this->version = '2.1.3';
        $this->author = 'cdigruttola';
        $this->module_key = 'aa9cf1c7972b1a64ce880690d6bdd1ae';
        $this->product_id = 'a4Mllbdc2SdDufSlpD0TxQ==';
        $this->need_instance = 0;
        $this->github = true;

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;
        $tabNames = [];
        foreach (Language::getLanguages() as $lang) {
            $tabNames[$lang['locale']] = $this->trans('PayPal Tracking', [], 'Modules.Paypaltracking.Main', $lang['locale']);
        }

        $this->tabs = [
            [
                'name' => $tabNames,
                'class_name' => 'AdminPayPalTracking',
                'visible' => true,
                'route_name' => 'admin_paypal_tracking',
                'parent_class_name' => 'AdminParentPayment',
                'wording' => 'PayPal Tracking',
                'wording_domain' => 'Modules.Paypaltracking.Main',
            ],
        ];

        parent::__construct();

        $this->displayName = $this->trans('TrackPrestaPay - Paypal Tracking Module Prestashop', [], 'Modules.Paypaltracking.Main');
        $this->description = $this->trans('This module helps to update tracking number to PayPal', [], 'Modules.Paypaltracking.Main');

        $this->ps_versions_compliancy = ['min' => '1.7.8', 'max' => _PS_VERSION_];
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install($reset = false)
    {
        $tableResult = true;
        if (!$reset) {
            $tableResult = $this->getInstaller()->createTables();
            $this->sendMailForInstallation('c.digruttola@hotmail.it');
        }

        return $tableResult && parent::install()
            && $this->registerHook('actionObjectOrderCarrierUpdateAfter')
            && $this->registerHook('actionObjectOrderUpdateAfter')
            && $this->registerHook('actionCarrierUpdate');
    }

    public function uninstall($reset = false)
    {
        $tableResult = true;
        if (!$reset) {
            $tableResult = $this->getInstaller()->dropTables();
            Configuration::deleteByName(PaypalTrackingConfigurationData::PAYPAL_API_LIVE_MODE);
            Configuration::deleteByName(PaypalTrackingConfigurationData::PAYPAL_TRACKING_DEBUG);
            Configuration::deleteByName(PaypalTrackingConfigurationData::PAYPAL_API_CLIENT_ID);
            Configuration::deleteByName(PaypalTrackingConfigurationData::PAYPAL_API_CLIENT_SECRET);
            Configuration::deleteByName(PaypalTrackingConfigurationData::PAYPAL_TRACKING_MODULES);
        }

        return $tableResult && parent::uninstall();
    }

    public function reset()
    {
        return $this->uninstall(true) && $this->install(true);
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        Tools::redirectAdmin(SymfonyContainer::getInstance()->get('router')->generate('admin_paypal_tracking_controller'));
    }

    private function getInstaller(): PaypalTrackingInstaller
    {
        try {
            $installer = $this->getService('cdigruttola.paypaltracking.installer');
        } catch (Error $error) {
            $installer = null;
        }

        if (null === $installer) {
            $installer = new PaypalTrackingInstaller(
                $this->getService('doctrine.dbal.default_connection'),
                new DatabaseYamlParser(new DatabaseYamlProvider($this)),
                $this->context
            );
        }

        return $installer;
    }

    /**
     * @template T
     *
     * @param class-string<T>|string $serviceName
     *
     * @return T|object|null
     */
    public function getService($serviceName)
    {
        try {
            return $this->get($serviceName);
        } catch (ServiceNotFoundException|Exception $exception) {
            if (_PS_MODE_DEV_) {
                throw $exception;
            }

            return null;
        }
    }

    public function hookActionObjectOrderCarrierUpdateAfter($params)
    {
        if ($this->active) {
            if (!isset($params['object'])) {
                return;
            }

            /** @var OrderCarrier $orderCarrier */
            $orderCarrier = $params['object'];

            if (!Validate::isLoadedObject($orderCarrier) || empty($orderCarrier->tracking_number)) {
                return;
            }

            $modules_name = $this->getPaymentModulesName();

            $order = new Order($orderCarrier->id_order);
            if (!in_array($order->module, $modules_name)) {
                \PrestaShopLogger::addLog('#PayPalTracking# Payment module for order ' . $order->id . ' is ' . $order->module . '. In module are associated -> ' . var_export($modules_name, true));
                unset($order);

                return;
            }

            $orderPayments = $order->getOrderPaymentCollection();
            $id_country = (new Address($order->id_address_delivery))->id_country;

            $status = 'IN_PROCESS';
            if (Configuration::get('PS_OS_SHIPPING') == $order->getCurrentOrderState()->id) {
                $status = 'SHIPPED';
            }
            unset($order);
            if (1 !== count($orderPayments->getResults())) {
                \PrestaShopLogger::addLog('#PayPalTracking# More than one order payment on order ' . $orderCarrier->id_order);

                return;
            }

            /** @var OrderPayment $orderPayment */
            $orderPayment = $orderPayments->getFirst();
            if (empty($orderPayment->transaction_id)) {
                \PrestaShopLogger::addLog('#PayPalTracking# Empty transaction Id on order ' . $orderCarrier->id_order);

                return;
            }

            if (!PayPalCarrierTracking::checkAssociatedPayPalCarrierTracking($orderCarrier->id_carrier, $id_country)) {
                \PrestaShopLogger::addLog('#PayPalTracking# Carrier ' . $orderCarrier->id_carrier . ' not associated to Paypal Carrier Tracking on order ' . $orderCarrier->id_order . ' for country ' . $id_country . ', searching for worldwide');
                if (!PayPalCarrierTracking::checkAssociatedPayPalCarrierTracking($orderCarrier->id_carrier)) {
                    \PrestaShopLogger::addLog('#PayPalTracking# Carrier ' . $orderCarrier->id_carrier . ' not associated to Paypal Carrier Tracking on order ' . $orderCarrier->id_order . ' for worldwide');

                    return;
                }
            }

            try {
                $trackingService = new TrackingClient();
                $trackingService->addShippingInfo($orderPayment->transaction_id, $orderCarrier->tracking_number, $orderCarrier->id_carrier, $id_country, $status);
            } catch (Exception $e) {
                PrestaShopLogger::addLog('#PayPalTracking# ' . $e->getMessage());
            }
        }
    }

    public function hookActionObjectOrderUpdateAfter($params)
    {
        if ($this->active) {
            if (!isset($params['object'])) {
                return;
            }

            /** @var Order $order */
            $order = $params['object'];

            if (!Validate::isLoadedObject($order)) {
                return;
            }

            if (Configuration::get('PS_OS_SHIPPING') != $order->getCurrentOrderState()->id) {
                \PrestaShopLogger::addLog('#PayPalTracking# Order status on order ' . $order->id . ' is not PS_OS_SHIPPING');

                return;
            }

            $modules_name = $this->getPaymentModulesName();

            if (!in_array($order->module, $modules_name)) {
                \PrestaShopLogger::addLog('#PayPalTracking# Payment module for order ' . $order->id . ' is ' . $order->module . '. In module are associated -> ' . var_export($modules_name, true));

                return;
            }

            $orderPayments = $order->getOrderPaymentCollection();
            if (1 !== count($orderPayments->getResults())) {
                \PrestaShopLogger::addLog('#PayPalTracking# More than one order payment on order ' . $order->id);

                return;
            }

            /** @var OrderPayment $orderPayment */
            $orderPayment = $orderPayments->getFirst();
            if (empty($orderPayment->transaction_id)) {
                \PrestaShopLogger::addLog('#PayPalTracking# Empty transaction Id on order ' . $order->id);

                return;
            }

            $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());

            if (empty($orderCarrier->tracking_number)) {
                return;
            }

            $id_country = (new Address($order->id_address_delivery))->id_country;
            if (!PayPalCarrierTracking::checkAssociatedPayPalCarrierTracking($orderCarrier->id_carrier, $id_country)) {
                \PrestaShopLogger::addLog('#PayPalTracking# Carrier ' . $orderCarrier->id_carrier . ' not associated to Paypal Carrier Tracking on order ' . $orderCarrier->id_order . ' for country ' . $id_country . ', searching for worldwide');
                if (!PayPalCarrierTracking::checkAssociatedPayPalCarrierTracking($orderCarrier->id_carrier)) {
                    \PrestaShopLogger::addLog('#PayPalTracking# Carrier ' . $orderCarrier->id_carrier . ' not associated to Paypal Carrier Tracking on order ' . $orderCarrier->id_order . ' for worldwide');

                    return;
                }
            }

            try {
                $trackingService = new TrackingClient();
                $trackingService->updateShippingInfo($orderPayment->transaction_id, $orderCarrier->tracking_number, $orderCarrier->id_carrier, $id_country);
            } catch (ClientException $e) {
                PrestaShopLogger::addLog('#PayPalTracking# ' . $e->getMessage());
                if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 404) {
                    try {
                        $trackingService->addShippingInfo($orderPayment->transaction_id, $orderCarrier->tracking_number, $orderCarrier->id_carrier, $id_country, 'SHIPPED');
                    } catch (Exception $e) {
                        PrestaShopLogger::addLog('#PayPalTracking# ' . $e->getMessage());
                    }
                }
            } catch (Exception $e) {
                PrestaShopLogger::addLog('#PayPalTracking# ' . $e->getMessage());
            }
        }
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionCarrierUpdate($params)
    {
        if (!$this->active) {
            return;
        }
        $id_carrier_old = (int) $params['id_carrier'];
        $id_carrier_new = (int) $params['carrier']->id;
        $paypalCarrierTrackings = PayPalCarrierTracking::getPayPalCarrierTrackingByCarrier($id_carrier_old);
        if (empty($paypalCarrierTrackings)) {
            PrestaShopLogger::addLog('#PayPalTracking# Entities not found for carrier_id ' . $id_carrier_old);

            return;
        }
        foreach ($paypalCarrierTrackings as $paypalCarrierTracking) {
            $paypalCarrierTracking->id_carrier = $id_carrier_new;
            if (false === $paypalCarrierTracking->update()) {
                PrestaShopLogger::addLog("#PayPalTracking# Error during update of $id_carrier_old to $id_carrier_new");
            }
        }
    }

    /**
     * @return string
     */
    public function getPayPalApiUrl(): string
    {
        $id_shop = Context::getContext()->shop->id;
        if (Configuration::get(PaypalTrackingConfigurationData::PAYPAL_API_LIVE_MODE, null, null, $id_shop)) {
            return 'https://api-m.paypal.com';
        } else {
            return 'https://api-m.sandbox.paypal.com';
        }
    }

    /**
     * @return array
     */
    public function getPaymentModulesName(): array
    {
        $id_shop = $this->context->shop->id;

        return json_decode(Configuration::get(PaypalTrackingConfigurationData::PAYPAL_TRACKING_MODULES, null, null, $id_shop), true);
    }

    /**
     * @return void
     */
    private function sendMailForInstallation($address): void
    {
        $mail_iso = Language::getIsoById((int) Configuration::get('PS_LANG_DEFAULT'));

        $dir_mail = false;
        if (file_exists(_PS_MODULE_DIR_ . $this->name . '/mails/' . $mail_iso . '/installation_paypaltracking_mail.txt')
            && file_exists(_PS_MODULE_DIR_ . $this->name . '/mails/' . $mail_iso . '/installation_paypaltracking_mail.html')) {
            $dir_mail = _PS_MODULE_DIR_ . $this->name . '/mails/';
        }

        if (file_exists(_PS_MAIL_DIR_ . $mail_iso . '/installation_paypaltracking_mail.txt')
            && file_exists(_PS_MAIL_DIR_ . $mail_iso . '/installation_paypaltracking_mail.html')) {
            $dir_mail = _PS_MAIL_DIR_;
        }

        if (!$dir_mail) {
           $mail_iso = "en";
            $dir_mail = _PS_MODULE_DIR_ . $this->name . '/mails/';
        }

        $data = [
            '{domain}' => $this->context->shop->getBaseURL(),
            '{addon}' => isset($this->module_key) && !empty($this->module_key),
            '{gumroad}' => isset($this->product_id),
            '{github}' => $this->github,
        ];

        Mail::send(
            $this->context->language->id,
            'installation_paypaltracking_mail',
            $this->context->getTranslator()->trans(
                'Installation PayPalTracking Module',
                [],
                'Modules.Paypaltracking.Main',
                $this->context->language->locale
            ),
            $data,
            $address,
            'Module Owner',
            null,
            null,
            null,
            null,
            $dir_mail,
            false,
            (int) $this->context->shop->id
        );
    }
}
