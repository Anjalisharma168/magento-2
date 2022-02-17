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

class ModuleConfigManagement implements \Lof\ProductNotification\Api\ModuleConfigManagementInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

	public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	) {
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
	}
    /**
     * {@inheritdoc}
     */
    public function getModuleConfig($storeId)
    {
        if($storeId == "all"){
            $storeId = 0;
        }else {
            $storeId = (int)$storeId;
        }
        $store = $this->_storeManager->getStore($storeId);
        $result = [];
        $field_config = ['general_settings/enable',
        'productalert/allow_price',
        'productalert/disable_price_guest',
        'productalert/enable_price',
        'productalert/allow_stock',
        'productalert/allow_new_product',
        'productalert/send_email_one_time'
        ];
        foreach($field_config as $key){
            $value = $this->scopeConfig->getValue(
                'productnotification/' . $key,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store);
            $result[$key] = $value; 
        }
       
        return json_encode($result);
    }

}
