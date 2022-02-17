<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_ProductNotification
 * @copyright  Copyright (c) 2020 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\ProductNotification\Api\Data;
interface StockSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get ProductNotification list.
     * @return \Lof\ProductNotification\Api\Data\StockInterface[]
     */
    public function getItems();
    /**
     * Set ProductNotification list.
     * @param \Lof\ProductNotification\Api\Data\StockInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}