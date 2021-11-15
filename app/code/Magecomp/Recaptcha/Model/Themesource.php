<?php
namespace Magecomp\Recaptcha\Model;
class Themesource implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'light', 'label' => __('Light')],
            ['value' => 'dark', 'label' => __('Dark')]
			
        ];
    }
}
