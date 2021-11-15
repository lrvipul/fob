<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 02/04/2020
 * Time: 17:35
 */

namespace Wyomind\PickupAtStore\Block\Adminhtml\Manage\Edit\Tab;


class CalendarColor extends \Magento\Framework\Data\Form\Element\AbstractElement
{

    public function getElementHtml()
    {
        $html = parent::getElementHtml();
        $value = $this->getData('value');


        $html .= '<script type="text/javascript">
            require(["jquery", "jquery/colorpicker/js/colorpicker"], function ($) {
                $(document).ready(function () {
                    var $el = $("#' . $this->getHtmlId() . '");
                    $el.css("backgroundColor", "'. $value .'");
                    $el.ColorPicker({
                        color: "'. $value .'",
                        onChange: function (hsb, hex, rgb) {
                            $el.css("backgroundColor", "#" + hex).val("#" + hex);
                        }
                    });
                });
            });
            </script>';
        return $html;

    }
}