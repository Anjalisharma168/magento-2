<?php
/**
 * Copyright (c) 2019 Landofcoder
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Lof\ProductNotification\Api;

interface CustomerManagementInterface
{

    /**
     * Retrieve Subsribe Price
     * @param string $customerId
     * @param string $subscribePriceId
     * @return \Lof\ProductNotification\Api\Data\PriceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPriceById($customerId, $subscribePriceId);

    /**
     * Retrieve Subsribe Stock
     * @param string $customerId
     * @param string $subscribeStockId
     * @return \Lof\ProductNotification\Api\Data\StockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStockById($customerId, $subscribeStockId);

    /**
     * GET for list price subscription api
     * @param int $customerId
     * @return mixed
     */
    /**
     * Retrieve Subsribe Price matching the specified criteria.
     * @param int $customerId
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\ProductNotification\Api\Data\PriceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListPrice($customerId, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * GET for list stock subscription api
     * @param int $customerId
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\ProductNotification\Api\Data\StockSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListStock($customerId, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);


    /**
     * DELETE for list price subscription api
     * @param int $customerId
     * @param int $subscribePriceId
     * @return mixed
     */
    public function deletePrice($customerId, $subscribePriceId);

    /**
     * DELETE for list stock subscription api
     * @param int $customerId
     * @param int $subscribeStockId
     * @return mixed
     */
    public function deleteStock($customerId, $subscribeStockId);
}
