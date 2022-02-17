<?php

namespace Dotsquare\Changetitle\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

	const MODULE_ENABLE = "subtitle/general/enable";
	const MODULE_DISPLAY = "subtitle/general/display_text";


	
	 public function getDefaultConfig($path)
   {
       return $this->scopeConfig->getValue($path, \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
   }

   public function isModuleEnabled()
   {
       return (bool) $this->getDefaultConfig(self::MODULE_ENABLE);
   }
   public function geCustomtext()
   {
       return $this->getDefaultConfig(self::MODULE_DISPLAY);
   }
}