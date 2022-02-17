<?php
namespace Dotsquare\Changetitle\Plugin\Model\Catalog;



class Product
{

	protected $helperData;

	public function __construct(
		
		\Dotsquare\Changetitle\Helper\Data $helperData

	)
	{
		$this->helperData = $helperData;
	}

    public function afterGetName(\Magento\Catalog\Model\Product $subject, $result)
    {
        // $title = $subject->getAttributeText(''); // call any custom attr like this of current product
    	if($this->helperData->isModuleEnabled() == 1){
    		$customtext = $this->helperData->geCustomtext();
        return $customtext." - ". $result;
    }
    else{
    	return  $result ;	
    }
    }
}