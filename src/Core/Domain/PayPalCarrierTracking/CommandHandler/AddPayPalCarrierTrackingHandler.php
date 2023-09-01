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

use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Command\AddPayPalCarrierTrackingCommand;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Exception\PayPalCarrierTrackingException;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\ValueObject\PayPalTrackingCarrierId;
use PayPalCarrierTracking;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\CarrierId;
use PrestaShopException;

final class AddPayPalCarrierTrackingHandler extends AbstractPayPalCarrierTrackingHandler implements AddPayPalCarrierTrackingHandlerInterface
{
    /**
     * {@inheritdoc}
     * @throws PayPalCarrierTrackingException
     * @throws PrestaShopException
     */
    public function handle(AddPayPalCarrierTrackingCommand $command)
    {
        $payPalCarrierTracking = new PayPalCarrierTracking();

        $this->fillPayPalCarrierTrackingWithCommandData($payPalCarrierTracking, $command);
        $this->assertRequiredFieldsAreNotMissing($payPalCarrierTracking);

        if (false === $payPalCarrierTracking->validateFields(false)) {
            throw new PayPalCarrierTrackingException('PayPalCarrierTracking contains invalid field values');
        }

        $payPalCarrierTracking->add();

        return new PayPalTrackingCarrierId((int) $payPalCarrierTracking->id);
    }

    private function fillPayPalCarrierTrackingWithCommandData(PayPalCarrierTracking $payPalCarrierTracking, AddPayPalCarrierTrackingCommand $command)
    {
        $payPalCarrierTracking->id_carrier = $command->getCarrierId();
        $payPalCarrierTracking->id_country = $command->getCountryId();
        $payPalCarrierTracking->paypal_carrier_enum = $command->getPaypalCarrierEnum();
    }
}
