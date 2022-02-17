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

class UnsubscribePriceAllManagement implements \Lof\ProductNotification\Api\UnsubscribePriceAllManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Lof\ProductNotification\Model\PriceFactory
     */
    protected $priceFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Lof\ProductNotification\Model\PriceFactory $priceFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Lof\ProductNotification\Model\PriceFactory $priceFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
        ) {
        $this->productRepository = $productRepository;
        $this->priceFactory      = $priceFactory;
        $this->storeManager      = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function postUnsubscribePriceAll(\Lof\ProductNotification\Api\Data\UnsubscribeRequestInterface $param)
    {
        $message = '';
        try {
            $token = $param->getToken();
            $id = $param->getId();
            $aid = $param->getAid();
            $email = $param->getEmail();
            if (!$param->getToken() || !$param->getId()) {
                throw new CouldNotSaveException(
                    __('Could unsubscribe Price because missing params: id or token.')
                );
                return __("Could not unsubscribe price.");
            }
            /** @var int $website_id */
            $website_id = $param->getWebsiteId();
            $website_id = $website_id?(int)$website_id:$this->storeManager->getStore()->getWebsiteId();
            /** @var \Lof\ProductNotification\Model\Price $price */
            $price = $this->priceFactory->create()->load((int)$id);
            if(!$price->getId()){
                throw new CouldNotSaveException(
                    __('Could unsubscribe Price because it is not exists.')
                );
                return __("Could not unsubscribe price."); 
            }
            if ($price->getToken() != $token) {
                throw new CouldNotSaveException(
                    __('Could unsubscribe Price because different token.')
                );
                return __("Could not unsubscribe price.");
            }
            /** @var aid */
            if($aid){
                $price_aid = md5((int)$price->getId() . $price->getSubscriberEmail());
                if ($price_aid != $aid) {
                    throw new CouldNotSaveException(
                        __('Could unsubscribe Price because different aid.')
                    );
                    return __("Could not unsubscribe price.");
                }
            }else {
                if($email != $price->getSubscriberEmail()){
                    throw new CouldNotSaveException(
                        __('Could unsubscribe Price because different subscriber email address.')
                    );
                    return __("Could not unsubscribe price.");
                }
            }
            /** @var \Lof\ProductNotification\Model\Price $priceModel */
            $priceModel = $this->priceFactory->create();

            if ($price->getCustomerId()) { //Delete all of customer id
                $priceModel->deleteCustomer($price->getCustomerId(),$website_id);
            } else { //Delete all of guest email address
                $priceModel->deleteGuest($price->getSubscriberEmail(),$website_id);
            }
            $message = "You will no longer receive price alert for this product.";
            
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not unsubscribe the price subscription: %1', $exception->getMessage()),
                $exception
            );
        }
        return $message;
    }
}
