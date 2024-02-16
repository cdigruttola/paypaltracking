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
class ActionDatabaseAddColumn extends ActionDatabaseAbstract implements ActionDatabaseInterface
{
    public function buildQuery(): void
    {
        $tablesArray = $this->tableData['database_add'] ?? [];
        $this->setQueries([]);
        $queriesArray = [];

        foreach ($tablesArray as $tableName => $table) {
            if (!empty($table['columns'])) {
                foreach ($table['columns'] as $columnName => $columnDefinition) {
                    if (!$this->checkColumnExistenceInTable($tableName, $columnName)) {
                        $queriesArray[] = $this->buildSingleAddQuery($tableName, $columnName, $columnDefinition);
                    }
                }
            }
        }

        $this->setQueries($queriesArray);
    }

    private function buildSingleAddQuery($tableName, $columnName, $columnDefinition): string
    {
        $dbQuery = 'ALTER TABLE ' . $this->dbPrefix . $tableName . '
                    ADD ' . $columnName . ' ' . $columnDefinition;

        return $dbQuery;
    }

    private function checkColumnExistenceInTable($tableName, $columnName): bool
    {
        $dbQuery = "SELECT count(*)
                    FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA=DATABASE()
                    AND COLUMN_NAME='" . $columnName . "'
                    AND TABLE_NAME='" . $this->dbPrefix . $tableName . "'";

        $statement = $this->connection->executeQuery($dbQuery);

        return $statement->rowCount() > 0;
    }
}
