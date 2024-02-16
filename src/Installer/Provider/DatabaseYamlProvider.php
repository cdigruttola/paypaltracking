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

namespace cdigruttola\PaypalTracking\Installer\Provider;

if (!defined('_PS_VERSION_')) {
    exit;
}

use cdigruttola\PaypalTracking\Exceptions\DatabaseYamlFileNotExistsException;

class DatabaseYamlProvider
{
    /**
     * @var \Paypaltracking
     */
    protected $module;

    public function __construct(\Paypaltracking $module)
    {
        $this->module = $module;
    }

    public function getDatabaseFilePath(): string
    {
        $filePossiblePath = _PS_MODULE_DIR_ . $this->module->name . '/config/';
        $databaseFileName = 'database.yml';
        $fullFilePath = $filePossiblePath . $databaseFileName;

        if (file_exists($fullFilePath)) {
            return $fullFilePath;
        } else {
            throw new DatabaseYamlFileNotExistsException($databaseFileName . ' file not exist in ' . $filePossiblePath);
        }
    }
}
