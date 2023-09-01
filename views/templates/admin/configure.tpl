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

<div class="panel">
    <h3><i class="icon icon-credit-card"></i> {l s='PayPal Update Tracking' d='Modules.Paypaltracking.Configure'}</h3>
    <p>
        <strong>{l s='This module send to PayPal the tracking number after it set in OrderCarrier' d='Modules.Paypaltracking.Configure'}</strong></p>
</div>

<div class="panel">
    <h3><i class="icon icon-tags"></i> {l s='Documentation' d='Modules.Paypaltracking.Configure'}</h3>
    <p>
        &raquo; {l s='You can get a PDF documentation to configure this module' d='Modules.Paypaltracking.Configure'} :
    <ul>
        <li><a href="{$module_dir|escape:'htmlall':'UTF-8'}docs/readme_en.pdf"
               target="_blank">{l s='English' d='Modules.Paypaltracking.Configure'}</a></li>
        <li><a href="{$module_dir|escape:'htmlall':'UTF-8'}docs/readme_it.pdf"
               target="_blank">{l s='Italian' d='Modules.Paypaltracking.Configure'}</a></li>
    </ul>
    </p>
</div>
