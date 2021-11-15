<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Rule;

class CustomerGroup implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    private $dataObjectConverter;

    /**
     * CustomerGroup constructor.
     *
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder   $criteriaBuilder
     * @param \Magento\Framework\Convert\DataObject          $dataObjectConverter
     */
    public function __construct(
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Convert\DataObject $dataObjectConverter
    ) {
        $this->groupRepository = $groupRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->dataObjectConverter = $dataObjectConverter;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $customerGroups = $this->groupRepository->getList($this->criteriaBuilder->create())->getItems();

        return $this->dataObjectConverter->toOptionArray($customerGroups, 'id', 'code');
    }
}
