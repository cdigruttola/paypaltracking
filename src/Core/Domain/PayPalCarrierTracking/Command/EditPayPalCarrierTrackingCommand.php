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

namespace cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Command;

use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Exception\PayPalCarrierTrackingException;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\ValueObject\PayPalTrackingCarrierId;
use PrestaShop\PrestaShop\Core\Domain\Carrier\Exception\CarrierConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\CarrierId;
use PrestaShop\PrestaShop\Core\Domain\Country\Exception\CountryConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Country\ValueObject\CountryId;

class EditPayPalCarrierTrackingCommand
{
    /**
     * @var PayPalTrackingCarrierId
     */
    private $payPalTrackingCarrierId;
    /**
     * @var CarrierId
     */
    private $carrierId;
    /**
     * @var CountryId
     */
    private $countryId;

    /**
     * @var string|null
     */
    private $paypalCarrierEnum;

    /**
     * @param $paypalTrackingCarrierId
     * @param $carrierId
     * @param $countryId
     * @param $paypalCarrierEnum
     * @throws CarrierConstraintException
     * @throws CountryConstraintException
     * @throws PayPalCarrierTrackingException
     */
    public function __construct($paypalTrackingCarrierId, $carrierId, $countryId, $paypalCarrierEnum)
    {
        $this->payPalTrackingCarrierId = new PayPalTrackingCarrierId($paypalTrackingCarrierId);
        $this->carrierId = new CarrierId($carrierId);
        $this->countryId = new CountryId($countryId);
        $this->paypalCarrierEnum = $paypalCarrierEnum;
    }

    /**
     * @return PayPalTrackingCarrierId
     */
    public function getPayPalTrackingCarrierId(): PayPalTrackingCarrierId
    {
        return $this->payPalTrackingCarrierId;
    }

    /**
     * @return CarrierId
     */
    public function getCarrierId(): CarrierId
    {
        return $this->carrierId;
    }

    /**
     * @return CountryId
     */
    public function getCountryId(): CountryId
    {
        return $this->countryId;
    }

    /**
     * @return string|null
     */
    public function getPaypalCarrierEnum(): ?string
    {
        return $this->paypalCarrierEnum;
    }
}
