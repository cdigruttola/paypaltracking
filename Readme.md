# TrackPrestaPay - Paypal Tracking Module Prestashop

## Introduction
The Paypal Tracking Module allows you to to send tracking information to PayPal via API.

## Requirements
The requirements needed to run the module on your system are:
- PrestaShop version 1.7.8 or higher
- PHP 7.4 or higher

## Module installation
Buy module in [Gumroad]([https://trackprestapay.gumroad.com/l/zufhl](https://trackprestapay.gumroad.com/l/paypaltrackingmodule))

Open the administration panel of your store and go to the Module -> Module manager menu.
Click on the button located at the top right "Upload a module".

A panel will be opened where you can select the form file to be loaded.
Click the "Choose a file" and select the zip file, exactly as downloaded from the store PrestaShop addons.
Click on the button "Upload this module" and wait for the upload confirmation message.
After the installation is finished (it takes very little time) it will return the successful installation message.

## Configuration
The module is configured by clicking on the "Configure" button accessible from the list of modules.
Here you can configure the following fields:
- PayPal Live Mode
- PayPal API Client ID
- PayPal API Client Secret
- Modules that are using PayPal

To obtain the PayPal API Client ID and Secret you should follow the steps in [PayPal](https://www.paypal.com/merchantapps/appcenter/streamlineoperations/apicredentials).

## Configure PayPal Carrier Tracking
PayPal provides its carriers names, available in [PayPal Carrier](https://developer.paypal.com/docs/tracking/reference/carriers/).

To associate your carriers to PayPal carriers, in Payment -> PayPal Tracking a form is available.

## Update to Version 2.0.0
In case of update to the version 2.0.0 you need to uninstall and re-install the module
