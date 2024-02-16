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

namespace cdigruttola\PaypalTracking\Form\DataHandler;

use cdigruttola\PaypalTracking\Entity\PaypalCarrierTracking;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityRepository $entityRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->entityRepository = $entityRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        $entity = new PaypalCarrierTracking();
        $entity->setIdCarrier((int) $data['carrierId']);
        $entity->setIdCountry((int) $data['countryId']);
        $entity->setWorldwide((bool) $data['worldwide']);
        $entity->setPaypalCarrierEnum($data['paypalCarrierEnum']);
        $entity->setDateAdd(new \DateTime());
        $entity->setDateUpd(new \DateTime());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data)
    {
        /** @var PaypalCarrierTracking $entity */
        $entity = $this->entityRepository->find($id);

        $entity->setIdCarrier((int) $data['carrierId']);
        $entity->setIdCountry((int) $data['countryId']);
        $entity->setWorldwide((bool) $data['worldwide']);
        $entity->setPaypalCarrierEnum($data['paypalCarrierEnum']);
        $entity->setDateUpd(new \DateTime());

        $this->entityManager->flush();

        return $entity->getId();
    }

}
