<?php

namespace Dotsquares\Topmenu\Plugin\Block;

use Magento\Framework\Data\Tree\NodeFactory;

class Topmenu
{

    /**
     * @var NodeFactory
     */
    protected $nodeFactory;
    protected $dataHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Data\Tree\NodeFactory $nodeFactory
     */
    public function __construct(
        NodeFactory $nodeFactory,
       \Dotsquares\Topmenu\Helper\Data $dataHelper
      
    ) 
    {
        $this->nodeFactory = $nodeFactory;
        $this->dataHelper = $dataHelper;

      

    }

    /**
     *
     * Inject node into menu.
     **/
    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        $node = $this->nodeFactory->create(
            [
                'data' => $this->getNodeAsArray(),
                'idField' => 'id',
                'tree' => $subject->getMenu()->getTree()
            ]
        );
        $subject->getMenu()->addChild($node);
    }

    /**
     *
     * Build node
     **/
    protected function getNodeAsArray()
    {
        if($this->dataHelper->isModuleEnabled() == 1){
              $helperData = $this->dataHelper->geCustomtext();
              return [
            'name' => $helperData,
            'id' => 'dotsquares',
            'url' => '/magento/pub/dotsquares-navigation',
            'has_active' => true,
            'is_active' => true
        ];
    }


else{

    }
    }
   
}