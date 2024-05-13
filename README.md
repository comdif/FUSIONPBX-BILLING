# FUSIONPBX-BILLING

Billing for the great multi-tenant IPBX Fusionpbx https://github.com/fusionpbx/fusionpbx

In /var/www/html/fusionpbx
install the directory "mybilladmin" with all content, customise cfg.php and point your browser to the url

https://yourfusion_pbx, login as admin, then you can go to https://yourfusion_pbx/mybilladmin

start by importing rates.
prepair a complete csv file using this format:

336;France-mobile-06;;0;0;200;1000

337;;France-mobile-07;;0;0;200;1000

331;;France-fixed-01;;0;0;200;1000

You can get a "csv" price list on many SIP Trunking Provider website to help you.

That's all you are ready to bill any account.

!! The scrip is auto-installable without any action and create the needed DB !!
