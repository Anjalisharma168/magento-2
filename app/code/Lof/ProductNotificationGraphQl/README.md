# Magento 2 Module Lof ProductNotificationGraphQl

    ``landofcoder/module-productnotification-graph-ql``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Magento 2 product notify in stock, sales price to subscriber Graph QL

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Lof`
 - Enable the module by running `php bin/magento module:enable Lof_ProductNotificationGraphQl`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require lof/module-productnotificationgraphql`
 - enable the module by running `php bin/magento module:enable Lof_ProductNotificationGraphQl`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration

 - enable (productnotification/general_settings/enable)

 - email_identity (productnotification/productalert/email_identity)

 - allow_price (productnotification/productalert/allow_price)

 - disable_price_guest (productnotification/productalert/disable_price_guest)

 - allow_stock (productnotification/productalert/allow_stock)

 - disable_stock_guest (productnotification/productalert/disable_stock_guest)

 - allow_new_product (productnotification/productalert/allow_new_product)

 - cat_ids (productnotification/productalert/cat_ids)


## Specifications

 - GraphQl Endpoint
	- SubscribeProductSalePrice

 - GraphQl Endpoint
	- SubscribeProductStock

 - GraphQl Endpoint
	- UnSubscribeProductSalePrice

 - GraphQl Endpoint
	- UnSubscribeProductStock

 - GraphQl Endpoint
	- MySubscribeProductSalePrice

 - GraphQl Endpoint
	- MySubscribeProductStock


## Attributes

## Require Extensions
- landofcoder/module-product-notification

## Example Graph Ql Query
1. Subscribe Price Drops Notification

```
mutation {
 subscribeProductSalePrice(
  product_id: 15
  product_sku: "24-MB06"
  price:45.00
  subscriber_name: "roni_cost"
  subscriber_email: "roni_cost@example.com"
  message: "Please let me know when it have sale price"
)
}
```
2. Subscribe Out of stock - Product back stock Notification

```
mutation {
 subscribeProductStock(
  product_id: 15
  product_sku: "24-MB06"
  subscriber_name: "roni_cost"
  subscriber_email: "roni_cost@example.com"
  message: "Please let me know when it have back to stock"
)
}
```

3. Show List product price drop subscription of logged in customer

```
{
mySubscribeProductSalePrice(
  filters: {}
  sort: {
    alert_price_id: ASC
  }
){
  total_count
  items{
    alert_price_id
    subscriber_name
    subscriber_email
    customer_id
    send_count
    price
    product_id
    token
    message
    product_sku
    website_id
    store_id
  }
}
}
```

4. Show List out of stock subscription of logged in customer

```
{
mySubscribeProductStock(
  filters: {}
  sort: {
    alert_stock_id: ASC
  }
){
  total_count
  items{
    alert_stock_id
    subscriber_name
    subscriber_email
    customer_id
    send_count
    price
    product_id
    token
    message
    product_sku
    website_id
    store_id
  }
}
}
```

5. Un Subscription Price Drop by id

```
mutation{
unSubscribeProductSalePrice(
  id:2,
  token:"7umjb382d04n8d3ayiargm6cqzqb08"
  email:"roni_cost@example.com"
)
}
```

6. Un Subscription Out of stock by id

```
mutation{
unSubscribeProductStock(
  id:2,
  token:"7umjb382d04n8d3ayiargm6cqzqb08"
  email:"roni_cost@example.com"
)
}
```

7. Un Subscription Price Drop all by website_id

```
mutation{
unSubscribeProductSalePrice(
  id:2,
  token:"7umjb382d04n8d3ayiargm6cqzqb08"
  email:"roni_cost@example.com"
  website_id: 1
)
}
```

8. Un Subscription Price Drop all by website_id

```
mutation{
unSubscribeProductStock(
  id:2,
  token:"7umjb382d04n8d3ayiargm6cqzqb08"
  email:"roni_cost@example.com"
  website_id: 1
)
}
```

## Donation

If this project help you reduce time to develop, you can give me a cup of coffee :) 

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/paypalme/allorderdesk)


**Our Magento 2 Extensions List**
* [Megamenu for Magento 2](https://landofcoder.com/magento-2-mega-menu-pro.html/)

* [Page Builder for Magento 2](https://landofcoder.com/magento-2-page-builder.html/)

* [Magento 2 Marketplace - Multi Vendor Extension](https://landofcoder.com/magento-2-marketplace-extension.html/)

* [Magento 2 Multi Vendor Mobile App Builder](https://landofcoder.com/magento-2-multi-vendor-mobile-app.html/)

* [Magento 2 Form Builder](https://landofcoder.com/magento-2-form-builder.html/)

* [Magento 2 Reward Points](https://landofcoder.com/magento-2-reward-points.html/)

* [Magento 2 Flash Sales - Private Sales](https://landofcoder.com/magento-2-flash-sale.html)

* [Magento 2 B2B Packages](https://landofcoder.com/magento-2-b2b-extension-package.html)

* [Magento 2 One Step Checkout](https://landofcoder.com/magento-2-one-step-checkout.html/)

* [Magento 2 Customer Membership](https://landofcoder.com/magento-2-membership-extension.html/)

* [Magento 2 Checkout Success Page](https://landofcoder.com/magento-2-checkout-success-page.html/)


**Featured Magento Services**

* [Customization Service](https://landofcoder.com/magento-2-create-online-store/)

* [Magento 2 Support Ticket Service](https://landofcoder.com/magento-support-ticket.html/)

* [Magento 2 Multi Vendor Development](https://landofcoder.com/magento-2-create-marketplace/)

* [Magento Website Maintenance Service](https://landofcoder.com/magento-2-customization-service/)

* [Magento Professional Installation Service](https://landofcoder.com/magento-2-installation-service.html)

* [Customization Service](https://landofcoder.com/magento-customization-service.html)





