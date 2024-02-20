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

class ActionDatabaseModifyIndex extends ActionDatabaseAbstract implements ActionDatabaseInterface
{
    public function buildQuery(): void
    {
        $tablesArray = $this->tableData['database_modify_index'] ?? [];
        $this->setQueries([]);
        $queriesArray = [];

        foreach ($tablesArray as $tableName => $table) {
            if (!empty($table['unique'])) {
                $drop = $this->dropOldIndex($tableName);
                if ($drop) {
                    $queriesArray[] = $drop;
                }
                $queriesArray[] = $this->buildSingleModifyQuery($tableName, $table['unique']);
            }
        }

        $this->setQueries($queriesArray);
    }

    private function buildSingleModifyQuery($tableName, $unique): string
    {
        return 'ALTER TABLE ' . $this->dbPrefix . $tableName . ' ADD UNIQUE INDEX (' . implode(', ', $unique) . ')';
    }

    private function dropOldIndex($tableName): ?string
    {
        $dbQuery = 'SHOW CREATE TABLE ' . $this->dbPrefix . $tableName;

        $statement = $this->connection->executeQuery($dbQuery);

        if ($statement->rowCount() > 0) {
            $lines = explode("\n", $statement->fetchAssociative()['Create Table']);

            foreach ($lines as $line) {
                if (str_contains($line, 'UNIQUE KEY')) {
                    if (preg_match('/UNIQUE KEY `(.*)`/Ui', $line, $arr)) {
                        return "ALTER TABLE $this->dbPrefix$tableName DROP INDEX `$arr[1]`";
                    }
                }
            }
        }

        return null;
    }
}
