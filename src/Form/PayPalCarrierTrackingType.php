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

namespace cdigruttola\PaypalTracking\Form;

use PrestaShopBundle\Form\Admin\Type\CountryChoiceType;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PayPalCarrierTrackingType extends TranslatorAwareType
{
    public const PAYPAL_CARRIERS = [
        'GLOBAL' => [
            'Aramex' => 'ARAMEX',
            'B2C Europe' => 'B_TWO_C_EUROPE',
            'CJ Logistics' => 'CJ_LOGISTICS',
            'Correos Express' => 'CORREOS_EXPRESS',
            'DHL Active Tracing' => 'DHL_ACTIVE_TRACING',
            'DHL Benelux' => 'DHL_BENELUX',
            'DHL ecCommerce US' => 'DHL_GLOBAL_MAIL',
            'DHL eCommerce Asia' => 'DHL_GLOBAL_MAIL_ASIA',
            'DHL Express' => 'DHL',
            'DHL Global eCommerce' => 'DHL_GLOBAL_ECOMMERCE',
            'DHL Packet' => 'DHL_PACKET',
            'DPD Global' => 'DPD',
            'DPD Local' => 'DPD_LOCAL',
            'DPD Local Reference' => 'DPD_LOCAL_REF',
            'DPE Express' => 'DPE_EXPRESS',
            'DPEX Hong Kong' => 'DPEX',
            'DTDC Express Global' => 'DTDC_EXPRESS',
            'EShopWorld' => 'ESHOPWORLD',
            'FedEx' => 'FEDEX',
            'FLYT Express' => 'FLYT_EXPRESS',
            'GLS' => 'GLS',
            'IMX France' => 'IMX',
            'International SEUR' => 'INT_SUER',
            'Landmark Global' => 'LANDMARK_GLOBAL',
            'Matkahuoloto' => 'MATKAHUOLTO',
            'Omni Parcel' => 'OMNIPARCEL',
            'One World' => 'ONE_WORLD',
            'Other' => 'OTHER',
            'Posti' => 'POSTI',
            'Raben Group' => 'RABEN_GROUP',
            'SF EXPRESS' => 'SF_EXPRESS',
            'SkyNet Worldwide Express' => 'SKYNET_Worldwide',
            'Spreadel' => 'SPREADEL',
            'TNT Global' => 'TNT',
            'UPS' => 'UPS',
            'UPS Mail Innovations' => 'UPS_MI',
            'WebInterpret' => 'WEBINTERPRET',
        ],
        'AG' => [
            'Correos Antigua and Barbuda' => 'CORREOS_AG',
        ],
        'AR' => [
            'Emirates Post' => 'EMIRATES_POST',
            'OCA Argentina' => 'OCA_AR',
        ],
        'AU' => [
            'Adsone' => 'ADSONE',
            'Australia Post' => 'AUSTRALIA_POST',
            'Australia Toll' => 'TOLL_AU',
            'Bonds Couriers' => 'BONDS_COURIERS',
            'Couriers Please' => 'COURIERS_PLEASE',
            'DHL Australia' => 'DHL_AU',
            'DTDC Australia' => 'DTDC_AU',
            'Fastway Australia' => 'FASTWAY_AU',
            'Hunter Express' => 'HUNTER_EXPRESS',
            'Sendle' => 'SENDLE',
            'Star Track' => 'STARTRACK',
            'Star Track Express' => 'STARTRACK_EXPRESS',
            'TNT Australia' => 'TNT_AU',
            'Toll' => 'TOLL',
            'UBI Logistics' => 'UBI_LOGISTICS',
        ],
        'AT' => [
            'Austrian Post Express' => 'AUSTRIAN_POST_EXPRESS',
            'Austrian Post Registered' => 'AUSTRIAN_POST',
            'DHL Austria' => 'DHL_AT',
        ],
        'BE' => [
            'bpost' => 'BPOST',
            'bpost International' => 'BPOST_INT',
            'Mondial Belgium' => 'MONDIAL_BE',
            'TaxiPost' => 'TAXIPOST',
        ],
        'BR' => [
            'Correos Brazil' => 'CORREOS_BR',
            'Directlog' => 'DIRECTLOG_BR',
        ],
        'BG' => [
            'Bulgarian Post' => 'BULGARIAN_POST',
        ],
        'CA' => [
            'Canada Post' => 'CANADA_POST',
            'Canpar' => 'CANPAR',
            'Greyhound' => 'GREYHOUND',
            'Loomis' => 'LOOMIS',
            'Purolator' => 'PUROLATOR',
        ],
        'CL' => [
            'Correos Chile' => 'CORREOS_CL',
        ],
        'CN' => [
            '4PX Express' => 'FOUR_PX_EXPRESS',
            'AUPOST CHINA' => 'AUPOST_CN',
            'BQC Express' => 'BQC_EXPRESS',
            'Buylogic' => 'BUYLOGIC',
            'China Post' => 'CHINA_POST',
            'CN Exps' => 'CNEXPS',
            'EC China' => 'EC_CN',
            'EFS' => 'EFS',
            'EMPS China' => 'EMPS_CN',
            'EMS China' => 'EMS_CN',
            'Huahan Express' => 'HUAHAN_EXPRESS',
            'SFC Express' => 'SFC_EXPRESS',
            'TNT China' => 'TNT_CN',
            'WinIt' => 'WINIT',
            'Yanwen' => 'YANWEN_CN',
        ],
        'CR' => [
            'Correos De Costa Rica' => 'CORREOS_CR',
        ],
        'HR' => [
            'Hrvatska' => 'HRVATSKA_HR',
        ],
        'CY' => [
            'Cyprus Post' => 'CYPRUS_POST_CYP',
        ],
        'CZ' => [
            'Ceska' => 'CESKA_CZ',
            'GLS Czech Republic' => 'GLS_CZ',
        ],
        'FR' => [
            'BERT TRANSPORT' => 'BERT',
            'Chronopost France' => 'CHRONOPOST_FR',
            'Coliposte' => 'COLIPOSTE',
            'Colis France' => 'COLIS',
            'DHL France' => 'DHL_FR',
            'DPD France' => 'DPD_FR',
            'GEODIS - Distribution & Express' => 'GEODIS',
            'GLS France' => 'GLS_FR',
            'LA Poste' => 'LAPOSTE',
            'Mondial Relay' => 'MONDIAL',
            'Relais Colis' => 'RELAIS_COLIS_FR',
            'Teliway' => 'TELIWAY',
            'TNT France' => 'TNT_FR',
        ],
        'DE' => [
            'Asendia Germany' => 'ASENDIA_DE',
            'Deltec Germany' => 'DELTEC_DE',
            'Deutsche' => 'DEUTSCHE_DE',
            'DHL Deutsche Post' => 'DHL_DEUTSCHE_POST',
            'DPD Germany' => 'DPD_DE',
            'GLS Germany' => 'GLS_DE',
            'Hermes Germany' => 'HERMES_DE',
            'TNT Germany' => 'TNT_DE',
        ],
        'GR' => [
            'ELTA Greece' => 'ELTA_GR',
            'Geniki Greece' => 'GENIKI_GR',
            'GRC Greece' => 'ACS_GR',
        ],
        'HK' => [
            'Asendia Hong Kong' => 'ASENDIA_HK',
            'DHL Hong Kong' => 'DHL_HK',
            'DPD Hong Kong' => 'DPD_HK',
            'Hong Kong Post' => 'HK_POST',
            'Kerry Express Hong Kong' => 'KERRY_EXPRESS_HK',
            'Logistics Worldwide Hong Kong' => 'LOGISTICSWORLDWIDE_HK',
            'Quantium' => 'QUANTIUM',
            'Seko Logistics' => 'SEKOLOGISTICS',
            'TA-Q-BIN Parcel Hong Kong' => 'TAQBIN_HK',
        ],
        'HU' => [
            'Magyar' => 'MAGYAR_HU',
        ],
        'IS' => [
            'Postur' => 'POSTUR_IS',
        ],
        'IN' => [
            'Bluedart' => 'BLUEDART',
            'Delhivery' => 'DELHIVERY_IN',
            'DotZot' => 'DOTZOT',
            'DTDC India' => 'DTDC_IN',
            'Ekart' => 'EKART',
            'India Post' => 'INDIA_POST',
            'Professional Couriers' => 'PROFESSIONAL_COURIERS',
            'Red Express' => 'REDEXPRESS',
            'Swift Air' => 'SWIFTAIR',
            'Xpress Bees' => 'XPRESSBEES',
        ],
        'ID' => [
            'First Logistics' => 'FIRST_LOGISITCS',
            'JNE Indonesia' => 'JNE_IDN',
            'Lion Parcel' => 'LION_PARCEL',
            'Ninjavan Indonesia' => 'NINJAVAN_ID',
            'Pandu Logistics' => 'PANDU',
            'Pos Indonesia Domestic' => 'POS_ID',
            'Pos Indonesia International' => 'POS_INT',
            'RPX Indonesia' => 'RPX_ID',
            'RPX International' => 'RPX',
            'Tiki' => 'TIKI_ID',
            'Wahana' => 'WAHANA_ID',
        ],
        'IE' => [
            'AN POST Ireland' => 'AN_POST',
            'DPD Ireland' => 'DPD_IR',
            'Masterlink' => 'MASTERLINK',
            'TPG' => 'TPG',
            'Wiseloads' => 'WISELOADS',
        ],
        'IL' => [
            'Israel Post' => 'ISRAEL_POST',
        ],
        'IT' => [
            'BRT Bartolini' => 'BRT_IT',
            'DHL Italy' => 'DHL_IT',
            'DMM Network' => 'DMM_NETWORK',
            'FERCAM Logistics & Transport' => 'FERCAM_IT',
            'GLS Italy' => 'GLS_IT',
            'Hermes Italy' => 'HERMES_IT',
            'Poste Italiane' => 'IT_POSTE_ITALIA',
            'Register Mail IT' => 'REGISTER_MAIL_IT',
            'SDA Italy' => 'SDA_IT',
            'SGT Corriere Espresso' => 'SGT_IT',
            'TNT Click Italy' => 'TNT_CLICK_IT',
            'TNT Italy' => 'TNT_IT',
        ],
        'JP' => [
            'DHL Japan' => 'DHL_JP',
            'Japan Post' => 'JAPAN_POST',
            'Pocztex' => 'POCZTEX',
            'Sagawa' => 'SAGAWA_JP',
            'TNT Japan' => 'TNT_JP',
            'Yamato Japan' => 'YAMATO',
        ],
        'KR' => [
            'Ecargo' => 'ECARGO',
            'eParcel Korea' => 'EPARCEL_KR',
            'Korea Post' => 'KOREA_POST',
            'Korea Thai CJ' => 'CJ_KR',
            'Logistics Worldwide Korea' => 'LOGISTICSWORLDWIDE_KR',
            'Pantos' => 'PANTOS',
            'Rincos' => 'RINCOS',
            'Rocket Parcel International' => 'ROCKET_PARCEL',
            'SRE Korea' => 'SRE_KOREA',
        ],
        'LT' => [
            'Lietuvos Pastas' => 'LIETUVOS_LT',
        ],
        'LV' => [
            'CDEK courier' => 'CDEK',
            'Latvijas Pasts' => 'LATVIJAS_PASTS',
        ],
        'MY' => [
            'Airpak' => 'AIRPAK_MY',
            'CityLink Malaysia' => 'CITYLINK_MY',
            'CJ Malaysia' => 'CJ_MY',
            'CJ Malaysia International' => 'CJ_INT_MY',
            'Cuckoo Express' => 'CUCKOOEXPRESS',
            'Jet Ship Malaysia' => 'JETSHIP_MY',
            'Kangaroo Express' => 'KANGAROO_MY',
            'Logistics Worldwide Malaysia' => 'LOGISTICSWORLDWIDE_MY',
            'Malaysia Post EMS / Pos Laju' => 'MALAYSIA_POST',
            'Nationwide' => 'NATIONWIDE',
            'Ninjavan Malaysia' => 'NINJAVAN_MY',
            'Skynet Malaysia' => 'SKYNET_MY',
            'TA-Q-BIN Parcel Malaysia' => 'TAQBIN_MY',
        ],
        'MX' => [
            'Correos De Mexico' => 'CORREOS_MX',
            'Estafeta' => 'ESTAFETA',
            'Mexico Aeroflash' => 'AEROFLASH',
            'Mexico Redpack' => 'REDPACK',
            'Mexico Senda Express' => 'SENDA_MX',
        ],
        'NL' => [
            'DHL Netherlands' => 'DHL_NL',
            'DHL Parcel Netherlands' => 'DHL_PARCEL_NL',
            'GLS Netherlands' => 'GLS_NL',
            'Kiala' => 'KIALA',
            'PostNL' => 'POSTNL',
            'PostNl International' => 'POSTNL_INT',
            'PostNL International 3S' => 'POSTNL_INT_3_S',
            'TNT Netherlands' => 'TNT_NL',
            'Transmission Netherlands' => 'TRANSMISSION',
        ],
        'NZ' => [
            'Courier Post' => 'COURIER_POST',
            'Fastway New Zealand' => 'FASTWAY_NZ',
            'New Zealand Post' => 'NZ_POST',
            'Toll IPEC' => 'TOLL_IPEC',
        ],
        'NG' => [
            'Courier Plus' => 'COURIERPLUS',
            'NiPost' => 'NIPOST_NG',
        ],
        'NO' => [
            'Posten Norge' => 'POSTEN_NORGE',
        ],
        'PH' => [
            '2GO' => 'TWO_GO',
            'Air 21' => 'AIR_21',
            'Airspeed' => 'AIRSPEED',
            'Jam Express' => 'JAMEXPRESS_PH',
            'LBC Express' => 'LBC_PH',
            'Ninjavan Philippines' => 'NINJAVAN_PH',
            'RAF Philippines' => 'RAF_PH',
            'Xend Express' => 'XEND_EXPRESS_PH',
        ],
        'PL' => [
            'DHL Poland' => 'DHL_PL',
            'DPD Poland' => 'DPD_PL',
            'InPost Paczkomaty' => 'INPOST_PACZKOMATY',
            'Poczta Polska' => 'POCZTA_POLSKA',
            'Siodemka' => 'SIODEMKA',
            'TNT Poland' => 'TNT_PL',
        ],
        'PT' => [
            'Adicional Logistics' => 'ADICIONAL_PT',
            'Chronopost Portugal' => 'CHRONOPOST_PT',
            'Portugal PTT' => 'CTT_PT',
            'Portugal Seur' => 'SEUR_PT',
        ],
        'RO' => [
            'DPD Romania' => 'DPD_RO',
            'Postaromana' => 'POSTA_RO',
        ],
        'RU' => [
            'DPD Russia' => 'DPD_RU',
            'Russian Post' => 'RUSSIAN_POST',
        ],
        'SA' => [
            'Dawn Wing' => 'DAWN_WING',
            'Ram' => 'RAM',
            'The Courier Guy' => 'THE_COURIER_GUY',
        ],
        'CS' => [
            'Serbia Post' => 'POST_SERBIA_CS',
        ],
        'SG' => [
            'DHL Singapore' => 'DHL_SG',
            'JetShip Singapore' => 'JETSHIP_SG',
            'Ninjavan Singapore' => 'NINJAVAN_SG',
            'Parcel Post' => 'PARCELPOST_SG',
            'Singapore Post' => 'SINGPOST',
            'TA-Q-BIN Parcel Singapore' => 'TAQBIN_SG',
        ],
        'ZA' => [
            'Fastway South Africa' => 'FASTWAY_ZA',
        ],
        'ES' => [
            'ASM' => 'ASM_ES',
            'CBL Logistics' => 'CBL_LOGISTICA',
            'Correos De Spain' => 'CORREOS_ES',
            'DHL Spain' => 'DHL_ES',
            'DHL Parcel Spain' => 'DHL_PARCEL_ES',
            'GLS Spain' => 'GLS_ES',
            'International Suer' => 'INT_SEUR',
            'ITIS' => 'ITIS',
            'Nacex Spain' => 'NACEX_ES',
            'Redur Spain' => 'REDUR_ES',
            'Spanish Seur' => 'SEUR_ES',
            'TNT Spain' => 'TNT_ES',
        ],
        'SE' => [
            'DB Schenker Sweden' => 'DBSCHENKER_SE',
            'DirectLink Sweden' => 'DIRECTLINK_SE',
            'PostNord Logistics' => 'POSTNORD_LOGISTICS_GLOBAL',
            'PostNord Logistics Denmark' => 'POSTNORD_LOGISTICS_DK',
            'PostNord Logistics Sweden' => 'POSTNORD_LOGISTICS_SE',
        ],
        'CH' => [
            'Swiss Post' => 'SWISS_POST',
        ],
        'TW' => [
            'Chunghwa Post' => 'CHUNGHWA_POST',
            'Taiwan Post' => 'TAIWAN_POST_TW',
        ],
        'TH' => [
            'Acommerce' => 'ACOMMMERCE',
            'Alphafast' => 'ALPHAFAST',
            'CJ Thailand' => 'CJ_TH',
            'FastTrack Thailand' => 'FASTRACK',
            'Kerry Express Thailand' => 'KERRY_EXPRESS_TH',
            'NIM Express' => 'NIM_EXPRESS',
            'Ninjavan Thailand' => 'NINJAVAN_THAI',
            'SendIt' => 'SENDIT',
            'Thailand Post' => 'THAILAND_POST',
        ],
        'TR' => [
            'PTT Posta' => 'PTT_POST',
        ],
        'UA' => [
            'Nova Poshta' => 'NOVA_POSHTA',
            'Nova Poshta International' => 'NOVA_POSHTA_INT',
        ],
        'AE' => [
            'AXL Express & Logistics' => 'AXL',
            'Continental' => 'CONTINENTAL',
            'Skynet Worldwide Express UAE' => 'SKYNET_UAE',
        ],
        'UK' => [
            'Airborne Express UK' => 'AIRBORNE_EXPRESS_UK',
            'Airsure' => 'AIRSURE',
            'APC Overnight' => 'APC_OVERNIGHT',
            'Asendia UK' => 'ASENDIA_UK',
            'CollectPlus' => 'COLLECTPLUS',
            'Deltec UK' => 'DELTEC_UK',
            'DHL UK' => 'DHL_UK',
            'DPD Delistrack' => 'DPD_DELISTRACK',
            'DPD UK' => 'DPD_UK',
            'Fastway UK' => 'FASTWAY_UK',
            'HermesWorld' => 'HERMESWORLD_UK',
            'Interlink Express' => 'INTERLINK',
            'MyHermes UK' => 'MYHERMES',
            'Nightline UK' => 'NIGHTLINE_UK',
            'Parcel Force' => 'PARCELFORCE',
            'Royal Mail' => 'ROYAL_MAIL',
            'RPD2man Deliveries' => 'RPD_2_MAN',
            'Skynet Worldwide Express UK' => 'SKYNET_UK',
            'TNT UK' => 'TNT_UK',
            'UK Mail' => 'UK_MAIL',
            'Yodel' => 'YODEL',
        ],
        'US' => [
            'ABC Package Express' => 'ABC_PACKAGE',
            'Airborne Express' => 'AIRBORNE_EXPRESS',
            'Asendia USA' => 'ASENDIA_US',
            'Cpacket' => 'CPACKET',
            'Ensenda USA' => 'ENSENDA',
            'Estes' => 'ESTES',
            'Fastway USA' => 'FASTWAY_US',
            'Globegistics USA' => 'GLOBEGISTICS',
            'International Bridge' => 'INTERNATIONAL_BRIDGE',
            'OnTrac' => 'ONTRAC',
            'RL Carriers' => 'RL_US',
            'RR Donnelley' => 'RRDONNELLEY',
            'USPS' => 'USPS',
        ],
        'VN' => [
            'Kerry Express Vietnam' => 'KERRY_EXPRESS_VN',
            'Vietnam Post' => 'VIETNAM_POST',
            'Vietnam Post EMS' => 'VNPOST_EMS',
        ],
    ];

    /**
     * @var array
     */
    private $carriers;

    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        array $carriers
    ) {
        parent::__construct($translator, $locales);

        $this->carriers = $carriers;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('carrierId', ChoiceType::class, [
                'choices' => $this->carriers,
                'label' => $this->trans('Carrier name', 'Modules.Paypaltracking.Admin'),
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans(
                            'This field cannot be empty.',
                            'Admin.Notifications.Error'
                        ),
                    ]),
                ],
            ])
            ->add('paypalCarrierEnum', ChoiceType::class, [
                'choices' => self::PAYPAL_CARRIERS,
                'label' => $this->trans('Paypal carrier enum', 'Modules.Paypaltracking.Admin'),
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans(
                            'This field cannot be empty.',
                            'Admin.Notifications.Error'
                        ),
                    ]),
                ],
            ])
            ->add('countryId', CountryChoiceType::class, [
                'label' => $this->trans('Country', 'Admin.Global'),
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans(
                            'This field cannot be empty.',
                            'Admin.Notifications.Error'
                        ),
                    ]),
                ],
            ])
            ->add('worldwide', SwitchType::class, [
                'label' => $this->getTranslator()->trans('Is worldwide?', [], 'Modules.Paypaltracking.Admin'),
                'required' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'Modules.Paypaltracking.Admin',
            ]);
    }
}
