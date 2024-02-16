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

namespace cdigruttola\PaypalTracking\Entity;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="cdigruttola\PaypalTracking\Repository\PaypalCarrierTrackingRepository")
 *
 * @ORM\Table()
 */
class PaypalCarrierTracking
{
    /**
     * @var int
     *
     * @ORM\Id
     *
     * @ORM\Column(name="id_paypal_carrier_tracking", type="integer")
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_carrier", type="integer")
     */
    private $idCarrier;

    /**
     * @var int
     *
     * @ORM\Column(name="id_country", type="integer")
     */
    private $idCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="paypal_carrier_enum", type="string")
     */
    private $paypalCarrierEnum;

    /**
     * @var bool
     *
     * @ORM\Column(name="worldwide", type="boolean")
     */
    private $worldwide;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_add", type="datetime")
     */
    private $dateAdd;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_upd", type="datetime")
     */
    private $dateUpd;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): PaypalCarrierTracking
    {
        $this->id = $id;

        return $this;
    }

    public function getIdCarrier(): int
    {
        return $this->idCarrier;
    }

    public function setIdCarrier(int $idCarrier): PaypalCarrierTracking
    {
        $this->idCarrier = $idCarrier;

        return $this;
    }

    public function getIdCountry(): int
    {
        return $this->idCountry;
    }

    public function setIdCountry(int $idCountry): PaypalCarrierTracking
    {
        $this->idCountry = $idCountry;

        return $this;
    }

    public function getPaypalCarrierEnum(): string
    {
        return $this->paypalCarrierEnum;
    }

    public function setPaypalCarrierEnum(string $paypalCarrierEnum): PaypalCarrierTracking
    {
        $this->paypalCarrierEnum = $paypalCarrierEnum;

        return $this;
    }

    public function isWorldwide(): bool
    {
        return $this->worldwide;
    }

    public function setWorldwide(bool $worldwide): PaypalCarrierTracking
    {
        $this->worldwide = $worldwide;

        return $this;
    }

    public function getDateAdd(): \DateTime
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTime $dateAdd): PaypalCarrierTracking
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function getDateUpd(): \DateTime
    {
        return $this->dateUpd;
    }

    public function setDateUpd(\DateTime $dateUpd): PaypalCarrierTracking
    {
        $this->dateUpd = $dateUpd;

        return $this;
    }
}
