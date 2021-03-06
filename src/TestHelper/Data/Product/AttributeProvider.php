<?php
/**
 * Tweakwise & Emico (https://www.tweakwise.com/ & https://www.emico.nl/) - All Rights Reserved
 *
 * @copyright Copyright (c) 2017-2017 Tweakwise.com B.V. (https://www.tweakwise.com)
 * @license   Proprietary and confidential, Unauthorized copying of this file, via any medium is strictly prohibited
 */

namespace Emico\TweakwiseExport\TestHelper\Data\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Eav\Model\Config as EavConfig;

class AttributeProvider
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var AttributeOptionManagementInterface
     */
    private $attributeOptionManagement;

    /**
     * @var AttributeOptionLabelInterfaceFactory
     */
    private $optionLabelFactory;

    /**
     * @var AttributeOptionInterfaceFactory
     */
    private $optionFactory;

    /**
     * @var CategorySetup
     */
    private $categorySetup;

    /**
     * AttributeProvider constructor.
     *
     * @param EavConfig $eavConfig
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     * @param AttributeOptionLabelInterfaceFactory $optionLabelFactory
     * @param AttributeOptionInterfaceFactory $optionFactory
     * @param CategorySetup $categorySetup
     */
    public function __construct(
        EavConfig $eavConfig,
        AttributeOptionManagementInterface $attributeOptionManagement,
        AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        AttributeOptionInterfaceFactory $optionFactory,
        CategorySetup $categorySetup
    )
    {
        $this->eavConfig = $eavConfig;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->optionFactory = $optionFactory;
        $this->categorySetup = $categorySetup;
    }

    /**
     * Fetches product attribute
     *
     * @param string $code
     * @return AttributeInterface
     */
    public function get(string $code): AttributeInterface
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, $code);
    }

    /**
     * @param string $set
     * @return int
     */
    public function getSetId(string $set = 'Default'): int
    {
        return (int) $this->categorySetup->getAttributeSetId(Product::ENTITY, $set);
    }

    /**
     * @param string $code
     * @param string|int $set
     * @return $this
     */
    public function ensureSet(string $code, $set): self
    {
        $this->categorySetup->addAttributeToGroup(Product::ENTITY, $set, 'Default', $code);
        return $this;
    }

    /**
     * Fetches or creates option id for product attribute
     *
     * @param string $code
     * @param string $label
     * @return int
     */
    public function getOptionId(string $code, string $label): int
    {
        $attribute = $this->get($code);
        $options = $attribute->getOptions();
        foreach ($options as $option) {
            if (\mb_strtolower($option->getLabel()) === \mb_strtolower($label)) {
                return (int) $option->getValue();
            }
        }

        // Create label
        $optionLabel = $this->optionLabelFactory->create();
        $optionLabel->setStoreId(0);
        $optionLabel->setLabel($label);

        // Create value
        $option = $this->optionFactory->create();
        $option->setLabel($optionLabel);
        $option->setStoreLabels([$optionLabel]);
        $option->setSortOrder(0);
        $option->setIsDefault(false);

        $this->attributeOptionManagement->add(Product::ENTITY, $attribute->getAttributeId(), $option);

        $attribute->setOptions();
        return $this->getOptionId($code, $label);
    }
}