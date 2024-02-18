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

use cdigruttola\PaypalTracking\Form\DataConfiguration\PaypalTrackingConfigurationData;
use cdigruttola\PaypalTracking\Hook\HookInterface;
use cdigruttola\PaypalTracking\Installer\DatabaseYamlParser;
use cdigruttola\PaypalTracking\Installer\PaypalTrackingInstaller;
use cdigruttola\PaypalTracking\Installer\Provider\DatabaseYamlProvider;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class Paypaltracking extends Module
{
    private bool $github;
    private $product_id;

    public function __construct()
    {
        $this->name = 'paypaltracking';
        $this->tab = 'payments_gateways';
        $this->version = '3.0.0';
        $this->author = 'cdigruttola';
        $this->module_key = 'aa9cf1c7972b1a64ce880690d6bdd1ae';
        $this->product_id = 'a4Mllbdc2SdDufSlpD0TxQ==';
        $this->need_instance = 0;
        $this->github = true;

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

    /**
     * @param string $methodName
     * @param array $arguments
     *
     * @return void|null
     */
    public function __call(string $methodName, array $arguments)
    {
        if (method_exists($this, $methodName)) {
            return $this->{$methodName}(...$arguments);
        } elseif (str_starts_with($methodName, 'hook')) {
            if ($hook = $this->getHookObject($methodName)) {
                return $hook->execute(...$arguments);
            }
        } else {
            return null;
        }
    }

    /**
     * @param string $methodName
     *
     * @return HookInterface|null
     */
    private function getHookObject(string $methodName): ?HookInterface
    {
        $serviceName = sprintf(
            'cdigruttola.paypaltracking.hook.%s',
            Tools::toUnderscoreCase(str_replace('hook', '', $methodName))
        );

        $hook = $this->getService($serviceName);

        return $hook instanceof HookInterface ? $hook : null;
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
            $mail_iso = 'en';
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
