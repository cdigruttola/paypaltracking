{*
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
*}

<form id="module_update_batch_order" class="defaultForm form-horizontal"
      action="{$link}" method="post" enctype="multipart/form-data" novalidate="">
  <input type="hidden" name="redirect" value="{if isset($current) && $current}{$current|escape:'html':'UTF-8'}{if isset($token) && $token}&amp;token={$token|escape:'html':'UTF-8'}{/if}&amp;successBatchUpdate={/if}" />
  <div class="panel" id="fieldset_0">

    <div class="panel-heading">
      <i class="icon-cogs"></i>{l s='Update batch orders' d='Modules.Paypaltracking.Configure'}
    </div>

    <div class="form-wrapper">
      <div class="form-group">
        <label class="control-label col-lg-4">
            {l s='Update order from' d='Modules.Paypaltracking.Configure'}
        </label>
        <div class="col-lg-3">
          <input type="date" name="update_order_from" class="form-control">
        </div>
      </div>
      <div class="form-group">
        <label class="control-label col-lg-4">
            {l s='Update orders up to (excluding)' d='Modules.Paypaltracking.Configure'}
        </label>
        <div class="col-lg-3">
          <input type="date" name="update_order_to" class="form-control">
        </div>
      </div>
    </div>

    <div class="panel-footer">
      <button type="submit" value="1" id="module_update_batch_order_submit_btn" name="update_batch_orders"
              class="btn btn-default pull-right">
        <i class="process-icon-save"></i>{l s='Update batch orders' d='Modules.Paypaltracking.Configure'}
      </button>
    </div>

  </div>
</form>
