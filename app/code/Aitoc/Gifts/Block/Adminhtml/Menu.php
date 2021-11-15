<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Block\Adminhtml;

class Menu extends \Magento\Backend\Block\Template
{
    /**
     * @var null|array
     */
    protected $items = null;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Aitoc_Gifts::menu.phtml';

    /**
     * @var \Aitoc\Gifts\Model\Pool\Action
     */
    private $actionPool;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Aitoc\Gifts\Model\Pool\Action $actionPool,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->actionPool = $actionPool;
    }

    /**
     * @return mixed
     */
    public function getButtonHtml()
    {
        $result = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button\SplitButton::class
        )->setData(
            [
                'label' => __('Create Rule'),
                'class' => 'add',
                'type' => 'button',
                'id' => 'promotion_action_create',
                'options' => $this->getActionListOptions(),
            ])->toHtml();

        return $result;
    }

    /**
     * @param $action
     *
     * @return string
     */
    public function getCreateActionUrl($action = '')
    {
        return $this->getUrl(
            '*/rule/edit',
            ['action' => $action]
        );
    }

    /**
     * @return array
     */
    protected function getActionListOptions()
    {
        $resultActionOptions = [];
        foreach ($this->actionPool->getActions() as $code => $item) {
            $resultActionOptions[$code] = [
                'label' => __( 'Create %1 Rule', $item['title']),
                'onclick' => "setLocation('" . $this->getCreateActionUrl($code) . "')",
                'default' => $code == 'promotion_group'
            ];
        }

        return $resultActionOptions;
    }

    /**
     * @return array|null
     */
    public function getItems()
    {
        if ($this->items === null) {
            $items = [
                'promotions' => [
                    'title' => __('Promotional Rules'),
                    'url' => $this->getUrl('*/promotions/index'),
                    'resource' => 'Aitoc_Gifts::promotions'
                ],
                'totals' => [
                    'title' => __('Statistics'),
                    'url' => $this->getUrl('*/promotions/total'),
                    'resource' => 'Aitoc_Gifts::statistic'
                ],
                'system_config' => [
                    'title' => __('Settings'),
                    'url' => $this->getUrl('adminhtml/system_config/edit',
                        ['section' => 'aitoc_gifts'])
                ],
                'readme' => [
                    'title' => __('Guide'),
                    'url' => 'https://www.aitoc.com/docs/guides/free-gift.html',
                    'attr' => [
                        'target' => '_blank'
                    ],
                    'separator' => true
                ]
            ];

            $this->items = $items;
        }

        return $this->items;
    }

    /**
     * @return array
     */
    public function getCurrentItem()
    {
        $items = $this->getItems();
        $controllerName = $this->getRequest()->getControllerName();

        if (array_key_exists($controllerName, $items)) {
            return $items[$controllerName];
        }

        return $items['promotions'];
    }

    /**
     * @param array $item
     * @return string
     */
    public function renderAttributes(array $item)
    {
        $result = '';
        if (isset($item['attr'])) {
            foreach ($item['attr'] as $attrName => $attrValue) {
                $result .= sprintf(' %s=\'%s\'', $attrName, $attrValue);
            }
        }
        return $result;
    }

    /**
     * @param $itemIndex
     * @return bool
     */
    public function isCurrent($itemIndex)
    {
        return $itemIndex == $this->getRequest()->getControllerName();
    }
}
