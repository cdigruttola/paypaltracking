<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace cdigruttola\Module\PaypalTracking\Admin\Api\Tracking;

use cdigruttola\Module\PaypalTracking\Admin\Api\GenericClient;
use cdigruttola\Module\PaypalTracking\Admin\Api\Token;
use GuzzleHttp\Exception\ClientException;

/**
 * Construct the client used to make call to maasland
 */
class TrackingClient extends GenericClient
{

    public function __construct()
    {
        parent::__construct();
        $this->client->setDefaultOption(
            'headers', [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . (new Token())->getToken(),
            ]
        );
    }

    /**
     *
     * @param $transaction_id
     * @param $tracking_number
     * @throws ClientException
     */
    public function addShippingInfo($transaction_id, $tracking_number)
    {
        $this->setRoute('/v1/shipping/trackers-batch');
        $this->post([
            'json' => [
                'trackers' => [[
                    'transaction_id' => $transaction_id,
                    'status' => 'IN_PROCESS',
                    'carrier' => 'IT_POSTE_ITALIANE', //TODO to be modified to consent user choice from BO
                    'tracking_number' => $tracking_number,
                    'tracking_number_type' => 'CARRIER_PROVIDED',
                    'tracking_number_validated' => true,
                ]]
            ],
        ]);
    }

    /**
     *
     * @param $transaction_id
     * @param $tracking_number
     * @throws ClientException
     */
    public function updateShippingInfo($transaction_id, $tracking_number)
    {
        $this->setRoute('/v1/shipping/trackers/' . $transaction_id . '-' . $tracking_number);
        $this->put([
            'json' => [
                'transaction_id' => $transaction_id,
                'status' => 'SHIPPED',
                'carrier' => 'IT_POSTE_ITALIANE', //TODO to be modified to consent user choice from BO
                'tracking_number' => $tracking_number,
            ],
        ]);
    }

}
