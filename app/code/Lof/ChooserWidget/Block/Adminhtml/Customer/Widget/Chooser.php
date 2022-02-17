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
 * @package    Lof_ChooserWidget
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\ChooserWidget\Block\Adminhtml\Customer\Widget;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Chooser extends Extended
{
    /**
     * @var array
     */
    protected $_selectedProducts = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $_resourceCategory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_resourceProduct;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $groupCollectionFactory;

    /** @var CustomerRepositoryInterface */
    protected $_customerRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context                        $context                
     * @param \Magento\Backend\Helper\Data                                   $backendHelper          
     * @param \Magento\Catalog\Model\CategoryFactory                         $categoryFactory        
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory      
     * @param \Magento\Catalog\Model\ResourceModel\Category                  $resourceCategory       
     * @param \Magento\Catalog\Model\ResourceModel\Product                   $resourceProduct        
     * @param \Magento\Customer\Model\CustomerFactory                        $customerFactory        
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory  $groupCollectionFactory 
     * @param \Magento\Store\Model\System\Store                              $systemStore            
     * @param CustomerRepositoryInterface                                    $customerRepository     
     * @param array                                                          $data                   
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category $resourceCategory,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Store\Model\System\Store $systemStore,
        CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->_categoryFactory   = $categoryFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_resourceCategory  = $resourceCategory;
        $this->_resourceProduct   = $resourceProduct;
        parent::__construct($context, $backendHelper, $data);
        $this->customerFactory        = $customerFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->context                = $context;
        $this->systemStore            = $systemStore;
        $this->_customerRepository = $customerRepository;
    }

    /**
     * Block construction, prepare grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('name');
        $this->setUseAjax(true);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element Form Element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl(
            'chooserwidget/customer_widget/chooser',
            ['uniq_id' => $uniqId, 'use_massaction' => false]
        );

        $chooser = $this->getLayout()->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Chooser'
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );

        if ($customerId = $element->getValue()) {
            $customer = $this->_customerRepository->getById($customerId);
            $chooser->setLabel($customer->getEmail());
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Checkbox Check JS Callback
     *
     * @return string
     */
    public function getCheckboxCheckCallback()
    {
        if ($this->getUseMassaction()) {
            return "function (grid, element) {
                $(grid.containerId).fire('product:changed', {element: element});
            }";
        }
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        if (!$this->getUseMassaction()) {
            $chooserJsObject = $this->getId();
            return '
                function (grid, event) {
                    var trElement = Event.findElement(event, "tr");
                    var productId = trElement.down("td").innerHTML;
                    var productName = trElement.down("td").next().next().innerHTML;
                    var optionLabel = productName;
                    var optionValue = "" + productId.replace(/^\s+|\s+$/g,"");
                    if (grid.categoryId) {
                        optionValue += "/" + grid.categoryId;
                    }
                    if (grid.categoryName) {
                        optionLabel = grid.categoryName + " / " + optionLabel;
                    }
                    ' .
                $chooserJsObject .
                '.setElementValue(optionValue);
                    ' .
                $chooserJsObject .
                '.setElementLabel(optionLabel);
                    ' .
                $chooserJsObject .
                '.close();
                }
            ';
        }
    }

    /**
     * Category Tree node onClick listener js function
     *
     * @return string
     */
    public function getCategoryClickListenerJs()
    {
        $js = '
            function (node, e) {
                {jsObject}.addVarToUrl("category_id", node.attributes.id);
                {jsObject}.reload({jsObject}.url);
                {jsObject}.categoryId = node.attributes.id != "none" ? node.attributes.id : false;
                {jsObject}.categoryName = node.attributes.id != "none" ? node.text : false;
            }
        ';
        $js = str_replace('{jsObject}', $this->getJsObjectName(), $js);
        return $js;
    }

    /**
     * Filter checked/unchecked rows in grid
     *
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $selected = $this->getSelectedProducts();
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $selected]);
            } else {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $selected]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare products collection, defined collection filters (category, product type)
     *
     * @return Extended
     */
    /**
     * Prepare products collection, defined collection filters (category, product type)
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->customerFactory->create()->getCollection();
         // Needed to enable filtering on name as a whole
        $collection->addNameToSelect();
        // Needed to enable filtering based on billing address attributes
        $collection->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
            ->joinAttribute('company', 'customer_address/company', 'default_billing', null, 'left');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for products grid
     *
     * @return Extended
     */
    protected function _prepareColumns()
    {

        $this->addColumn('entity_id', [
            'header' => __('ID'),
            'index' => 'entity_id'
        ]);

        $this->addColumn('name', [
                'header' => __('Name'),
                'width'  => '100',
                'index'  => 'name'
            ]);

        $this->addColumn('email', [
                'header' => __('Email'),
                'width'  => '100',
                'index'  => 'email'
            ]);

        $groups = $this->groupCollectionFactory->create()
        ->addFieldToFilter('customer_group_id', ['gt' => 0])
        ->load()
        ->toOptionHash();
        $this->addColumn('group', [
            'header'  => __('Group'),
            'width'   => '100',
            'index'   => 'group_id',
            'type'    => 'options',
            'options' => $groups
        ]);

        $this->addColumn('billing_telephone', [
                'header' => __('Telephone'),
                'width'  => '100',
                'index'  => 'billing_telephone'
            ]);

        $this->addColumn('billing_postcode', [
                'header' => __('ZIP'),
                'width'  => '90',
                'index'  => 'billing_postcode'
            ]);

        $this->addColumn('billing_country_id', [
                'header' => __('Country'),
                'width'  => '100',
                'type'   => 'country',
                'index'  => 'billing_country_id'
            ]);

        $this->addColumn('billing_region', [
                'header' => __('State/Province'),
                'width'  => '100',
                'index'  => 'billing_region'
            ]);

        $this->addColumn('customer_since', [
                'header'    => __('Customer Since'),
                'type'      => 'datetime',
                'align'     => 'center',
                'index'     => 'created_at',
                'gmtoffset' => true
            ]);

        if(!$this->context->getStoreManager()->isSingleStoreMode()) {
            $this->addColumn('website_id', [
                'header'  => __('Website'),
                'align'   => 'center',
                'width'   => '80px',
                'type'    => 'options',
                'options' => $this->systemStore->getWebsiteOptionHash(true),
                'index'   => 'website_id',
                ]);
        }

        return parent::_prepareColumns();
    }

    /**
     * Adds additional parameter to URL for loading only products grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'chooserwidget/customer_widget/chooser',
            [
                'products_grid'   => true,
                '_current'        => true,
                'uniq_id'         => $this->getId()
            ]
        );
    }

    /**
     * Setter
     *
     * @param array $selectedProducts
     * @return $this
     */
    public function setSelectedProducts($selectedProducts)
    {
        $this->_selectedProducts = $selectedProducts;
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        if ($selectedProducts = $this->getRequest()->getParam('selected_products', null)) {
            $this->setSelectedProducts($selectedProducts);
        }
        return $this->_selectedProducts;
    }
}