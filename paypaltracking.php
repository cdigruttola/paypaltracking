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

use cdigruttola\Module\PaypalTracking\Admin\Api\Tracking\TrackingClient;
use GuzzleHttp\Exception\ClientException;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class Paypaltracking extends Module
{
    const PAYPAL_API_LIVE_MODE = 'PAYPAL_API_LIVE_MODE';
    const PAYPAL_TRACKING_DEBUG = 'PAYPAL_TRACKING_DEBUG';
    const PAYPAL_API_CLIENT_ID = 'PAYPAL_API_CLIENT_ID';
    const PAYPAL_API_CLIENT_SECRET = 'PAYPAL_API_CLIENT_SECRET';
    const PAYPAL_TRACKING_MODULES = 'PAYPAL_TRACKING_MODULES';
    const PAYPAL_TRACKING_MODULES_ARRAY = 'PAYPAL_TRACKING_MODULES[]';

    private bool $github;
    private $product_id;

    public function __construct()
    {
        $this->name = 'paypaltracking';
        $this->tab = 'payments_gateways';
        $this->version = '2.1.3';
        $this->author = 'cdigruttola';
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
        if (!$reset) {
            include dirname(__FILE__) . '/sql/install.php';
            $this->sendMailForInstallation('c.digruttola@hotmail.it');
        }

        return parent::install()
            && $this->registerHook('actionObjectOrderCarrierUpdateAfter')
            && $this->registerHook('actionObjectOrderUpdateAfter')
            && $this->registerHook('actionCarrierUpdate');
    }

    public function uninstall($reset = false)
    {
        if (!$reset) {
            include dirname(__FILE__) . '/sql/uninstall.php';
            Configuration::deleteByName(self::PAYPAL_API_LIVE_MODE);
            Configuration::deleteByName(self::PAYPAL_TRACKING_DEBUG);
            Configuration::deleteByName(self::PAYPAL_API_CLIENT_ID);
            Configuration::deleteByName(self::PAYPAL_API_CLIENT_SECRET);
            Configuration::deleteByName(self::PAYPAL_TRACKING_MODULES);
        }

        return parent::uninstall();
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
        $output = '';
        if (Tools::isSubmit('submitPaypaltrackingModule')) {
            if ($this->postProcess()) {
                $output .= $this->displayConfirmation($this->trans('Settings updated succesfully', [], 'Modules.Paypaltracking.Main'));
            } else {
                $output .= $this->displayError($this->trans('Error occurred during settings update', [], 'Modules.Paypaltracking.Main'));
            }
        }

        if (Tools::getIsset('successBatchUpdate')) {
            if (Tools::getValue('successBatchUpdate')) {
                $output .= $this->displayConfirmation($this->trans('Synchronisation successful.', [], 'Modules.Paypaltracking.Main'));
            } else {
                $output .= $this->displayError($this->trans('Synchronisation failed, ask for support. Error: %s', [Tools::getValue('errorMessage')], 'Modules.Paypaltracking.Main'));
            }
        }
        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('current', $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
        $this->context->smarty->assign('token', Tools::getAdminTokenLite('AdminModules'));
        $this->context->smarty->assign('link', SymfonyContainer::getInstance()->get('router')->generate('admin_paypal_tracking_update_batch_orders'));

        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm() . $this->context->smarty->fetch($this->local_path . 'views/templates/admin/update_batch_orders.tpl');
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPaypaltrackingModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Settings', [], 'Modules.Paypaltracking.Main'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->trans('PayPal Live Mode', [], 'Modules.Paypaltracking.Main'),
                        'name' => self::PAYPAL_API_LIVE_MODE,
                        'is_bool' => true,
                        'desc' => $this->trans('This options set if you using SandBox or Live mode.', [], 'Modules.Paypaltracking.Main'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->trans('Live', [], 'Modules.Paypaltracking.Main'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->trans('SandBox', [], 'Modules.Paypaltracking.Main'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Debug Mode', [], 'Modules.Paypaltracking.Main'),
                        'name' => self::PAYPAL_TRACKING_DEBUG,
                        'is_bool' => true,
                        'desc' => $this->trans('This options set if you want to enable more logs in case of error.', [], 'Modules.Paypaltracking.Main'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->trans('On', [], 'Modules.Paypaltracking.Main'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->trans('Off', [], 'Modules.Paypaltracking.Main'),
                            ],
                        ],
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->trans('Enter PayPal API Client ID', [], 'Modules.Paypaltracking.Main'),
                        'name' => self::PAYPAL_API_CLIENT_ID,
                        'label' => $this->trans('PayPal API Client ID', [], 'Modules.Paypaltracking.Main'),
                    ],
                    [
                        'type' => 'password',
                        'desc' => $this->trans('Enter PayPal API Client Secret', [], 'Modules.Paypaltracking.Main'),
                        'name' => self::PAYPAL_API_CLIENT_SECRET,
                        'label' => $this->trans('PayPal API Client Secret', [], 'Modules.Paypaltracking.Main'),
                    ],
                    [
                        'type' => 'select',
                        'desc' => $this->trans('Please select modules that use PayPal', [], 'Modules.Paypaltracking.Main'),
                        'name' => self::PAYPAL_TRACKING_MODULES,
                        'class' => 'chosen',
                        'multiple' => true,
                        'options' => [
                            'query' => PaymentModule::getInstalledPaymentModules(),
                            'id' => 'id_module',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Modules.Paypaltracking.Main'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $id_shop = $this->context->shop->id;

        return [
            self::PAYPAL_API_LIVE_MODE => Configuration::get(self::PAYPAL_API_LIVE_MODE, null, null, $id_shop),
            self::PAYPAL_TRACKING_DEBUG => Configuration::get(self::PAYPAL_TRACKING_DEBUG, null, null, $id_shop),
            self::PAYPAL_API_CLIENT_ID => Configuration::get(self::PAYPAL_API_CLIENT_ID, null, null, $id_shop),
            self::PAYPAL_API_CLIENT_SECRET => Configuration::get(self::PAYPAL_API_CLIENT_SECRET, null, null, $id_shop),
            self::PAYPAL_TRACKING_MODULES_ARRAY => json_decode(Configuration::get(self::PAYPAL_TRACKING_MODULES, null, null, $id_shop), true),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        $res = true;

        foreach (array_keys($form_values) as $key) {
            if ($key === self::PAYPAL_TRACKING_MODULES_ARRAY) {
                $res &= Configuration::updateValue(self::PAYPAL_TRACKING_MODULES, json_encode(Tools::getValue(self::PAYPAL_TRACKING_MODULES)));
            } else {
                $res &= Configuration::updateValue($key, Tools::getValue($key));
            }
        }

        return $res;
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
        if (Configuration::get(self::PAYPAL_API_LIVE_MODE, null, null, $id_shop)) {
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
        $modules_id = json_decode(Configuration::get(self::PAYPAL_TRACKING_MODULES, null, null, $id_shop), true);
        $modules_name = [];
        foreach ($modules_id as $id) {
            $modules_name[] = Module::getInstanceById($id)->name;
        }

        return $modules_name;
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
