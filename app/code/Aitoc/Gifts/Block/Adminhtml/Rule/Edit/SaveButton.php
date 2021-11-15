<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Block\Adminhtml\Rule\Edit;

use Magento\Ui\Component\Control\Container;

class SaveButton extends GenericButton
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'aitoc_gifts_rule_form.aitoc_gifts_rule_form',
                                'actionName' => 'save',
                                'params' => [
                                    false
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'class_name' => Container::SPLIT_BUTTON,
            'options' => $this->getOptions(),
        ];
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    protected function getOptions()
    {
        $options[] = [
            'id_hard' => 'save_and_continue1',
            'label' => __('Save & Continue'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'aitoc_gifts_rule_form.aitoc_gifts_rule_form',
                                'actionName' => 'save',
                                'params' => [true, ['back' => 'continue']]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $options[] = [
            'id_hard' => 'save_and_duplicate',
            'label' => __('Save & Duplicate'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'aitoc_gifts_rule_form.aitoc_gifts_rule_form',
                                'actionName' => 'save',
                                'params' => [true, ['back' => 'duplicate']]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $options[] = [
            'id_hard' => 'save_and_new',
            'label' => __('Save & New'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'aitoc_gifts_rule_form.aitoc_gifts_rule_form',
                                'actionName' => 'save',
                                'params' => [true, ['back' => 'new']]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        return $options;
    }
}
