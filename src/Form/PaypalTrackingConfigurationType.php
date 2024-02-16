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

namespace cdigruttola\PaypalTracking\Form;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaypalTrackingConfigurationType extends TranslatorAwareType
{
    /** @var array */
    private $paymentModules;

    public function __construct(TranslatorInterface $translator, array $locales, array $paymentModules)
    {
        parent::__construct($translator, $locales);
        $this->paymentModules = $paymentModules;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('api_live_mode', SwitchType::class, [
                'required' => true,
                'label' => $this->trans('PayPal Live Mode', 'Modules.Paypaltracking.Configure'),
                'help' => $this->trans('This options set if you using SandBox or Live mode.', 'Modules.Paypaltracking.Configure'),
            ])
            ->add('debug', SwitchType::class, [
                'required' => true,
                'label' => $this->trans('Debug Mode', 'Modules.Paypaltracking.Configure'),
                'help' => $this->trans('This options set if you want to enable more logs in case of error.', 'Modules.Paypaltracking.Configure'),
            ])
            ->add('api_client_id', TextType::class, [
                'required' => false,
                'label' => $this->trans('PayPal API Client ID', 'Modules.Paypaltracking.Configure'),
                'help' => $this->trans('Enter PayPal API Client ID', 'Modules.Paypaltracking.Configure'),
            ])
            ->add('api_client_secret', PasswordType::class, [
                'required' => true,
                'label' => $this->trans('PayPal API Client Secret', 'Modules.Paypaltracking.Configure'),
                'help' => $this->trans('Enter PayPal API Client Secret', 'Modules.Paypaltracking.Configure'),
            ])
            ->add('modules', ChoiceType::class, [
                'required' => true,
                'multiple' => true,
                'label' => $this->trans('Please select modules that use PayPal', 'Modules.Paypaltracking.Configure'),
                'choices' => $this->paymentModules,
            ]);
    }
}
