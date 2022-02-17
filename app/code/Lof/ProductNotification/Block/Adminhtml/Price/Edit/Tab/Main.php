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

namespace Lof\ProductNotification\Block\Adminhtml\Price\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;

    /**
     * @var array
     */
    protected $_drawLevel;

    /**
     * @var \Lof\Faq\Model\ResourceModel\Category\Collection
     */
    protected $_categoryCollection;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        \Lof\ChooserWidget\Helper\Chooser $chooserHelper,
        \Lof\ProductNotification\Helper\Data $helperData,
        \Lof\ProductNotification\Model\Config\Source\Website $storeWebsite,
        array $data = []
    ) {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        $this->_systemStore      = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->chooserHelper = $chooserHelper;
        $this->helperData    = $helperData;
        $this->storeWebsite  = $storeWebsite;
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('productnotification_price');

        if ($this->_isAllowedAction('Lof_ProductNotification::price_edit')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('question_');

         $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getAlertPriceId()) {
            $fieldset->addField('alert_price_id', 'hidden', ['name' => 'alert_price_id']);
        }

        $productConfig = [
            'input_name'  => 'product_id',
            'input_label' => __('Product'),
            'button_text' => __('Select Product...')
        ];
        $this->chooserHelper->createProductChooser($model, $fieldset, $productConfig);

        $productConfig = [
            'input_name'  => 'customer_id',
            'input_label' => __('Customer'),
            'button_text' => __('Select Customer...')
        ];
        $this->chooserHelper->createCustomerChooser($model, $fieldset, $productConfig);

        $fieldset->addField(
            'subscriber_email',
            'text',
            [
                'name'     => 'subscriber_email',
                'label'    => __('Subscriber Email'),
                'title'    => __('Subscriber Email'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'subscriber_name',
            'text',
            [
                'name'     => 'subscriber_name',
                'label'    => __('Subscriber Name'),
                'title'    => __('Email'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'message',
            'textarea',
            [
                'name'     => 'message',
                'label'    => __('Message'),
                'title'    => __('Message'),
                'style'    => 'height:20em',
                'disabled' => $isElementDisabled
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField( 'add_date', 
            'date', 
            [ 
                'label'       => __('Subscribed On'),
                'title'       => __('Subscribed On'),
                'name'        => 'add_date',
                'date_format' => $dateFormat,
                'disabled'    => $isElementDisabled
            ]
        );

        $fieldset->addField( 'last_send_date', 
            'date', 
            [ 
                'label'       => __('Last Sent Date'),
                'title'       => __('Last Sent Date'),
                'name'        => 'last_send_date',
                'date_format' => $dateFormat,
                'disabled'    => $isElementDisabled
            ]
        );

        if ($model->getId()) {
            $fieldset->addField( 'send_count', 
                'note', 
                [ 
                    'label'    => __('Send Count'),
                    'title'    => __('Send Count'),
                    'text'     => $model->getSendCount(),
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField( 'price', 
                'note', 
                [ 
                    'label'    => __('Subscribed Product Price'),
                    'title'    => __('Subscribed Product Price'),
                    'text'     => $model->getPrice(),
                    'disabled' => $isElementDisabled
                ]
            );
        }

        $field = $fieldset->addField(
            'website_id',
            'select',
            [
                'name'     => 'website_id',
                'label'    => __('Website'),
                'title'    => __('Website'),
                'values'   => $this->storeWebsite->toOptionArray(),
                'style'    => 'min-width: 160px;',
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name'  => 'status',
                'options' => [
                    '1' => __('Enabled'),
                    '0' => __('Disabled')
                ],
                'style'    => 'width: 160px;',
                'disabled' => $isElementDisabled
            ]
        );

        if (!$model->getId()) {
            $model->setData('status', '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('General');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
