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

namespace Lof\ChooserWidget\Helper;

class Chooser extends \Magento\Framework\App\Helper\AbstractHelper
{

    const PRODUCT_CHOOSER_BLOCK_ALIAS     = 'Lof\ChooserWidget\Block\Adminhtml\Product\Widget\Chooser';
    const CATEGORY_CHOOSER_BLOCK_ALIAS    = 'adminhtml/catalog_category_widget_chooser';
    const CMS_PAGE_CHOOSER_BLOCK_ALIAS    = 'adminhtml/cms_page_widget_chooser';
    const CMS_BLOCK_CHOOSER_BLOCK_ALIAS   = 'adminhtml/cms_block_widget_chooser';
    const CUSTOMER_CHOOSER_BLOCK_ALIAS    = 'Lof\ChooserWidget\Block\Adminhtml\Customer\Widget\Chooser';
    const XML_PATH_DEFAULT_CHOOSER_CONFIG = 'productnotification/chooser_defaults';

    protected $_requiredConfigValues = array('input_name');

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @param \Magento\Framework\App\Helper\Context      $context      
     * @param \Magento\Framework\View\LayoutInterface    $layout       
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager 
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Store\Model\StoreManagerInterface $storeManager
        ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_layout           = $layout;
    }

    /**
     * Wrapper function, that creates product chooser button in the
     * generic Mage Admin forms
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    public function createProductChooser($dataModel, $fieldset, $config)
    {
        $blockAlias = self::PRODUCT_CHOOSER_BLOCK_ALIAS;
        $this->_prepareChooser($dataModel, $fieldset, $config, $blockAlias);
        return $this;
    }

    /**
     * Wrapper function, that creates category chooser button in the
     * generic Mage Admin forms
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    public function createCategoryChooser($dataModel, $fieldset, $config)
    {
        $blockAlias = self::CATEGORY_CHOOSER_BLOCK_ALIAS;
        $this->_prepareChooser($dataModel, $fieldset, $config, $blockAlias);
        return $this;
    }

    /**
     * Wrapper function, that creates cms page chooser button in the
     * generic Mage Admin forms
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    public function createCmsPageChooser($dataModel, $fieldset, $config)
    {
        $blockAlias = self::CMS_PAGE_CHOOSER_BLOCK_ALIAS;
        $this->_prepareChooser($dataModel, $fieldset, $config, $blockAlias);
        return $this;
    }

    /**
     * Wrapper function, that creates cms block chooser button in the
     * generic Mage Admin forms
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    public function createCmsBlockChooser($dataModel, $fieldset, $config)
    {
        $blockAlias = self::CMS_BLOCK_CHOOSER_BLOCK_ALIAS;
        $this->_prepareChooser($dataModel, $fieldset, $config, $blockAlias);
        return $this;
    }

    /**
     * Wrapper function, that creates product chooser button in the
     * generic Mage Admin forms
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    public function createCustomerChooser($dataModel, $fieldset, $config)
    {
        $blockAlias = self::CUSTOMER_CHOOSER_BLOCK_ALIAS;
        $this->_prepareChooser($dataModel, $fieldset, $config, $blockAlias);
        return $this;
    }

    /**
     * Wrapper function, that creates custom chooser button in the
     * generic Mage Admin forms
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     * @param string $blockAlias
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    public function createChooser($dataModel, $fieldset, $config, $blockAlias)
    {
        $this->_prepareChooser($dataModel, $fieldset, $config, $blockAlias);
        return $this;
    }

    /**
     * This function is actually some kind of workaround how to create
     * a chooser and to reuse the product chooser widget.
     *
     * Most of the code was created after some reverse engineering of these 2 classes:
     *  - Mage_Widget_Block_Adminhtml_Widget_Options
     *  - Mage_Adminhtml_Block_Catalog_Product_Widget_Chooser
     *
     * So there are interesting ideas of the Magento Core Team in these 2 classes:
     *  - Mage_Widget_Block_Adminhtml_Widget_Options
     *  -- Here they extend Mage_Adminhtml_Block_Widget_Form and do some tricks in:
     *  --- _prepareForm
     *  --- addFields and _addField
     *
     *  - Mage_Adminhtml_Block_Catalog_Product_Widget_Chooser
     *  -- Here they attach the chooser html in the property after_element_html
     *  -- also they add some js methods, that control the behaviour of the chooser button
     *     and the the behaviour of the products grid that appear after the the button is pressed.
     *
     * The ideas in the both classes are interesting and this is a good example how we
     * can reuse core components.
     *
     * !!! The best solution would be to create our class that extends
     * Mage_Adminhtml_Block_Widget_Form and to do similar tricks that they do in Mage_Widget_Block_Adminhtml_Widget_Options
     * So we can reuse this class for the forms, that we need different kind of choosers.
     * Right now we can't reuse their Mage_Widget_Block_Adminhtml_Widget_Options, because there
     * are too many dependencies of the widget config and this class can't be reused easy out of the widget context.
     *
     * Also it was needed to include some extra JS files by layout update: <update handle="editor"/>
     * In favour to fire the choose grid after the choose button is pressed.
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     * @param string $blockAlias
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    protected function _prepareChooser($dataModel, $fieldset, $config, $blockAlias)
    {
        $this->_checkRequiredConfigs($config)
                ->_populateMissingConfigValues($config, $blockAlias);

        $chooserConfigData = $this->_prepareChooserConfig($config, $blockAlias);
        $chooserBlock = $this->_layout->createBlock($blockAlias, '', $chooserConfigData);

        $element = $this->_createFormElement($dataModel, $fieldset, $config);

        $fieldset->getForm()->setHtmlIdPrefix('');

        $chooserBlock
            ->setConfig($chooserConfigData)
            ->setFieldsetId($fieldset->getId())
            ->prepareElementHtml($element);

        //$this->_fixChooserAjaxUrl($element);

        return $this;
    }

    /**
     * Checks if all required config values are in the config array
     * Basically there values are critical for the normal work of the extension
     * If we don't have them, then for e.g. we can't pass the data, that we need to save
     * after form submit.
     *
     * We throw exception if at least on required config values is missing
     *
     * @param array $config
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     * @throws Exception
     */
    protected function _checkRequiredConfigs($config)
    {
        foreach($this->_requiredConfigValues as $value) {
            if(!isset($config[$value])) {
                throw new Exception("Required input config value \"" . $value . "\" is missing.");
            }
        }

        return $this;
    }

    /**
     * Inspects the config array and populate missing not mandatory values
     * with the predefined default values
     *
     * @param array $config
     * @param string $blockAlias
     *
     * @return Jarlssen_ChooserWidget_Helper_Chooser
     */
    protected function _populateMissingConfigValues(&$config, $blockAlias)
    {
        $currentWidgetKey = str_replace('adminhtml/', '',$blockAlias);
        $store = $this->_storeManager->getStore();

        $chooserDefaults = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_CHOOSER_CONFIG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);

        if(!isset($chooserDefaults[$currentWidgetKey])) {
            $currentWidgetKey = 'default';
        }

        foreach($chooserDefaults[$currentWidgetKey] as $configKey => $value) {
            if(!isset($config[$configKey])) {
                $config[$configKey] = $value;
            }
        }

        return $this;
    }

    /**
     * Creates label form element and sets empty value of
     * the hidden input, that is created, when we have form element
     * from type label
     *
     * @param Mage_Core_Model_Abstract $dataModel
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param array $config
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    protected function _createFormElement($dataModel, $fieldset, $config)
    {
        $isRequired = (isset($config['required']) && true === $config['required']) ? true : false;

        $inputConfig = array(
            'name'  => $config['input_name'],
            'label' => $config['input_label'],
            'required' => $isRequired
        );

        if (!isset($config['input_id'])) {
            $config['input_id'] = $config['input_name'];
        }

        $element = $fieldset->addField($config['input_id'], 'label', $inputConfig);
        $element->setValue($dataModel->getData($element->getId()));
        $dataModel->setData($element->getId(),'');

        return $element;
    }

    /**
     * Prepare config in format, that is needed for the chooser "factory"
     *
     * @param array $config
     * @param string $blockAlias
     *
     * @return array
     */
    protected function _prepareChooserConfig($config, $blockAlias)
    {
        return array(
            'button' =>
                array(
                    'open' => $config['button_text'],
                    'type' => $blockAlias
                )
        );
    }
}
