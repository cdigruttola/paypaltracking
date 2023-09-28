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

namespace cdigruttola\Module\PaypalTracking\Admin\Api;

use GuzzleHttp\Exception\ClientException;

/**
 * Handle authentication firebase requests
 */
class Token extends GenericClient
{

    /**
     * @return false|string
     *
     * @throws ClientException
     */
    public function getToken()
    {
        $id_shop = \Context::getContext()->shop->id;
        if ($this->isExpired()) {
            $this->setRoute('/v1/oauth2/token');
            $response = $this->post([
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'auth' => [
                    \Configuration::get(\Paypaltracking::PAYPAL_API_CLIENT_ID, null, null, $id_shop),
                    \Configuration::get(\Paypaltracking::PAYPAL_API_CLIENT_SECRET, null, null, $id_shop),
                ],
                'body' => [
                    'grant_type' => 'client_credentials',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            \Configuration::updateValue('PAYPAL_API_ACCESS_TOKEN', $data['access_token'], false, null, $id_shop);
            \Configuration::updateValue('PAYPAL_API_ACCESS_TOKEN_EXPIRES_IN', $data['expires_in'], false, null, $id_shop);
            \Configuration::updateValue('PAYPAL_API_ACCESS_TOKEN_REQUESTED_DATE', date('Y-m-d H:i:s'), false, null, $id_shop);
        }

        return \Configuration::get('PAYPAL_API_ACCESS_TOKEN', null, null, $id_shop);
    }

    /**
     * Check the token validity. The token expire time is set to 3600 seconds.
     *
     * @return bool
     */
    public function isExpired()
    {
        $refresh_date = \Configuration::get('PAYPAL_API_ACCESS_TOKEN_REQUESTED_DATE', null, null, (int) \Context::getContext()->shop->id);

        if (empty($refresh_date)) {
            return true;
        }

        return strtotime($refresh_date) + 32400 < time();
    }
}
