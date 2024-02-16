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

namespace cdigruttola\PaypalTracking\Form\DataConfiguration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Configuration\AbstractMultistoreConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaypalTrackingConfigurationData extends AbstractMultistoreConfiguration
{
    public const PAYPAL_API_LIVE_MODE = 'PAYPAL_API_LIVE_MODE';
    public const PAYPAL_TRACKING_DEBUG = 'PAYPAL_TRACKING_DEBUG';
    public const PAYPAL_API_CLIENT_ID = 'PAYPAL_API_CLIENT_ID';
    public const PAYPAL_API_CLIENT_SECRET = 'PAYPAL_API_CLIENT_SECRET';
    public const PAYPAL_TRACKING_MODULES = 'PAYPAL_TRACKING_MODULES';
    private const CONFIGURATION_FIELDS = [
        'api_live_mode',
        'debug',
        'api_client_id',
        'api_client_secret',
        'modules',
    ];

    /**
     * @return OptionsResolver
     */
    protected function buildResolver(): OptionsResolver
    {
        return (new OptionsResolver())
            ->setDefined(self::CONFIGURATION_FIELDS)
            ->setAllowedTypes('api_live_mode', 'bool')
            ->setAllowedTypes('debug', 'bool')
            ->setAllowedTypes('api_client_id', 'string')
            ->setAllowedTypes('api_client_secret', 'string')
            ->setAllowedTypes('modules', ['null', 'array']);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        $return = [];
        $shopConstraint = $this->getShopConstraint();

        $return['api_live_mode'] = $this->configuration->get(self::PAYPAL_API_LIVE_MODE, true, $shopConstraint);
        $return['debug'] = $this->configuration->get(self::PAYPAL_TRACKING_DEBUG, false, $shopConstraint);
        $return['api_client_id'] = $this->configuration->get(self::PAYPAL_API_CLIENT_ID, '', $shopConstraint);
        $return['api_client_secret'] = $this->configuration->get(self::PAYPAL_API_CLIENT_SECRET, '', $shopConstraint);
        $return['modules'] = json_decode($this->configuration->get(self::PAYPAL_TRACKING_MODULES, '', $shopConstraint), true);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function updateConfiguration(array $configuration): array
    {
        if ($this->validateConfiguration($configuration)) {
            $shopConstraint = $this->getShopConstraint();
            $this->updateConfigurationValue(self::PAYPAL_API_LIVE_MODE, 'api_live_mode', $configuration, $shopConstraint);
            $this->updateConfigurationValue(self::PAYPAL_TRACKING_DEBUG, 'debug', $configuration, $shopConstraint);
            $this->updateConfigurationValue(self::PAYPAL_API_CLIENT_ID, 'api_client_id', $configuration, $shopConstraint);
            $this->updateConfigurationValue(self::PAYPAL_API_CLIENT_SECRET, 'api_client_secret', $configuration, $shopConstraint);
            $this->configuration->set(self::PAYPAL_TRACKING_MODULES, json_encode($configuration['modules']), $shopConstraint);
        }

        return [];
    }
}
