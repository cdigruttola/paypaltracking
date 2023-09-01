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

use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Exception\MissingPayPalCarrierTrackingRequiredFieldsException;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Exception\PayPalCarrierTrackingException;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\ValueObject\PayPalTrackingCarrierId;
use PayPalCarrierTracking;
use PrestaShop\PrestaShop\Adapter\Carrier\AbstractCarrierHandler;
use PrestaShopException;

abstract class AbstractPayPalCarrierTrackingHandler extends AbstractCarrierHandler
{

    /**
     * @param PayPalTrackingCarrierId $payPalTrackingCarrierId
     * @return PayPalCarrierTracking
     * @throws PayPalCarrierTrackingException
     */
    protected function getPayPalCarrierTracking(PayPalTrackingCarrierId $payPalTrackingCarrierId)
    {
        try {
            $payPalCarrierTracking = new PayPalCarrierTracking($payPalTrackingCarrierId->getValue());
        } catch (PrestaShopException $exception) {
            throw new PayPalCarrierTrackingException('Failed to create new PayPalCarrierTracking', 0, $exception);
        }

        if ($payPalCarrierTracking->id !== $payPalTrackingCarrierId->getValue()) {
            throw new PayPalCarrierTrackingException(sprintf('Entity with id "%s" was not found', $payPalTrackingCarrierId->getValue()));
        }

        return $payPalCarrierTracking;
    }

    /**
     * @throws MissingPayPalCarrierTrackingRequiredFieldsException
     */
    protected function assertRequiredFieldsAreNotMissing(PayPalCarrierTracking $payPalCarrierTracking)
    {
        $errors = $payPalCarrierTracking->validateFieldsRequiredDatabase();

        if (!empty($errors)) {
            $missingFields = array_keys($errors);

            throw new MissingPayPalCarrierTrackingRequiredFieldsException($missingFields, sprintf('One or more required fields for PayPalCarrierTracking are missing. Missing fields are: %s', implode(',', $missingFields)));
        }
    }
}
