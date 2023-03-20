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
 *  @author    cdigruttola <c.digruttola@hotmail.it>
 *  @copyright Copyright since 2007 Carmine Di Gruttola
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 */

import Grid from '@PSJs/components/grid/grid';
import LinkRowActionExtension from '@PSJs/components/grid/extension/link-row-action-extension';
import SubmitRowActionExtension from '@PSJs/components/grid/extension/action/row/submit-row-action-extension';
import SortingExtension from '@PSJs/components/grid/extension/sorting-extension';
import FiltersResetExtension from '@PSJs/components/grid/extension/filters-reset-extension';
import ReloadListActionExtension from '@PSJs/components/grid/extension/reload-list-extension';
import ColumnTogglingExtension from '@PSJs/components/grid/extension/column-toggling-extension';
import ExportToSqlManagerExtension from '@PSJs/components/grid/extension/export-to-sql-manager-extension';
import FormSubmitButton from '@PSJs/components/form-submit-button';

const {$} = window

$(() => {
    const recipeGrid = new Grid('paypalCarrierTracking')
    recipeGrid.addExtension(new SortingExtension());
    recipeGrid.addExtension(new LinkRowActionExtension());
    recipeGrid.addExtension(new SubmitRowActionExtension());
    recipeGrid.addExtension(new FiltersResetExtension());
    recipeGrid.addExtension(new ReloadListActionExtension());
    recipeGrid.addExtension(new ColumnTogglingExtension());
    recipeGrid.addExtension(new ExportToSqlManagerExtension());

    new FormSubmitButton();
});
