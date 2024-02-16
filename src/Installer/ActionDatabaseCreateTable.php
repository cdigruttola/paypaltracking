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
class ActionDatabaseCreateTable extends ActionDatabaseAbstract implements ActionDatabaseInterface
{
    public const defaultEngine = 'InnoDb';
    public const defaultCharset = 'UTF8';

    public function buildQuery(): void
    {
        $tablesArray = $this->tableData['database'] ?? [];
        $this->setQueries([]);
        $queriesArray = [];

        foreach ($tablesArray as $tableName => $table) {
            $queriesArray[] = $this->buildSingleCreateQuery($tableName, $table);
        }

        $this->setQueries($queriesArray);
    }

    private function buildSingleCreateQuery($tableName, $table): string
    {
        $dbQuery = '';

        if (empty($table['columns']) || empty($tableName)) {
            return $dbQuery;
        }

        $dbQuery .= 'CREATE TABLE IF NOT EXISTS ' . $this->dbPrefix . $tableName;
        $dbQuery .= ' (';

        $dbColumnsQuery = [];

        foreach ($table['columns'] as $columnName => $column) {
            $dbColumnsQuery[] = $columnName . ' ' . $column;
        }

        if (!empty($table['primary'])) {
            $dbColumnsQuery[] = 'PRIMARY KEY (' . implode(', ', $table['primary']) . ')';
        }

        $dbQuery .= implode(',', $dbColumnsQuery);

        $dbQuery .= ')';

        $dbQuery .= ' ENGINE = ' . (!empty($table['engine']) ? $table['engine'] : ActionDatabaseCreateTable::defaultEngine);

        $dbQuery .= ' DEFAULT CHARACTER SET ' . (!empty($table['charset']) ? $table['charset'] : ActionDatabaseCreateTable::defaultCharset);

        return $dbQuery;
    }
}
