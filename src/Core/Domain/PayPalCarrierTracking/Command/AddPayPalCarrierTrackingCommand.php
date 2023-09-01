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

class AddPayPalCarrierTrackingCommand
{
    /**
     * @var int
     */
    private int $carrierId;
    /**
     * @var int
     */
    private int $countryId;
    /**
     * @var string
     */
    private string $paypalCarrierEnum;

    public function __construct(
        int $carrierId,
        int $countryId,
        string $paypalCarrierEnum
    ) {
        $this->setCarrierId($carrierId);
        $this->setCountryId($countryId);
        $this->setPaypalCarrierEnum($paypalCarrierEnum);
    }

    /**
     * @return int
     */
    public function getCarrierId(): int
    {
        return $this->carrierId;
    }

    /**
     * @param int $carrierId
     *
     * @return AddPayPalCarrierTrackingCommand
     */
    public function setCarrierId(int $carrierId): AddPayPalCarrierTrackingCommand
    {
        $this->carrierId = $carrierId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCountryId(): int
    {
        return $this->countryId;
    }

    /**
     * @param int $countryId
     *
     * @return AddPayPalCarrierTrackingCommand
     */
    public function setCountryId(int $countryId): AddPayPalCarrierTrackingCommand
    {
        $this->countryId = $countryId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaypalCarrierEnum(): string
    {
        return $this->paypalCarrierEnum;
    }

    /**
     * @param string $paypalCarrierEnum
     *
     * @return AddPayPalCarrierTrackingCommand
     */
    public function setPaypalCarrierEnum(string $paypalCarrierEnum): AddPayPalCarrierTrackingCommand
    {
        $this->paypalCarrierEnum = $paypalCarrierEnum;

        return $this;
    }
}
