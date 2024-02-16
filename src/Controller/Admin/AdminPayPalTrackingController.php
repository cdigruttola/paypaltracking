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
use GuzzleHttp\Exception\GuzzleException;
use PrestaShop\PrestaShop\Adapter\Country\Repository\CountryRepository;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPayPalTrackingController extends FrameworkBundleAdminController
{
    const ADMIN_PAYPAL_TRACKING = 'admin_paypal_tracking';

    /** @var array */
    private $languages;
    /** @var \Module */
    private $module;

    public function __construct($languages, $module)
    {
        $this->languages = $languages;
        $this->module = $module;
    }

    public function indexConfiguration(): Response
    {
        $configurationForm = $this->get('cdigruttola.paypaltracking.form.configuration_type.form_handler')->getForm();

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
    public function saveConfiguration(Request $request): Response
    {
        $redirectResponse = $this->redirectToRoute('admin_paypal_tracking_controller');

        $form = $this->get('cdigruttola.paypaltracking.form.configuration_type.form_handler')->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $redirectResponse;
        }

        if ($form->isValid()) {
            $data = $form->getData();
            $saveErrors = $this->get('cdigruttola.paypaltracking.form.configuration_type.form_handler')->save($data);

            if (0 === count($saveErrors)) {
                $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));

                return $redirectResponse;
            }
        }

        $formErrors = [];

        foreach ($form->getErrors(true) as $error) {
            $formErrors[] = $error->getMessage();
        }

        $this->flashErrors($formErrors);

        return $redirectResponse;
    }

    /**
     * @param Request $request
     * @param PayPalCarrierTrackingFilters $filters
     *
     * @return Response
     *
     * @AdminSecurity("is_granted(['read'], request.get('_legacy_controller'))", message="Access denied.")
     */
    public function indexAction(Request $request, PayPalCarrierTrackingFilters $filters)
    {
        $gridFactory = $this->get('cdigruttola.paypaltracking.core.grid.factory.paypal_carrier_tracking');
        $grid = $gridFactory->getGrid($filters);

        return $this->render('@Modules/paypaltracking/views/templates/admin/index.html.twig', [
            'grid' => $this->presentGrid($grid),
            'help_link' => false,
        ]);
    }

    /**
     * Show create form & handle processing of it.
     *
     * @AdminSecurity("is_granted(['create'], request.get('_legacy_controller'))", message="Access denied.")
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $form = $this->get('cdigruttola.paypaltracking.core.form.identifiable_object.builder.paypal_carrier_tracking_form_builder')->getForm();
        $form->handleRequest($request);

        $formHandler = $this->get('cdigruttola.paypaltracking.core.form.identifiable_object.handler.paypal_carrier_tracking_form_handler');

        try {
            $result = $formHandler->handle($form);

            if (null !== $result->getIdentifiableObjectId()) {
                $this->addFlash('success', $this->trans('Successful creation.', 'Admin.Notifications.Success'));

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
     * @AdminSecurity("is_granted(['update'], request.get('_legacy_controller'))", message="Access denied.")
     *
     * @return Response
     */
    public function editAction(int $carrierId, Request $request)
    {
        $form = $this->get('cdigruttola.paypaltracking.core.form.identifiable_object.builder.paypal_carrier_tracking_form_builder')->getFormFor($carrierId);
        $form->handleRequest($request);

        $formHandler = $this->get('cdigruttola.paypaltracking.core.form.identifiable_object.handler.paypal_carrier_tracking_form_handler');

        try {
            $result = $formHandler->handleFor($carrierId, $form);

            if ($result->isSubmitted()) {
                if ($result->isValid()) {
                    $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));
                } else {
                    $this->addFlashFormErrors($form);
                }

                return $this->redirectToRoute(self::ADMIN_PAYPAL_TRACKING);
            }
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        /** @var PaypalCarrierTracking $entity */
        $entity = $this->getDoctrine()
            ->getRepository(PaypalCarrierTracking::class)
            ->find($carrierId);

        $country = new \Country($entity->getIdCountry());
        $carrier = new \Carrier($entity->getIdCarrier());

        $id_lang = \Context::getContext()->language->id;

        return $this->render('@Modules/paypaltracking/views/templates/admin/paypalcarrier/edit.html.twig', [
            'form' => $form->createView(),
            'help_link' => false,
            'title' => $this->trans('Edit: %name% and country %country%', 'Modules.Paypaltracking.Admin',
                ['%name%' => $carrier->name, '%country%' => $country->name[$id_lang] ]),
        ]);
    }

    /**
     * @AdminSecurity("is_granted('delete', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param int $carrierId
     *
     * @return RedirectResponse
     */
    public function deleteAction($carrierId)
    {
        $entity = $this->getDoctrine()
            ->getRepository(PaypalCarrierTracking::class)
            ->find($carrierId);


        if (!empty($entity)) {
            $entityManager = $this->get('doctrine.orm.entity_manager');

            $entityManager->remove($entity);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $this->trans('Successful deletion.', 'Admin.Notifications.Success')
            );

            return $this->redirectToRoute(self::ADMIN_PAYPAL_TRACKING);
        }

        $this->addFlash(
            'error',
            $this->trans('Cannot find entity %d', 'Modules.Paypaltracking.Admin' , ['%d' => $carrierId])
        );

        return $this->redirectToRoute(self::ADMIN_PAYPAL_TRACKING);
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function updateBatchOrdersAction(Request $request)
    {
        $redirectResponse = $this->redirectToRoute('admin_paypal_tracking_controller');

        try {
            $dateFrom = $request->get('paypal_tracking_update_batch')['update_order_from'];
            $dateTo = $request->get('paypal_tracking_update_batch')['update_order_to'];

            if (empty($dateFrom) || empty($dateTo)) {
                throw new \RangeException($this->trans('The selected date range is not valid. Date must be both set.', 'Modules.Paypaltracking.Configure'));
            }
            if ($dateFrom > $dateTo) {
                throw new \RangeException($this->trans('The selected date range is not valid. Date to must be greater than date from.', 'Modules.Paypaltracking.Configure'));
            }

            /** @var AdminPayPalTrackingService $service */
            $service = $this->get('cdigruttola.paypaltracking.service.paypal_carrier_tracking');
            if ($service->updateBatchOrders($dateFrom, $dateTo)) {
                $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));
            }
        } catch (GuzzleException|\Exception $ex) {
            \PrestaShopLogger::addLog('#PayPalTracking# ' . $ex->getMessage());
            $this->addFlash('error', $this->trans('See logs.', 'Modules.Paypaltracking.Configure'));
        }

        return $redirectResponse;
    }

    /**
     * @AdminSecurity("is_granted('update', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param int $carrierId
     *
     * @return RedirectResponse
     */
    public function toggleWorldwideAction(int $carrierId): RedirectResponse
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        /** @var PaypalCarrierTracking $entity */
        $entity = $entityManager
            ->getRepository(PaypalCarrierTracking::class)
            ->findOneBy(['id' => $carrierId]);

        if (empty($entity)) {
            $response = [
                'status' => false,
                'message' => sprintf('Entity %d doesn\'t exist', $carrierId),
            ];
            $errors = [$response];
            $this->flashErrors($errors);

            return $this->redirectToRoute(self::ADMIN_PAYPAL_TRACKING);
        }

        try {
            $entity->setWorldwide(!$entity->isWorldwide());
            $entityManager->flush();

            $this->addFlash('success', $this->trans('The status has been successfully updated.', 'Admin.Notifications.Success'));
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => sprintf(
                    'There was an error while updating the status of worldwide %d: %s',
                    $carrierId,
                    $e->getMessage()
                ),
            ];
            $errors = [$response];
            $this->flashErrors($errors);
        }

        return $this->redirectToRoute(self::ADMIN_PAYPAL_TRACKING);

    }
}
