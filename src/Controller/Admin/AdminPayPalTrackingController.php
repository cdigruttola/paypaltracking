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

namespace cdigruttola\Module\PaypalTracking\Controller\Admin;

use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Exception\MissingPayPalCarrierTrackingRequiredFieldsException;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Exception\PayPalCarrierTrackingException;
use cdigruttola\Module\PaypalTracking\Core\Domain\PayPalCarrierTracking\Query\GetPayPalCarrierTrackingForEditing;
use cdigruttola\Module\PaypalTracking\Core\Search\Filters\PayPalCarrierTrackingFilters;
use cdigruttola\Module\PaypalTracking\Service\Admin\AdminPayPalTrackingService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Order;
use PayPalCarrierTracking;
use phpDocumentor\Reflection\Types\This;
use PrestaShop\PrestaShop\Core\Domain\Carrier\Exception\CarrierNotFoundException;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopException;
use PrestaShopLogger;
use RangeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tools;

class AdminPayPalTrackingController extends FrameworkBundleAdminController
{
    const ADMIN_PAYPAL_TRACKING = 'admin_paypal_tracking';

    /**
     * @param Request $request
     * @param PayPalCarrierTrackingFilters $filters
     *
     * @return Response
     * @AdminSecurity("is_granted(['read'], request.get('_legacy_controller'))", message="Access denied.")
     */
    public function indexAction(Request $request, PayPalCarrierTrackingFilters $filters)
    {
        $legacyController = $request->attributes->get('_legacy_controller');

        $gridFactory = $this->get('cdigruttola.module.paypaltracking.core.grid.factory.paypal_carrier_tracking');
        $grid = $gridFactory->getGrid($filters);

        return $this->render('@Modules/paypaltracking/views/templates/admin/index.html.twig', [
            'grid' => $this->presentGrid($grid),
            'help_link' => $this->generateSidebarLink($legacyController),
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
        $form = $this->get('cdigruttola.module.paypaltracking.core.form.identifiable_object.builder.paypal_carrier_tracking_form_builder')->getForm();
        $form->handleRequest($request);

        $formHandler = $this->get('cdigruttola.module.paypaltracking.core.form.identifiable_object.handler.paypal_carrier_tracking_form_handler');

        try {
            $result = $formHandler->handle($form);

            if ($orderStateId = $result->getIdentifiableObjectId()) {
                $this->addFlash('success', $this->trans('Successful creation.', 'Admin.Notifications.Success'));

                return $this->redirectToRoute(self::ADMIN_PAYPAL_TRACKING);
            }
        } catch (Exception $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
        }

        return $this->render('@Modules/paypaltracking/views/templates/admin/paypalcarrier/create.html.twig', [
            'form' => $form->createView(),
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
            'contextLangId' => $this->getContextLangId(),
            'templatesPreviewUrl' => _MAIL_DIR_,
            'languages' => array_map(
                function (array $language) {
                    return [
                        'id' => $language['iso_code'],
                        'value' => sprintf('%s - %s', $language['iso_code'], $language['name']), ];
                },
                $this->get('prestashop.adapter.legacy.context')->getLanguages()
            ),
        ]);
    }

    /**
     * @AdminSecurity("is_granted(['update'], request.get('_legacy_controller'))", message="Access denied.")
     *
     * @return Response
     */
    public function editAction(int $carrierId, Request $request)
    {
        $form = $this->get('cdigruttola.module.paypaltracking.core.form.identifiable_object.builder.paypal_carrier_tracking_form_builder')->getFormFor($carrierId);
        $form->handleRequest($request);

        $formHandler = $this->get('cdigruttola.module.paypaltracking.core.form.identifiable_object.handler.paypal_carrier_tracking_form_handler');

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
        } catch (PayPalCarrierTrackingException $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
        }

        return $this->render('@Modules/paypaltracking/views/templates/admin/paypalcarrier/edit.html.twig', [
            'form' => $form->createView(),
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
            'editable' => $this->getQueryBus()->handle(new GetPayPalCarrierTrackingForEditing((int) $carrierId)),
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
        $payPalCarrierTracking = new PayPalCarrierTracking($carrierId);
        $errors = [];

        if (!$payPalCarrierTracking->delete()) {
            $errors[] = ['key' => 'Could not delete %i%',
                'domain' => 'Modules.Paypaltracking.Admin',
                'parameters' => ['%i%' => $carrierId], ];
        }

        if (0 === count($errors)) {
            $this->addFlash('success', $this->trans('Successful deletion.', 'Admin.Notifications.Success'));
        } else {
            $this->flashErrors($errors);
        }
        unset($payPalCarrierTracking);

        return $this->redirectToRoute(self::ADMIN_PAYPAL_TRACKING);
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     * @throws PrestaShopException
     * @throws Exception
     */
    public function updateBatchOrdersAction(Request $request)
    {
        $res = false;
        $errorMessage = '';
        try {
            $dateFrom = Tools::getValue('update_order_from');
            $dateTo = Tools::getValue('update_order_to');

            if (empty($dateFrom) || empty($dateTo)) {
                throw new RangeException($this->trans('The selected date range is not valid. Date must be both set.' , 'Modules.Paypaltracking.Configure'));
            }
            if ($dateFrom > $dateTo) {
                throw new RangeException($this->trans('The selected date range is not valid. Date to must be greater than date from.' , 'Modules.Paypaltracking.Configure'));
            }

            /** @var AdminPayPalTrackingService $service */
            $service = $this->get('cdigruttola.module.paypaltracking.service.paypal_carrier_tracking');
            if ($service->updateBatchOrders($dateFrom, $dateTo)) {
                $res = true;
                $errorMessage = $this->trans('See logs.', 'Modules.Paypaltracking.Configure');
            }
        } catch (GuzzleException|Exception $ex) {
            PrestaShopLogger::addLog($ex->getMessage());
            $errorMessage = $ex->getMessage();
        }

        return $this->redirect(Tools::getValue('redirect') . $res . '&errorMessage=' . $errorMessage);
    }

    /**
     * Get errors that can be used to translate exceptions into user friendly messages
     *
     * @return array
     */
    private function getErrorMessages(Exception $e)
    {
        return [
            CarrierNotFoundException::class => $this->trans(
                'This carrier does not exist.',
                'Modules.Paypaltracking.Admin'
            ),
            MissingPayPalCarrierTrackingRequiredFieldsException::class => $this->trans(
                'The %s field is required.',
                'Admin.Notifications.Error',
                [
                    implode(
                        ',',
                        $e instanceof MissingPayPalCarrierTrackingRequiredFieldsException ? $e->getMissingRequiredFields() : []
                    ),
                ]
            ),
        ];
    }
}
