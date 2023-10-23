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

namespace cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\QueryResult;

use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\ValueObject\PayPalTrackingCarrierId;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\CarrierId;
use PrestaShop\PrestaShop\Core\Domain\Country\ValueObject\CountryId;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EditablePayPalCarrierTracking
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
     * @var string
     */
    private $paypalEnum;
    /**
     * @var bool
     */
    private $worldwide;

    public function __construct(
        PayPalTrackingCarrierId $payPalTrackingCarrierId,
        CarrierId $carrierId,
        CountryId $countryId,
        string $paypalEnum,
        bool $worldwide
    ) {
        $this->payPalTrackingCarrierId = $payPalTrackingCarrierId;
        $this->carrierId = $carrierId;
        $this->countryId = $countryId;
        $this->paypalEnum = $paypalEnum;
        $this->worldwide = $worldwide;
    }

    public function getPayPalTrackingCarrierId(): PayPalTrackingCarrierId
    {
        return $this->payPalTrackingCarrierId;
    }

    public function getCarrierId(): CarrierId
    {
        return $this->carrierId;
    }

    public function getCountryId(): CountryId
    {
        return $this->countryId;
    }

    public function getCarrierName(): string
    {
        $carrier = new \Carrier($this->carrierId->getValue());

        return $carrier->name;
    }

    public function getCountryName(): string
    {
        return \Country::getNameById(\Context::getContext()->language->id, $this->countryId->getValue());
    }

    /**
     * @return string
     */
    public function getPaypalEnum(): string
    {
        return $this->paypalEnum;
    }

    public function isWorldwide(): bool
    {
        return $this->worldwide;
    }

}
