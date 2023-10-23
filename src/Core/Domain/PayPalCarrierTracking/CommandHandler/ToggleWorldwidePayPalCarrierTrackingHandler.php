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

namespace cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\CommandHandler;

use cdigruttola\Module\Electronicinvoicefields\Core\Domain\AddressCustomerType\Command\ToggleNeedInvoiceAddressCustomerTypeCommand;
use cdigruttola\Module\Electronicinvoicefields\Core\Domain\AddressCustomerType\Exception\AddressCustomerTypeNotFoundException;
use cdigruttola\Module\Electronicinvoicefields\Core\Domain\AddressCustomerType\Exception\CannotToggleStatusAddressCustomerTypeException;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Command\ToggleWorldwidePayPalCarrierTrackingCommand;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Exception\CannotToggleWorldwidePayPalTrackingCarrierException;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Exception\PayPalCarrierTrackingException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Handles command that toggle status of address customer type
 *
 * @internal
 */
final class ToggleWorldwidePayPalCarrierTrackingHandler extends AbstractPayPalCarrierTrackingHandler implements ToggleWorldwidePayPalCarrierTrackingHandlerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param ToggleWorldwidePayPalCarrierTrackingCommand $command
     * @throws CannotToggleWorldwidePayPalTrackingCarrierException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws PayPalCarrierTrackingException
     */
    public function handle(ToggleWorldwidePayPalCarrierTrackingCommand $command)
    {
        $payPalTrackingCarrierId = $command->getPayPalTrackingCarrierId();
        $payPalCarrierTracking = $this->getPayPalCarrierTracking($payPalTrackingCarrierId);

        $payPalCarrierTracking->worldwide = !(int) $payPalCarrierTracking->worldwide;
        if (false === $payPalCarrierTracking->update()) {
            throw new CannotToggleWorldwidePayPalTrackingCarrierException(sprintf('Unable to toggle worldwide of paypal tracking carrier with id "%d"', $payPalTrackingCarrierId->getValue()));
        }
    }
}
