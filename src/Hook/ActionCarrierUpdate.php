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

namespace cdigruttola\PaypalTracking\Hook;

use cdigruttola\PaypalTracking\Entity\PaypalCarrierTracking;
use cdigruttola\PaypalTracking\Form\DataConfiguration\PaypalTrackingConfigurationData;
use cdigruttola\PaypalTracking\Repository\PaypalCarrierTrackingRepository;
use cdigruttola\PaypalTracking\Service\Admin\AdminPayPalTrackingService;
use Doctrine\ORM\EntityManagerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ActionCarrierUpdate extends AbstractHook
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(\Module $module,
        \Context $context,
        PaypalCarrierTrackingRepository $repository,
        AdminPayPalTrackingService $service,
        EntityManagerInterface $em)
    {
        parent::__construct($module, $context, $repository, $service);
        $this->em = $em;
    }

    public function execute(array $params)
    {
        $id_carrier_old = (int) $params['id_carrier'];
        $id_carrier_new = (int) $params['carrier']->id;

        /** @var PaypalCarrierTracking[] $paypalCarrierTrackings */
        $paypalCarrierTrackings = $this->repository->findBy(['id_carrier' => $id_carrier_old]);

        if (empty($paypalCarrierTrackings)) {
            \PrestaShopLogger::addLog('#PayPalTracking# Entities not found for carrier_id ' . $id_carrier_old);

            return;
        }

        foreach ($paypalCarrierTrackings as $paypalCarrierTracking) {
            try {
                $paypalCarrierTracking->setIdCarrier($id_carrier_new);

                $this->em->persist($paypalCarrierTracking);
            } catch (\Exception $e) {
                \PrestaShopLogger::addLog("#PayPalTracking# Error during update of $id_carrier_old to $id_carrier_new");
                $id_shop = $this->context->shop->id;
                if (\Configuration::get(PaypalTrackingConfigurationData::PAYPAL_TRACKING_DEBUG, null, null, $id_shop)) {
                    \PrestaShopLogger::addLog('#PayPalTracking# Error during update - ' . $e->getMessage() . '. Exception Class ' . get_class($e) . '. Trace ' . $e->getTraceAsString());
                }
            }
        }

        $this->em->flush();
    }
}
