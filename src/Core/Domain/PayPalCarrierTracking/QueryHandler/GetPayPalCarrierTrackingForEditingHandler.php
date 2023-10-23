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

use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Exception\PayPalCarrierTrackingException;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Query\GetPayPalCarrierTrackingForEditing;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\QueryResult\EditablePayPalCarrierTracking;
use PrestaShop\PrestaShop\Core\Domain\Carrier\Exception\CarrierConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\CarrierId;
use PrestaShop\PrestaShop\Core\Domain\Country\Exception\CountryConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Country\ValueObject\CountryId;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class GetPayPalCarrierTrackingForEditingHandler implements GetPayPalCarrierTrackingForEditingHandlerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param GetPayPalCarrierTrackingForEditing $query
     *
     * @return EditablePayPalCarrierTracking
     *
     * @throws PayPalCarrierTrackingException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws CountryConstraintException
     * @throws CarrierConstraintException
     */
    public function handle(GetPayPalCarrierTrackingForEditing $query)
    {
        $payPalTrackingCarrierId = $query->getPayPalTrackingCarrierId();
        $payPalCarrierTracking = new \PayPalCarrierTracking($payPalTrackingCarrierId->getValue());
        $carrier = new \Carrier($payPalCarrierTracking->id_carrier);
        $country = new \Country($payPalCarrierTracking->id_country);

        if ($payPalCarrierTracking->id !== $payPalTrackingCarrierId->getValue()) {
            throw new PayPalCarrierTrackingException($payPalTrackingCarrierId, sprintf('Entity with id "%s" was not found', $payPalTrackingCarrierId->getValue()));
        }

        return new EditablePayPalCarrierTracking(
            $payPalTrackingCarrierId,
            new CarrierId($carrier->id),
            new CountryId($country->id),
            $payPalCarrierTracking->paypal_carrier_enum,
            (bool) $payPalCarrierTracking->worldwide
        );
    }
}
