<?php

/**
 * @category    Example
 * @package     Scandiweb_Test
 * @author      Yurii Miroshnyk relied on the Ralfs' Aizsils <info@scandiweb.com> code
 * @copyright   Copyright (c) 2021 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Create Migration product class
 */
class AddSampleProduct implements DataPatchInterface
{
    /**
     * moduleDataSetup
     *
     * @var ModuleDataSetupInterface
     */
    protected ModuleDataSetupInterface $moduleDataSetup;

    /**
     * eavSetupFactory
     *
     * @var EavSetupFactory
     */
    protected EavSetupFactory $eavSetupFactory;

    /**
     * productInterfaceFactory
     * 
     * @var ProductInterfaceFactory
     */
    protected ProductInterfaceFactory $productInterfaceFactory;

    /**
     * productRepository
     * 
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * categoryCollectionFactory
     * 
     * @var CollectionFactory
     */
    protected CollectionFactory $categoryCollectionFactory;

    /**
     * appStat
     * 
     * @var State
     */
    protected State $appState;

    /**
     * storeManager
     * 
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * sourceItemFactory
     * 
     * @var SourceItemInterfaceFactory
     */
    protected SourceItemInterfaceFactory $sourceItemFactory;

    /**
     * sourceItemsSaveInterface
     * 
     * @var SourceItemsSaveInterface
     */
    protected SourceItemsSaveInterface $sourceItemsSaveInterface;

    /**
     * eavSetup
     * 
     * @var EavSetup
     */
    protected EavSetup $eavSetup;

    /**
     * categoryLink
     * 
     * @var CategoryLinkManagementInterface
     */
    protected CategoryLinkManagementInterface $categoryLink;

    /**
     * sourceItems
     * 
     * @var array
     */
    protected array $sourceItems = [];

    /**
     * Custom attributes data 
     * 
     * @var array
     */
    protected array $customAttrs = [
        [
            'name' => 'type_of',
            'type' => 'varchar',
            'input' => 'select',
            'label' => 'Type Of',
            'default' => 'simple',
            'source' => 'Scandiweb\Test\Model\Attribute\Source\Samples',
            'required' => true,
            'visible_on_front' => false
        ],
        [
            'name' => 'important_description',
            'type' => 'text',
            'input' => 'text',
            'label' => 'Important Description',
            'default' => 'Type Your description here',
            'source' => '',
            'required' => true,
            'visible_on_front' => true
        ],
        [
            'name' => 'unimportant_description',
            'type' => 'text',
            'input' => 'text',
            'label' => 'Unimportant Description',
            'default' => 'Type or not type some description here',
            'source' => '',
            'required' => false,
            'visible_on_front' => true
        ]
    ];

    /**
     * Data for products that will be added
     * 
     * @var array
     */
    protected array $productsData = [
        [
            'name' => 'Sample Product One',
            'url_key' => 'sample-product-1',
            'price' => 100.01,
            'sku' => 'sample-product1',
            'quantity' => 42,
            'weight' => 10,
            'meta_title' => 'Sample Product One',
            'meta_descr' => 'Sample Product One',
            'meta_keyw' => 'Sample Product One',
            'country' => 'UA',
            'type_of' => 'partial',
            'important_description' => 'This is very important product',
            'unimportant_description' => 'Earth\'s goods is the best in the Solar System!'
        ],
        [
            'name' => 'Sample Product Two',
            'url_key' => 'sample-product-2',
            'price' => 100.02,
            'sku' => 'sample-product2',
            'quantity' => 420,
            'weight' => 11,
            'meta_title' => 'Sample Product Two',
            'meta_descr' => 'Sample Product Two',
            'meta_keyw' => 'Sample Product Two',
            'country' => 'LV',
            'type_of' => 'simple',
            'important_description' => 'This is also very important product',
            'unimportant_description' => 'You can buy this product everywhere! (Except Moon)'
        ]
    ];

    /**
     * Migration patch constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param ProductInterfaceFactory $productInterfaceFactory
     * @param ProductRepositoryInterface $productRepository
     * @param State $appState
     * @param StoreManagerInterface $storeManager
     * @param EavSetup $eavSetup
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param SourceItemsSaveInterface $sourceItemsSaveInterface
     * @param CategoryLinkManagementInterface $categoryLink
     * @param CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        ProductInterfaceFactory $productInterfaceFactory,
        ProductRepositoryInterface $productRepository,
        State $appState,
        StoreManagerInterface $storeManager,
        EavSetup $eavSetup,
        SourceItemInterfaceFactory $sourceItemFactory,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        CategoryLinkManagementInterface $categoryLink,
        CollectionFactory $categoryCollectionFactory
    ) {
        $this->appState = $appState;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->productRepository = $productRepository;
        $this->eavSetup = $eavSetup;
        $this->storeManager = $storeManager;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->categoryLink = $categoryLink;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Add new product
     *
     * @return void
     */
    public function apply(): void
    {
        foreach ($this->customAttrs as $attr) {
            $this->addAttribute($attr);
        }

        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        foreach ($this->productsData as $prod) {
            $this->addProd($prod);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Add attributes for all products
     *
     * @param array $data
     * @return void
     */
    public function addAttribute($data)
    {
        /** 
         * @var EavSetup $eavSetup 
         */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(Product::ENTITY, $data['name'], [
            'type' => $data['type'],
            'backend' => '',
            'frontend' => '',
            'label' => $data['label'],
            'input' => $data['input'],
            'class' => '',
            'source' => $data['source'],
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'required' => $data['required'],
            'user_defined' => false,
            'default' => $data['default'],
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => $data['visible_on_front'],
            'used_in_product_listing' => true,
            'unique' => false
        ]);
    }

    /**
     * Add products (from array) to the Default Category
     * 
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws ValidationException
     * 
     * @param array $data
     * @return void
     */
    public function addProd($data)
    {
        $product = $this->productInterfaceFactory->create();

        if ($product->getIdBySku($data['sku'])) {
            return;
        }

        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');
        $websiteIDs = [$this->storeManager->getStore()->getWebsiteId()];

        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setWebsiteIds($websiteIDs)
            ->setAttributeSetId($attributeSetId)
            ->setName($data['name'])
            ->setUrlKey($data['url_key'])
            ->setSku($data['sku'])
            ->setPrice($data['price'])
            ->setWeight($data['weight'])
            ->setCountryOfManufacture($data['country'])
            ->setMetaTitle($data['meta_title'])
            ->setMetaKeyword($data['meta_keyw'])
            ->setMetaDescription($data['meta_descr'])
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData([
                'use_config_manage_stock' => 1,
                'is_qty_decimal' => 0,
                'min_sale_qty' => 1,
                'max_sale_qty' => 10,
                'is_in_stock' => 1,
                'qty' => $data['quantity']
            ]);

        $product->setCustomAttribute('type_of', $data['type_of']);
        $product->setCustomAttribute('important_description', $data['important_description']);
        $product->setCustomAttribute('unimportant_description', $data['unimportant_description']);

        $product = $this->productRepository->save($product);

        $sourceItem = $this->sourceItemFactory->create();
        $sourceItem->setSourceCode('default');
        $sourceItem->setQuantity($data['quantity']);
        $sourceItem->setSku($product->getSku());
        $sourceItem->setStatus(SourceItemInterface::STATUS_IN_STOCK);

        $this->sourceItems[] = $sourceItem;
        $this->sourceItemsSaveInterface->execute($this->sourceItems);

        $categoryId = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('name', 'Default Category')
            ->getAllIds();

        $this->categoryLink->assignProductToCategories($product->getSku(), $categoryId);
    }
}
