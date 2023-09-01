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

use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Command\EditPayPalCarrierTrackingCommand;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Exception\PayPalCarrierTrackingException;

final class EditPayPalCarrierTrackingHandler extends AbstractPayPalCarrierTrackingHandler implements EditPayPalCarrierTrackingHandlerInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws PayPalCarrierTrackingException
     */
    public function handle(EditPayPalCarrierTrackingCommand $command)
    {
        $payPalCarrierTrackingId = $command->getPayPalTrackingCarrierId();
        $carrierId = $command->getCarrierId();
        $payPalCarrierTracking = $this->getPayPalCarrierTracking($payPalCarrierTrackingId);

        $this->getCarrier($carrierId);

        $this->updatePayPalCarrierTrackingWithCommandData($payPalCarrierTracking, $command);
        $this->assertRequiredFieldsAreNotMissing($payPalCarrierTracking);

        if (false === $payPalCarrierTracking->validateFields(false)) {
            throw new PayPalCarrierTrackingException('PayPalCarrierTracking contains invalid field values');
        }

        if (false === $payPalCarrierTracking->update()) {
            throw new PayPalCarrierTrackingException('Failed to update PayPalCarrierTracking');
        }
    }

    private function updatePayPalCarrierTrackingWithCommandData(\PayPalCarrierTracking $payPalCarrierTracking, EditPayPalCarrierTrackingCommand $command)
    {
        if (null !== $command->getCarrierId()) {
            $payPalCarrierTracking->id_carrier = $command->getCarrierId()->getValue();
        }
        if (null !== $command->getCountryId()) {
            $payPalCarrierTracking->id_country = $command->getCountryId()->getValue();
        }
        if (null !== $command->getPaypalCarrierEnum()) {
            $payPalCarrierTracking->paypal_carrier_enum = $command->getPaypalCarrierEnum();
        }
    }
}
