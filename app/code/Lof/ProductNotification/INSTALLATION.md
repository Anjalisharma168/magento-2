## How to installation

1. Setup module via FTP and run magento 2 commands:

The extension include 2 module: Lof_All, Lof_Setup, Lof_ChooserWidget, Lof_ConfigurableProduct and Lof_ProductNotification

- Connect your server with FTP client (example: FileZilla).
- Upload module files in the module packages in to folder: app/code/Lof/ProductNotification , app/code/Lof/ChooserWidget, app/code/Lof/All, app/code/Lof/ConfigurableProduct, app/code/Lof/Setup
- Access SSH terminal, then run commands:

```
php bin/magento setup:upgrade --keep-generated
php bin/magento setup:static-content:deploy -f
php bin/magento cache:clean
```

- To config the module please. Go to admin > Store > Configuration > Landofcoder Extensions > Product Notification
- To manage subscribers. Go to admin > Catalog > LOF Product Notification