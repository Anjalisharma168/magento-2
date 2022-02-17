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

namespace Lof\ProductNotification\Model\Api;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class UnsubscribeStockManagement implements \Lof\ProductNotification\Api\UnsubscribeStockManagementInterface
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Lof\ProductNotification\Model\StockFactory
     */
    protected $stockFactory;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Lof\ProductNotification\Model\StockFactory $stockFactory
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Lof\ProductNotification\Model\StockFactory $stockFactory
        ) {
        $this->productRepository = $productRepository;
        $this->stockFactory = $stockFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function postUnsubscribeStock(\Lof\ProductNotification\Api\Data\UnsubscribeRequestInterface $param)
    {
        $message = "";
        try {
            $token = $param->getToken();
            $id = $param->getId();
            $aid = $param->getAid();
            $email = $param->getEmail();
            if (!$param->getToken() || !$param->getId()) {
                throw new CouldNotSaveException(
                    __('Could unsubscribe Stock because missing params: id or token.')
                );
                return __("Could not unsubscribe stock.");
            }
            /** @var \Lof\ProductNotification\Model\Stock $stock */
            $stock = $this->stockFactory->create()->load((int)$id);
            if(!$stock->getId()){
                throw new CouldNotSaveException(
                    __('Could unsubscribe Stock because it is not exists.')
                );
                return __("Could not unsubscribe price."); 
            }
            if ($stock->getToken() != $token) {
                throw new CouldNotSaveException(
                    __('Could unsubscribe Stock because different token.')
                );
                return __("Could not unsubscribe stock.");
            }
            /** @var aid */
            if($aid){
                $stock_aid = md5($stock->getId() . $stock->getSubscriberEmail());
                if ($stock_aid != $aid) {
                    throw new CouldNotSaveException(
                        __('Could unsubscribe Stock because different aid.')
                    );
                    return __("Could not unsubscribe stock.");
                }
            }else {
                if($email != $stock->getSubscriberEmail()){
                    throw new CouldNotSaveException(
                        __('Could unsubscribe Stock because different subscriber email address.')
                    );
                    return __("Could not unsubscribe stock.");
                }
            }
            $productId = $stock->getProductId();
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            if (!$productId) {
                throw new CouldNotSaveException(
                    __('Could unsubscribe Stock because empty product Id.')
                );
                return __("Could not unsubscribe stock.");
            }
            $stock->delete();
            $message = "You will no longer receive stock alert for this product.";
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not unsubscribe the stock subscription: %1', $exception->getMessage()),
                $exception
            );
        }
        return $message;
    }
}
