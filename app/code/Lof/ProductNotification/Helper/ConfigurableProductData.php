<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_ProductNotification
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\ProductNotification\Helper;

class ConfigurableProductData extends \Magento\ConfigurableProduct\Helper\Data
{
    public function getOptions($currentProduct, $allowedProducts)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $stockRegistry = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
        $options = [];
        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            $product = $objectManager->get('Magento\Catalog\Model\Product')->load($productId);
            $stockitem = $stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
            if($stockitem->getQty() == 0) continue;
            $images = $this->getGalleryImages($product);
            if ($images) {
                foreach ($images as $image) {
                    $options['images'][$productId][] =
                        [
                            'thumb'    => $image->getData('small_image_url'),
                            'img'      => $image->getData('medium_image_url'),
                            'full'     => $image->getData('large_image_url'),
                            'caption'  => $image->getLabel(),
                            'position' => $image->getPosition(),
                            'isMain'   => $image->getFile() == $product->getImage()
                        ];
                }
            }
            foreach ($this->getAllowAttributes($currentProduct) as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                $options[$productAttributeId][$attributeValue][] = $productId;
                $options['index'][$productId][$productAttributeId] = $attributeValue;
            }
        }
        return $options;
    }
}