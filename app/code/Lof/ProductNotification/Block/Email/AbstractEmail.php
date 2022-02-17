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

namespace Lof\ProductNotification\Block\Email;

use Magento\Framework\Pricing\PriceCurrencyInterface;

abstract class AbstractEmail extends \Magento\Framework\View\Element\Template
{
    /**
     * Product collection array
     *
     * @var array
     */
    protected $_products = [];

    /**
     * Current Store scope object
     *
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * @var \Magento\Framework\Filter\Input\MaliciousCode
     */
    protected $_maliciousCode;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageBuilder;

    /**
     * @var \Magento\Framework\Url
     */
    protected $frontUrlModel;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \Magento\Framework\Url $frontUrlModel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Url $frontUrlModel,
        array $data = []
    ) {
        $this->imageBuilder = $imageBuilder;
        $this->priceCurrency = $priceCurrency;
        $this->_maliciousCode = $maliciousCode;
        $this->frontUrlModel = $frontUrlModel;
        parent::__construct($context, $data);
    }

    /**
    * Filter malicious code before insert content to email
    *
    * @param  string|array $content
    * @return string|array
    */
    public function getFilteredContent($content)
    {
        return $this->_maliciousCode->filter($content);
    }

    /**
     * Set Store scope
     *
     * @param int|string|\Magento\Store\Model\Website|\Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        if ($store instanceof \Magento\Store\Model\Website) {
            $store = $store->getDefaultStore();
        }
        if (!$store instanceof \Magento\Store\Model\Store) {
            $store = $this->_storeManager->getStore($store);
        }

        $this->_store = $store;

        return $this;
    }

    /**
     * Retrieve current store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            $this->_store = $this->_storeManager->getStore();
        }
        return $this->_store;
    }

    /**
     * Convert price from default currency to current currency
     *
     * @param float $price
     * @param boolean $format             Format price to currency format
     * @param boolean $includeContainer   Enclose into <span class="price"><span>
     * @return float
     */
    public function formatPrice($price, $format = true, $includeContainer = true)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat($price, $includeContainer)
            : $this->priceCurrency->convert($price);
    }

    /**
     * Reset product collection
     *
     * @return void
     */
    public function reset()
    {
        $this->_products = [];
    }

    /**
     * Add product to collection
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function addProduct(\Magento\Catalog\Model\Product $product)
    {
        $product->setStore($this->getStore());
        $product->setStoreId($this->getStore()->getId());
        $this->_products[$product->getId()] = $product;
    }

    /**
     * Retrieve product collection array
     *
     * @return mixed
     */
    public function getProducts()
    {
        return $this->_products;
    }

    /**
     * Get store url params
     *
     * @return mixed
     */
    protected function _getUrlParams()
    {
        $alert = $this->getAlert();
        return [
            '_scope'        => $this->getStore(),
            '_scope_to_url' => true,
            'store'         => $this->getStore()->getId(),
            'token'         => $alert->getToken(),
            'id'            => $alert->getId(),
            'aid'           => md5($alert->getId() . $alert->getSubscriberEmail()),
            '_nosid' => true
        ];
    }

    /**
     * @return \Magento\Framework\Pricing\Render
     */
    protected function getPriceRender()
    {
        return $this->_layout->createBlock(
            'Magento\Framework\Pricing\Render',
            '',
            ['data' => ['price_render_handle' => 'catalog_product_prices']]
        );
    }

    /**
     * Return HTML block with tier price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getPriceRender();
        $price = '';

        if ($priceRender) {
            $price = $priceRender->render(
                $priceType,
                $product,
                $arguments
            );
        }
        return $price;
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    /**
     * get frontend url
     * 
     * @param string $url
     * @param array|mixed|null $params
     * @return string
     */
    public function getFrontendUrl ($url, $params = []) 
    {
        return $this->frontUrlModel->getUrl($url, $params);
    }
}
