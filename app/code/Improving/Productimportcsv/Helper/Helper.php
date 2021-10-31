<?php

namespace Improving\Productimportcsv\Helper;

use http\Exception;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Improving\MartinHaaning\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\CategoryLinkManagementInterface;




class Helper extends AbstractHelper
{
    public function __construct(State $state,
                                ProductAttributeRepositoryInterface $attributeRepository,
                                attributeFactory $attributeFactory,
                                eavSetupFactory $eavSetupFactory,
                                Context $context,
                                CategoryFactory $categoryFactory,
                                CategoryRepository $categoryRepository,
                                CategoryRepositoryInterface $categoryRepositoryInterface,
                                productRepositoryInterface $productRepositoryInterface,
                                productFactory $productFactory,
                                StockRepositoryInterface $stockRepository,
                                sourceItemsSaveInterface $sourceItemsSave,
                                sourceItemInterfaceFactory $sourceItemFactory,
                                sourceItemInterface $sourceItemInterface,
                                data $helperData,
                                searchCriteriaBuilder $criteriaBuilder,
                                categoryLinkManagementInterface $categoryLinkManagement)
    {
        parent::__construct($context);
        $this->attributeRepository = $attributeRepository;
        $this->attributeFactory = $attributeFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->state = $state;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->productFactory = $productFactory;
        $this->StockRepositoryInterface = $stockRepository;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemInterface = $sourceItemInterface;
        $this->helperData = $helperData;
        $this->searchCriteriaBuilder = $criteriaBuilder;
        $this->categoryLinkManagementInterface = $categoryLinkManagement;
    }


    public function getAttributes($path){
        $file = fopen($path, 'r');
        $i = 0;
        while (($line = fgetcsv($file, 1000, ";")) !== FALSE) {
            if ($i == 0) {
                //category og qty skal ikke med da de er indbyggede i magento
                unset($line[array_search("CATEGORY", $line, false)]);
                unset($line[array_search("QTY", $line, false)]);
                return $line;
            }
            $i++;
        }
    fclose($file);

    }

    public function getProducts($path): array
    {
        $file = $path;
        $arrResult = array();
        $headers = false;
        $handle = fopen($file, "r");
        if (empty($handle) === false) {
            $i = 0;
            while (($data = fgetcsv($handle, 1000,
                    ";")) !== FALSE) {
                if ($i < 20) {
                    if (!$headers) {
                        $headers[] = $data;
                    } else {
                        $arrResult[] = $data;
                    }
                }
                $i++;
            }
            fclose($handle);
        }
        return $arrResult;
    }

    public function createAttributes($csvAttributes){

        $newAttributes = array();
        foreach ($csvAttributes as $attribute) {

                try {
                    $this->attributeRepository->get($attribute);
                } catch (NoSuchEntityException $e) {

                    $newAttributes[] = $attribute;

                    $eavSetup = $this->eavSetupFactory->create();
                    $eavSetup->addAttribute(
                        Product::ENTITY,
                        $attribute,
                        [
                            'group' => 'General',
                            'type' => 'varchar',
                            'label' => ucfirst(strtolower($attribute)),
                            'input' => 'select',
                            'source' => '',
                            'frontend' => '',
                            'backend' => '',
                            'required' => false,
                            'sort_order' => 50,
                            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
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
        return $newAttributes;
    }

    /**
     * @throws LocalizedException
     */

    public function createCategories($categoryName)
    {

        $category = $this->categoryFactory->create();
        $category->setName($categoryName);
        $exists = $category->getCollection()->addAttributeToFilter('name', $categoryName)->getFirstItem();

        if (!$exists->getId()) {
            $category->setParentId(2);
            $category->setIsActive(true);
            $this->categoryRepository->save($category);
            $ids = $category->getId();
            echo "Created category ". $categoryName . "\r\n";

        } else {
            $ids = $exists->getId();
        }

        return $ids;

    }

    public function createConfigurable ($csvProducts) {
        $createdProducts = array();
        $nameArray = array();
        $simpleProductIds = array();
        foreach ($csvProducts as $csvProduct) {

            foreach ($csvProducts as $checkProduct) {
                if ($csvProduct[1] == $checkProduct[1]) {
                    if (!in_array($csvProduct[1], $nameArray)) {
                        $simpleProductIds[] = $this->createSimple($checkProduct);
                        $nameArray[] = $csvProduct[1];
                    }
                }
            }
            try {
                $this->productRepositoryInterface->get($csvProduct[1]);

                } catch (NoSuchEntityException $e) {
                $product = $this->productFactory->create();
                $createdProducts[] = $csvProduct[1];
                $product->setTypeId('configurable');
                $product->setName($csvProduct[1]);
                $product->setSku($csvProduct[1]);
                $product->setWebsiteIds([1]);
                $product->setUrlKey($csvProduct[1]);
                $product->setAttributeSetId(4);
                $product->setVisibility(3);
                $this->productRepositoryInterface->save($product);

                $catIds[] = $this->createCategories(array_values(array_slice($csvProduct, -3))[0]);

                $this->categoryLinkManagementInterface->assignProductToCategories(
                  $product->getSku(),
                  $catIds
                );

                echo "Created Product " . $product->getName() . "\r\n";
                $catIds = array();
            }
            $simpleProductIds = array();
        }
        return $createdProducts;

    }
    /**
     * @throws NoSuchEntityException
     */
    public function createSimple($simpleProduct){
            try {
                $this->productRepositoryInterface->get($simpleProduct[0]);
            } catch (NoSuchEntityException $e) {

                $product = $this->productFactory->create();
                $product->setName($simpleProduct[1]);
                $product->setTypeId('simple');
                $product->setSku($simpleProduct[0]);
                $product->setWebsiteIds([1]);
                $product->setUrlKey($simpleProduct[1] . $simpleProduct[0]);
                $product->setAttributeSetId(4);
                $price = array_values(array_slice($simpleProduct, -6))[0];
                $product->setPrice((float)$price);
                $product->setVisibility(4);
                $this->productRepositoryInterface->save($product);

            }
            $product = $this->productRepositoryInterface->get($simpleProduct[0]);
            $product->setName($simpleProduct[1]);
            $qty = array_values(array_slice($simpleProduct, -7))[0];
            $sourceItem = $this->sourceItemFactory->create();
            $sourceItem->setSourceCode('default');
            $sku = $simpleProduct[0];
            //WHY WONT YOU JUST BE A NORMAL STRING?
            $sourceItem->setSku((string)$sku);
            $sourceItem->setQuantity($qty);
            $sourceItem->setStatus(1);
            $this->sourceItemsSave->execute([$sourceItem]);

            $this->productRepositoryInterface->save($product);
            $productId[] = $product->getId();
            return $productId;

    }


    public function createOptions($csvAttributes, $csvProducts) {
    $options = array();
    $attrCodes = array_slice($csvAttributes, 4, 4);
    for ($i = 5; $i < 10; $i++) {
        if ($i != 7) {
        $arr = array();
            foreach ($csvProducts as $csvProduct) {
                $arr[] = array_values(array_slice($csvProduct, $i))[0];
            }
        $options[] = array_unique($arr, SORT_STRING);
        }
    }
    $newOptions = array_combine($attrCodes, $options);

    foreach ($newOptions as $code => $labels) {
        foreach($labels as $label) {
            $ids[] = $this->helperData->createOrGetId($code, $label);
        }
    }
    return $newOptions;
    }


}



