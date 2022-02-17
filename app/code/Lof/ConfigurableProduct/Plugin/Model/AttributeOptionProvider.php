<?php


namespace Lof\ConfigurableProduct\Plugin\Model;
class AttributeOptionProvider
{

    public function afterGetAttributeOptions(\Magento\ConfigurableProduct\Model\AttributeOptionProvider $subject, array $result)
    {
        $optiondata=array();

        foreach ($result as $option) {  

            if(isset($option['stock_status']) && $option['stock_status']==0){
                $option['option_title']  = $option['option_title'].__(' - out of stock');
            }
            $optiondata[]=$option;
        }
        return $optiondata;
    }
}