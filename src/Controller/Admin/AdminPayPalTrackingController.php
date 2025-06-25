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

namespace cdigruttola\PaypalTracking\Controller\Admin;

use cdigruttola\PaypalTracking\Core\Search\Filters\PayPalCarrierTrackingFilters;
use cdigruttola\PaypalTracking\Entity\PaypalCarrierTracking;
use cdigruttola\PaypalTracking\Form\PaypalTrackingUpdateBatchType;
use cdigruttola\PaypalTracking\Service\Admin\AdminPayPalTrackingService;
use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\PrestaShop\Core\Context\LanguageContext;
use PrestaShop\PrestaShop\Core\Form\Handler;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Builder\FormBuilderInterface;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Handler\FormHandler;
use PrestaShop\PrestaShop\Core\Grid\GridFactory;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPayPalTrackingController extends PrestaShopAdminController
{
    const ADMIN_PAYPAL_TRACKING = 'admin_paypal_tracking';

    /** @var \Paypaltracking */
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function indexConfiguration(
        #[Autowire(service: 'cdigruttola.digruttolacustomization.form.configuration_type.form_handler')]
        Handler $formHandler,
    ): Response {
        $configurationForm = $formHandler->getForm();

        return $this->render('@Modules/paypaltracking/views/templates/admin/index_config.html.twig', [
            'form' => $configurationForm->createView(),
            'update_form' => $this->createForm(
                PaypalTrackingUpdateBatchType::class, null,
                ['action' => $this->generateUrl('admin_paypal_tracking_update_batch_orders')]
            )->createView(),
            'module_dir' => _MODULE_DIR_ . $this->module->name . '/',
            'help_link' => false,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function saveConfiguration(
        Request $request,
        #[Autowire(service: 'cdigruttola.digruttolacustomization.form.configuration_type.form_handler')]
        Handler $formHandler,
    ): Response {
        $redirectResponse = $this->redirectToRoute('admin_paypal_tracking_controller');

        $form = $formHandler->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $redirectResponse;
        }

        if ($form->isValid()) {
            $data = $form->getData();
            $saveErrors = $formHandler->save($data);

            if (0 === count($saveErrors)) {
                $this->addFlash('success', $this->trans('Successful update.', [], 'Admin.Notifications.Success'));

                return $redirectResponse;
            }
        }

        $formErrors = [];

        foreach ($form->getErrors(true) as $error) {
            $formErrors[] = $error->getMessage();
        }

        $this->addFlashErrors($formErrors);

        return $redirectResponse;
    }

    /**
     * @param PayPalCarrierTrackingFilters $filters
     *
     * @return Response
     *
     * #[AdminSecurity("is_granted(['read'], request.get('_legacy_controller'))", message="Access denied.")
     */
    public function indexAction(
        PayPalCarrierTrackingFilters $filters,
        #[Autowire(service: 'cdigruttola.paypaltracking.core.grid.factory.paypal_carrier_tracking')]
        GridFactory $gridFactory,
    ): Response {
        $grid = $gridFactory->getGrid($filters);

        return $this->render('@Modules/paypaltracking/views/templates/admin/index.html.twig', [
            'grid' => $this->presentGrid($grid),
            'help_link' => false,
        ]);
    }

    /**
     * Show create form & handle processing of it.
     *
     * #[AdminSecurity("is_granted(['create'], request.get('_legacy_controller'))", message="Access denied.")
     *
     * @return Response
     */
    public function createAction(
        Request $request,
        #[Autowire(service: 'cdigruttola.paypaltracking.core.form.identifiable_object.builder.paypal_carrier_tracking_form_builder')]
        FormBuilderInterface $formDataHandler,
        #[Autowire(service: 'cdigruttola.paypaltracking.core.form.identifiable_object.handler.paypal_carrier_tracking_form_handler')]
        FormHandler $formHandler,
    ): Response {
        $form = $formDataHandler->getForm();
        $form->handleRequest($request);

        try {
            $result = $formHandler->handle($form);

            if (null !== $result->getIdentifiableObjectId()) {
                $this->addFlash('success', $this->trans('Successful creation.', [], 'Admin.Notifications.Success'));

                return $this->redirectToRoute(self::ADMIN_PAYPAL_TRACKING);
            }
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('@Modules/paypaltracking/views/templates/admin/paypalcarrier/create.html.twig', [
            'form' => $form->createView(),
            'help_link' => false,
        ]);
    }

    /**
     * #[AdminSecurity("is_granted(['update'], request.get('_legacy_controller'))", message="Access denied.")
     *
     * @return Response
     */
    public function editAction(
        int $carrierId,
        Request $request,
        #[Autowire(service: 'cdigruttola.paypaltracking.core.form.identifiable_object.builder.paypal_carrier_tracking_form_builder')]
        FormBuilderInterface $formDataHandler,
        #[Autowire(service: 'cdigruttola.paypaltracking.core.form.identifiable_object.handler.paypal_carrier_tracking_form_handler')]
        FormHandler $formHandler,
    ): Response {
        $form = $formDataHandler->getFormFor($carrierId);
        $form->handleRequest($request);

        try {
            $result = $formHandler->handleFor($carrierId, $form);

            if ($result->isSubmitted()) {
                if ($result->isValid()) {
                    $this->addFlash('success', $this->trans('Successful update.', [], 'Admin.Notifications.Success'));
                } else {
                    $this->addFlashFormErrors($form);
                }

                return $this->redirectToRoute(self::ADMIN_PAYPAL_TRACKING);
            }
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        /** @var PaypalCarrierTracking $entity */
        $entity = $this->container->get(EntityManagerInterface::class)
            ->getRepository(PaypalCarrierTracking::class)
            ->find($carrierId);

        $country = new \Country($entity->getIdCountry());
        $carrier = new \Carrier($entity->getIdCarrier());

        $id_lang = $this->container->get(LanguageContext::class)->getId();

        return $this->render('@Modules/paypaltracking/views/templates/admin/paypalcarrier/edit.html.twig', [
            'form' => $form->createView(),
            'help_link' => false,
            'title' => $this->trans('Edit: %name% and country %country%', ['%name%' => $carrier->name, '%country%' => $country->name[$id_lang]], 'Modules.Paypaltracking.Admin'),
        ]);
    }

    /**
     * #[AdminSecurity("is_granted('delete', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param int $carrierId
     *
     * @return RedirectResponse
     */
    public function deleteAction($carrierId)
    {
        $entity = $this->container->get(EntityManagerInterface::class)
            ->getRepository(PaypalCarrierTracking::class)
            ->find($carrierId);

        if (!empty($entity)) {
            $entityManager = $this->container->get(EntityManagerInterface::class);

            $entityManager->remove($entity);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $this->trans('Successful deletion.', [], 'Admin.Notifications.Success')
            );

            return $this->redirectToRoute(self::ADMIN_PAYPAL_TRACKING);
        }

        $this->addFlash(
            'error',
            $this->trans('Cannot find entity %d', ['%d' => $carrierId], 'Modules.Paypaltracking.Admin')
        );

        return $this->redirectToRoute(self::ADMIN_PAYPAL_TRACKING);
    }

    /**
     * #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function updateBatchOrdersAction(
        Request $request,
        #[Autowire(service: 'cdigruttola.paypaltracking.service.paypal_carrier_tracking')]
        AdminPayPalTrackingService $service
    ): Response {
        $redirectResponse = $this->redirectToRoute('admin_paypal_tracking_controller');

        try {
            $dateFrom = $request->get('paypal_tracking_update_batch')['update_order_from'];
            $dateTo = $request->get('paypal_tracking_update_batch')['update_order_to'];

            if (empty($dateFrom) || empty($dateTo)) {
                throw new \RangeException($this->trans('The selected date range is not valid. Date must be both set.', [], 'Modules.Paypaltracking.Configure'));
            }
            if ($dateFrom > $dateTo) {
                throw new \RangeException($this->trans('The selected date range is not valid. Date to must be greater than date from.', [], 'Modules.Paypaltracking.Configure'));
            }

            if ($service->updateBatchOrders($dateFrom, $dateTo)) {
                $this->addFlash('success', $this->trans('Successful update.', [], 'Admin.Notifications.Success'));
            }
        } catch (\Exception $ex) {
            \PrestaShopLogger::addLog('#PayPalTracking# ' . $ex->getMessage());
            $this->addFlash('error', $this->trans('See logs.', [], 'Modules.Paypaltracking.Configure'));
        }

        return $redirectResponse;
    }

    /**
     * #[AdminSecurity("is_granted('update', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param int $carrierId
     *
     * @return RedirectResponse
     */
    public function toggleWorldwideAction(int $carrierId): RedirectResponse
    {
        $entityManager = $this->container->get(EntityManagerInterface::class);
        /** @var PaypalCarrierTracking $entity */
        $entity = $entityManager
            ->getRepository(PaypalCarrierTracking::class)
            ->findOneBy(['id' => $carrierId]);

        if ($entity == null) {
            $errors = [$this->trans('Entity %d doesn\'t exist', [$carrierId], 'Modules.Paypaltracking.Admin')];
            $this->addFlashErrors($errors);

            return $this->redirectToRoute(self::ADMIN_PAYPAL_TRACKING);
        }

        try {
            $entity->setWorldwide(!$entity->isWorldwide());
            $entityManager->flush();

            $this->addFlash('success', $this->trans('The status has been successfully updated.', [], 'Admin.Notifications.Success'));
        } catch (\Exception $e) {
            $errors = [$this->trans('There was an error while updating the status of worldwide %d: %s', [$carrierId, $e->getMessage()], 'Modules.Paypaltracking.Admin')];
            $this->addFlashErrors($errors);
        }

        return $this->redirectToRoute(self::ADMIN_PAYPAL_TRACKING);
    }

    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
                EntityManagerInterface::class => EntityManagerInterface::class,
                LanguageContext::class => LanguageContext::class,
            ];
    }
}
