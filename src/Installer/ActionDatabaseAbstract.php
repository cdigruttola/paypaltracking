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

namespace cdigruttola\PaypalTracking\Installer;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;

abstract class ActionDatabaseAbstract
{
    public const HOOK_LIST = [];

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $dbPrefix;

    /**
     * @var array
     */
    protected $queries = [];

    /**
     * @var array
     */
    protected $tableData = [];

    public function __construct(Connection $connection, string $dbPrefix)
    {
        $this->connection = $connection;
        $this->dbPrefix = $dbPrefix;
    }

    public function execute(): bool
    {
        $result = true;

        foreach ($this->getQueries() as $query) {
            $statement = $this->connection->executeQuery($query);

            if ($statement instanceof Statement && 0 !== (int) $statement->errorCode()) {
                $result &= false;
            }
        }

        return $result;
    }

    public function setData(array $data)
    {
        $this->tableData = $data;

        return $this;
    }

    public function getData(): array
    {
        return $this->tableData;
    }

    public function getQueries(): array
    {
        return $this->queries;
    }

    public function setQueries($queries)
    {
        $this->queries = $queries;

        return $this;
    }
}
