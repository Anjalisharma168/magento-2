<?php
namespace Dotsquares\Productname\Plugin\Catalog\Model;
class Product
{
    public function afterGetName(\Magento\Catalog\Model\Product $subject, $result)
    {
        // $title = $subject->getAttributeText(''); // call any custom attr like this of current product
        $title = "hello";
        return $title." - ". $result . ' MODIFIED BY PREFERENCE';
    }
}