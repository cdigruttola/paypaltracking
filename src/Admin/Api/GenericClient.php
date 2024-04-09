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

use GuzzleHttp\Client;

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class GenericClient
{
    protected $client;
    /** @var false|\Module */
    protected $module;
    private $route;

    public function __construct()
    {
        $this->module = \Module::getInstanceByName('paypaltracking');

        if ($this->getGuzzleMajorVersionNumber() >= 6) {
            $this->client = new Client([
                'base_uri' => $this->module->getPayPalApiUrl(),
            ]);
        } else {
            $this->client = new Client([
                'base_url' => $this->module->getPayPalApiUrl(),
            ]);
        }
    }

    /**
     * @param array $options
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function post(array $options = [])
    {
        return $this->client->post($this->route, $options);
    }

    /**
     * @param array $options
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function put(array $options = [])
    {
        return $this->client->put($this->route, $options);
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route): void
    {
        $this->route = $route;
    }

    public function getGuzzleMajorVersionNumber()
    {
        // Guzzle 7 and above
        if (defined('\GuzzleHttp\ClientInterface::MAJOR_VERSION')) {
            // @phpstan-ignore-next-line
            return (int) \GuzzleHttp\ClientInterface::MAJOR_VERSION;
        }

        // Before Guzzle 7
        if (defined('\GuzzleHttp\ClientInterface::VERSION')) {
            // @phpstan-ignore-next-line
            return (int) \GuzzleHttp\ClientInterface::VERSION[0];
        }

        return null;
    }
}
