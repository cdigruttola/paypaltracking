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

namespace cdigruttola\PaypalTracking\Form\DataProvider;

use cdigruttola\PaypalTracking\Entity\PaypalCarrierTracking;
use cdigruttola\PaypalTracking\Form\PayPalCarrierTrackingType;
use Doctrine\ORM\EntityRepository;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataProvider\FormDataProviderInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class PayPalCarrierTrackingFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(
        EntityRepository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($id)
    {
        /** @var PaypalCarrierTracking $entity */
        $entity = $this->repository->findOneBy(['id' => (int) $id]);

        $optVal = $this->getIndexByEnum($entity->getPaypalCarrierEnum());

        return [
            'carrierId' => $entity->getIdCarrier(),
            'countryId' => $entity->getIdCountry(),
            'paypalCarrierEnum' => $optVal,
            'worldwide' => $entity->isWorldwide(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultData()
    {
        $data = [
            'is_enabled' => true,
        ];

        return $data;
    }

    private function getIndexByEnum(string $paypalEnum)
    {
        $i = -1;

        foreach (PayPalCarrierTrackingType::PAYPAL_CARRIERS as $nation => $values) {
            foreach ($values as $key => $val) {
                ++$i;
                if ($val == $paypalEnum) {
                    return $val;
                }
            }
        }

        return $i;
    }
}
