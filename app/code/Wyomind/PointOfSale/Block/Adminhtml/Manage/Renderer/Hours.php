<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block\Adminhtml\Manage\Renderer;

/**
 * Class Hours
 * @package Wyomind\PointOfSale\Block\Adminhtml\Manage\Renderer
 */
class Hours extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * Retrieve allow attributes
     *
     * @return array
     */
    public function getHtmlAttributes()
    {
        return ['type', 'name', 'class', 'style', 'checked', 'onclick', 'onchange', 'disabled'];
    }

    /**
     * Prepare value list
     *
     * @return array
     */
    protected function _prepareValues()
    {
        $values = [
            [
                'value' => "Monday",
                'label' => __('Monday')
            ],
            [
                'value' => "Tuesday",
                'label' => __('Tuesday')
            ],
            [
                'value' => "Wednesday",
                'label' => __('Wednesday')
            ],
            [
                'value' => "Thursday",
                'label' => __('Thursday')
            ],
            [
                'value' => "Friday",
                'label' => __('Friday')
            ],
            [
                'value' => "Saturday",
                'label' => __('Saturday')
            ],
            [
                'value' => "Sunday",
                'label' => __('Sunday')
            ]
        ];

        return $values;
    }

    /**
     * Retrieve HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $values = $this->_prepareValues();

        if (!$values) {
            return '';
        }
        $id = $this->getHtmlId();

        $html= "<script>
            require(['jquery', 'pos_edit'], function ($, pointofsale) {
                'use strict';
                var elementId = '" . $id . "';
                    
                setTimeout(pointofsale.initializeGMap, 5000);
                
                // initialize hours
                pointofsale.initializeHours(elementId);
                
                $(document).on('click', '." . $id . "_day', function() {
                    pointofsale.activeField(this, elementId);
                });
                
                $(document).on('click', '." . $id . "_lunch', function() {
                    pointofsale.activeFieldLunch(this, elementId);
                });
                
                $(document).on('change', '.hours_summary', function() {
                    pointofsale.summary(elementId);
                });
            });
        </script>";

        $html .=  '<ul class="checkboxes">';

        foreach ($values as $day) {
            $html .= '<li style="display:inline-block;width:300px;float:left">';
            $html .= '<label class="data-grid-checkbox-cell-inner">'
                . '<input value="' . $day['value'] . '" '
                . 'class="' . $id . '_day admin__control-checkbox" '
                . 'id="' . $day['value'] . '" '
                . 'type="checkbox" '
                . 'value="' . $day['value'] . '" />'
                . '<label for="' . $day['value'] . '">&nbsp;<b>' . $day['label'] . '</b></label>'
                . '</label>';

            $html .= "<div style='margin:4px 0 2px 35px;'> <select style='width:100px;' id='" . $day['value'] . "_open' class='hours_summary'>";
            for ($h = 0; $h <= 24; $h++) {
                for ($m = 0; $m < 60; $m = $m + 15) {
                    $html .= "<option value='" . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT) . "'>"
                        . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT)
                        . "</option>";
                    if ($h == 24) {
                        break;
                    }
                }
            }
            $html .= "</select> - ";
            $html .= "<select style='width:100px;' id='" . $day['value'] . "_close' class='hours_summary'>";
            for ($h = 0; $h <= 24; $h++) {
                $selected = ($h == 24) ? "selected " : "";
                for ($m = 0; $m < 60; $m = $m + 15) {
                    $html .= "<option " . $selected . "value='" . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT) . "'>"
                        . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT)
                        . "</option>";
                    if ($h == 24) {
                        break;
                    }
                }
            }
            $html .= "</select></div>";
            $html .= '</li>';

            $html .= '<li style="display:inline-block;width:300px;float:left">';
            $html .= '<label class="data-grid-checkbox-cell-inner">'
                . '<input value="' . $day['value'] . '" '
                . 'class="' . $id . '_lunch admin__control-checkbox" '
                . 'id="' . $day['value'] . '_lunch" '
                . 'type="checkbox" '
                . 'value="' . $day['value'] . '_lunch" />'
                . '<label for="' . $day['value'] . '_lunch">&nbsp;<b>' . __("Lunch hours") . '</b></label>'
                . '</label>';

            $html .= "<div style='margin:4px 0 2px 35px;'> <select style='width:100px;' id='" . $day['value'] . "_lunch_open' class='hours_summary'>";
            for ($h = 0; $h <= 24; $h++) {
                for ($m = 0; $m < 60; $m = $m + 15) {
                    $html .= "<option value='" . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT) . "'>"
                        . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT)
                        . "</option>";
                    if ($h == 24) {
                        break;
                    }
                }
            }
            $html .= "</select> - ";
            $html .= "<select style='width:100px;' id='" . $day['value'] . "_lunch_close' class='hours_summary'>";
            for ($h = 0; $h <= 24; $h++) {
                $selected = ($h == 24) ? "selected " : "";
                for ($m = 0; $m < 60; $m = $m + 15) {
                    $html .= "<option " . $selected . "value='" . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT) . "'>"
                        . str_pad($h, 2, 0, STR_PAD_LEFT) . ':' . str_pad($m, 2, 0, STR_PAD_LEFT)
                        . "</option>";
                    if ($h == 24) {
                        break;
                    }
                }
            }
            $html .= '</select></div>';
            $html .= '</li>';
        }
        $html .= '</ul>' . $this->getAfterElementHtml();

        return $html;
    }
}