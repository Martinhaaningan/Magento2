<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Improving\Material\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    private $attributeRepository;

    /**
     * Eav setup factory
     * @var EavSetupFactory
     */

    /**
     * Init
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory)
    {

        $this->eavSetupFactory = $eavSetupFactory;

    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $eavSetup = $this->eavSetupFactory->create();
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'brands',
                [
                    'group' => 'General',
                    'type' => 'varchar',
                    'label' => 'Brands',
                    'input' => 'multiselect',
                    'source' => 'Improving\Material\Model\Attribute\Source\Brands',
                    'frontend' => 'Improving\Material\Model\Attribute\Frontend\Brands',
                    'backend' => 'Improving\Material\Model\Attribute\Backend\Brands',
                    'required' => false,
                    'sort_order' => 50,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'visible' => true,
                    'is_html_allowed_on_front' => true,
                    'visible_on_front' => true,
                    'user_defined' => true
                ]
            );
        }
    }
}
