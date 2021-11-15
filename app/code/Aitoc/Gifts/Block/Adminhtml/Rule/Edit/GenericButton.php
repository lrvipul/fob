<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Block\Adminhtml\Rule\Edit;

use Aitoc\Gifts\Controller\RegistryConstants;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

abstract class GenericButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\ Registry
     */
    private $registry;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    )
    {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getButtonData();

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    protected function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * @return int|null
     */
    protected function getRuleId()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_RULE)->getId();
    }
}
