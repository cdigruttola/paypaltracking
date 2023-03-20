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

namespace cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\QueryHandler;

use Carrier;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Query\GetPayPalCarrierTrackingForEditing;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\QueryResult\EditablePayPalCarrierTracking;
use PayPalCarrierTracking;
use PrestaShop\PrestaShop\Core\Domain\Carrier\Exception\CarrierNotFoundException;

final class GetPayPalCarrierTrackingForEditingHandler implements GetPayPalCarrierTrackingForEditingHandlerInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws CarrierNotFoundException
     */
    public function handle(GetPayPalCarrierTrackingForEditing $query)
    {
        $carrierId = $query->getCarrierId();
        $payPalCarrierTracking = new PayPalCarrierTracking($carrierId->getValue());
        $carrier = new Carrier($carrierId->getValue());

        if ($payPalCarrierTracking->id !== $carrierId->getValue()) {
            throw new CarrierNotFoundException($carrierId, sprintf('Carrier with id "%s" was not found', $carrierId->getValue()));
        }

        return new EditablePayPalCarrierTracking(
            $carrierId,
            $carrier->name,
            $payPalCarrierTracking->paypal_carrier_enum
        );
    }
}
