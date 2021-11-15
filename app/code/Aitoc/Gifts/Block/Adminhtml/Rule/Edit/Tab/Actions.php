<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */

namespace Aitoc\Gifts\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Framework\App\ObjectManager;
use Aitoc\Gifts\Controller\RegistryConstants;

class Actions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $ruleActions;

    /**
     * @var string
     */
    protected $_nameInLayout = 'actions_apply_to';

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * Actions constructor.
     *
     * @param \Magento\Backend\Block\Template\Context              $context
     * @param \Magento\Framework\Registry                          $registry
     * @param \Magento\Framework\Data\FormFactory                  $formFactory
     * @param \Magento\Rule\Block\Actions                          $ruleActions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Aitoc\Gifts\Model\RuleFactory                      $ruleFactory
     * @param array                                                $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Actions $ruleActions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Aitoc\Gifts\Model\RuleFactory $ruleFactory,
        array $data = []

    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->ruleActions = $ruleActions;
        $this->ruleFactory = $ruleFactory ?: ObjectManager::getInstance()
            ->get(\Magento\SalesRule\Model\RuleFactory::class);
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Rules');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Rules');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $model = $this->getRule();
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return \Aitoc\Gifts\Model\SalesRule
     */
    private function getRule()
    {
        if ($giftsRule = $this->_coreRegistry->registry(RegistryConstants::CURRENT_RULE)) {
            return $giftsRule->getSalesRule();
        } else {
            return $this->ruleFactory->create()->getSalesRule();
        }
    }

    /**
     * @param        $model
     * @param string $fieldsetId
     * @param string $formName
     *
     * @return \Magento\Framework\Data\Form
     */
    protected function addTabToForm($model, $fieldsetId = 'actions_fieldset', $formName = 'aitoc_gifts_rule_form')
    {
        $actionsFieldSetId = $model->getActionsFieldSetId($formName);

        $newChildUrl = $this->getUrl(
            'aitoc_gifts/rule/newActionHtml/form/' . $actionsFieldSetId,
            ['form_namespace' => $formName]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $newChildUrl
        )->setFieldSetId(
            $actionsFieldSetId
        );

        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' => __(
                    '<br/>Cart Item Actions <br/>
                     Apply the rule only to cart items matching the following conditions ' .
                    '(leave blank for all items).'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'actions',
            'text',
            [
                'name' => 'actions',
                'label' => __('Rules'),
                'title' => __('Rules'),
                'required' => true,
                'data-form-part' => $formName
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->ruleActions
        );

        $form->setValues($model->getData());
        $this->setActionFormName($model->getActions(), $formName);

        return $form;
    }

    /**
     * Handles addition of form name to action and its actions.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $actions
     * @param string $formName
     * @return void
     */
    private function setActionFormName(\Magento\Rule\Model\Condition\AbstractCondition $actions, $formName)
    {
        $actions->setFormName($formName);
        if ($actions->getActions() && is_array($actions->getActions())) {
            foreach ($actions->getActions() as $condition) {
                $this->setActionFormName($condition, $formName);
            }
        }
    }
}
