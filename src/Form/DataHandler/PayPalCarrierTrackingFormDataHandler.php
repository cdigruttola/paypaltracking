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

namespace cdigruttola\Module\PaypalTracking\Form\DataHandler;

use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Command\AddPayPalCarrierTrackingCommand;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Command\EditPayPalCarrierTrackingCommand;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\CarrierId;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataHandler\FormDataHandlerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Saves or updates data submitted in form
 */
final class PayPalCarrierTrackingFormDataHandler implements FormDataHandlerInterface
{
    /**
     * @var CommandBusInterface
     */
    private $bus;

    public function __construct(
        CommandBusInterface $bus
    ) {
        $this->bus = $bus;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        $command = $this->buildPayPalCarrierTrackingAddCommandFromFormData($data);

        /** @var CarrierId $carrierId */
        $carrierId = $this->bus->handle($command);

        return $carrierId->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function update($paypalCarrierTrackingId, array $data)
    {
        $command = $this->buildPayPalCarrierTrackingEditCommand($paypalCarrierTrackingId, $data);

        $this->bus->handle($command);
    }

    /**
     * @return AddPayPalCarrierTrackingCommand
     */
    private function buildPayPalCarrierTrackingAddCommandFromFormData(array $data)
    {
        return new AddPayPalCarrierTrackingCommand(
            (int) $data['carrierId'],
            (int) $data['countryId'],
            $data['paypalCarrierEnum']
        );
    }

    /**
     * @param int $paypalCarrierTrackingId
     *
     * @return EditPayPalCarrierTrackingCommand
     */
    private function buildPayPalCarrierTrackingEditCommand($paypalCarrierTrackingId, array $data)
    {
        return new EditPayPalCarrierTrackingCommand($paypalCarrierTrackingId, (int) $data['carrierId'], (int) $data['countryId'], $data['paypalCarrierEnum']);
    }
}
