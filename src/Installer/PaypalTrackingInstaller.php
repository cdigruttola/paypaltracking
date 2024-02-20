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

namespace cdigruttola\PaypalTracking\Installer;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Doctrine\DBAL\Connection;
use PrestaShop\PrestaShop\Adapter\ContainerFinder;

class PaypalTrackingInstaller
{
    /**
     * @var DatabaseYamlParser
     */
    private $databaseYaml;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Context
     */
    private $context;

    /**
     * @param Connection $connection
     * @param DatabaseYamlParser $databaseYaml
     * @param \Context $context
     */
    public function __construct(Connection $connection, DatabaseYamlParser $databaseYaml, $context)
    {
        $this->connection = $connection;
        $this->databaseYaml = $databaseYaml;
        $this->context = $context;
    }

    private function getContainer()
    {
        if (null === $this->context->container) {
            $containerFinder = new ContainerFinder($this->context);
            $container = $containerFinder->getContainer();
            $this->context->container = $container;
        }

        return $this->context->container;
    }

    public function createTables(): bool
    {
        $databaseData = $this->databaseYaml->getParsedFileData();
        $container = $this->getContainer();

        // THIS WAY INSTEAD OF SERVICE CALL COZ OF NOT AVAILABLE SERVICE DURING INSTALLATION
        $createTableAction = new ActionDatabaseCreateTable(
            $container->get('doctrine.dbal.default_connection'),
            $container->getParameter('database_prefix')
        );

        // THIS WAY INSTEAD OF SERVICE CALL COZ OF NOT AVAILABLE SERVICE DURING INSTALLATION
        $addColumnsAction = new ActionDatabaseAddColumn(
            $container->get('doctrine.dbal.default_connection'),
            $container->getParameter('database_prefix')
        );

        // THIS WAY INSTEAD OF SERVICE CALL COZ OF NOT AVAILABLE SERVICE DURING INSTALLATION
        $modifyColumnsAction = new ActionDatabaseModifyColumn(
            $container->get('doctrine.dbal.default_connection'),
            $container->getParameter('database_prefix')
        );

        $createTableAction
            ->setData($databaseData)
            ->buildQuery();

        $addColumnsAction
            ->setData($databaseData)
            ->buildQuery();

        $modifyColumnsAction
            ->setData($databaseData)
            ->buildQuery();

        return $createTableAction->execute()
                && $addColumnsAction->execute()
                && $modifyColumnsAction->execute();
    }

    /**
     * @return bool
     */
    public function dropTables(): bool
    {
        $databaseData = $this->databaseYaml->getParsedFileData();
        $container = $this->getContainer();
        $dropTableAction = $container->get('cdigruttola.paypaltracking.installer.action_database_drop_table');
        $dropTableAction
            ->setData($databaseData)
            ->buildQuery();

        $result = $dropTableAction->execute();

        return $result;
    }

    public function modifyIndex(): bool
    {
        $databaseData = $this->databaseYaml->getParsedFileData();
        $container = $this->getContainer();

        $modifyIndex = new ActionDatabaseModifyIndex(
            $container->get('doctrine.dbal.default_connection'),
            $container->getParameter('database_prefix')
        );

        $modifyIndex
            ->setData($databaseData)
            ->buildQuery();

        return true ; //$modifyIndex->execute();
    }
}
