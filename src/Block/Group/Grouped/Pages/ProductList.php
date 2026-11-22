<?php /** @noinspection PhpDeprecationInspection The template of Magento's product widget needs these methods. */

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Block\Group\Grouped\Pages;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Model\AbstractLanding;
use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Database;
use Infrangible\Core\Helper\Product;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\ViewModel\Product\OptionsData;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Url\EncoderInterface;

/**
 * Shows the first products of a landing page in the list of a landing group.
 *
 * The values of configurable products are those of their children, so the children are searched and their parents
 * shown, with the image of the found child.
 */
class ProductList extends AbstractProduct
{
    /** @var Collection|null */
    private ?Collection $productCollection = null;

    /** @var array<int|string, mixed> the found child of each shown product */
    private array $productIds = [];

    /**
     * @param Context $context
     * @param Product $productHelper
     * @param EncoderInterface $encoder
     * @param Visibility $catalogProductVisibility
     * @param Attribute $attributeHelper
     * @param Variables $variables
     * @param Database $databaseHelper
     * @param OptionsData $optionsData
     * @param array $data
     *
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        protected Product $productHelper,
        protected EncoderInterface $encoder,
        protected Visibility $catalogProductVisibility,
        protected Attribute $attributeHelper,
        protected Variables $variables,
        protected Database $databaseHelper,
        protected OptionsData $optionsData,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Creates the collection of the first products of the landing page.
     *
     * @param Page $page
     * @param int $limit
     *
     * @return void
     * @throws Exception
     */
    public function createProductCollection(Page $page, int $limit = 5): void
    {
        $productIdsCollection = $this->productHelper->getProductCollection();
        $productIdsCollection->addStoreFilter();

        for ($position = 1; $position <= AbstractLanding::ATTRIBUTE_COUNT; $position++) {
            $attributeId = $page->getAttributeId($position);
            $value = $page->getValue($position);

            if ($attributeId !== null && $value !== null) {
                $this->addAttributeFilter($productIdsCollection, $attributeId, $value);
            }
        }

        $attributeSetId = $page->getAttributeSetId();

        if ($attributeSetId !== null) {
            $productIdsCollection->addAttributeToFilter('attribute_set_id', ['eq' => $attributeSetId]);
        }

        $productIdsSelect = $productIdsCollection->getSelect();

        $productIdsSelect->joinLeft(
            ['super_link' => $this->databaseHelper->getTableName('catalog_product_super_link')],
            'super_link.product_id = e.entity_id',
            []
        );

        $productIdsSelect->reset(Select::COLUMNS);
        $productIdsSelect->columns(
            ['parent_id' => 'IFNULL(super_link.parent_id, e.entity_id)', 'child_id' => 'e.entity_id']
        );

        $this->productIds = $this->databaseHelper->fetchPairs($productIdsSelect);

        $productCollection = $this->productHelper->getProductCollection();

        $productCollection->addAttributeToFilter('entity_id', ['in' => array_keys($this->productIds)]);
        $productCollection->addMinimalPrice();
        $productCollection->addFinalPrice();
        $productCollection->addTaxPercents();
        $productCollection->addAttributeToSelect($this->_catalogConfig->getProductAttributes());
        $productCollection->addUrlRewrite();
        $productCollection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $productCollection->addStoreFilter();
        $productCollection->addAttributeToSort(
            $page->getAttributeOrder() ?? 'position',
            $page->getAttributeOrderDirection() ?? 'asc'
        );
        $productCollection->setPage(1, $limit);

        $this->productCollection = $productCollection;
    }

    /**
     * Filters the products with the value of the attribute: one of the options of attributes with options.
     *
     * @param Collection $productIdsCollection
     * @param int $attributeId
     * @param string|string[] $value
     *
     * @return void
     * @throws Exception
     */
    protected function addAttributeFilter(Collection $productIdsCollection, int $attributeId, string|array $value): void
    {
        $attribute = $this->attributeHelper->getAttribute(\Magento\Catalog\Model\Product::ENTITY, (string)$attributeId);
        // pages of a collection have the option IDs as comma-separated text, loaded pages as a list
        $values = is_array($value) ? $value : ($attribute->usesSource() ? explode(',', $value) : [$value]);

        if ($attribute->getFrontendInput() === 'multiselect') {
            // multiselect values are comma-separated lists of option IDs
            $condition = array_map(static fn(string $optionId): array => ['finset' => $optionId], $values);
        } else {
            $condition = count($values) === 1 ? ['eq' => reset($values)] : ['in' => $values];
        }

        $productIdsCollection->addAttributeToFilter($attribute->getAttributeCode(), $condition);
    }

    /**
     * Returns the collection of the products, empty if it was not created.
     *
     * @return Collection
     */
    public function getProductCollection(): Collection
    {
        if ($this->productCollection === null) {
            // no products before createProductCollection()
            $this->productCollection = $this->productHelper->getProductCollection();
            $this->productCollection->getSelect()->where('1 = 0');
        }

        return $this->productCollection;
    }

    /**
     * Returns the parameters of the form that adds the product to the cart.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array<string, mixed>
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product): array
    {
        $url = $this->getAddToCartUrl($product);

        return [
            'action' => $url,
            'data'   => [
                'product'                               => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->encoder->encode($url)
            ]
        ];
    }

    /**
     * Returns the options of the product for the form that adds it to the cart (template of Magento's product widget).
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array<int, array<string, mixed>>
     */
    public function getOptionsData(\Magento\Catalog\Model\Product $product): array
    {
        return $this->optionsData->getOptionsData($product);
    }

    /**
     * Returns no pager: the list only shows the first products (template of Magento's product widget).
     *
     * @return string
     */
    public function getPagerHtml(): string
    {
        return '';
    }

    /**
     * Returns the HTML of the final price of the product.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string|null $priceType
     * @param string $renderZone
     * @param array $arguments
     *
     * @phpstan-param array<string, mixed> $arguments
     *
     * @return string
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = Render::ZONE_ITEM_LIST,
        array $arguments = []
    ): string {
        return parent::getProductPriceHtml($product, FinalPrice::PRICE_CODE, $renderZone, $arguments);
    }

    /**
     * Returns the image of the product: of configurable products the image of the found child.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     *
     * @phpstan-param array<string, mixed> $attributes
     *
     * @return Image
     */
    public function getImage($product, $imageId, $attributes = []): Image
    {
        $childId = $this->productIds[ $this->variables->stringValue($product->getId()) ] ?? null;

        if ($product->getTypeId() === Configurable::TYPE_CODE && $childId !== null) {
            try {
                $product = $this->productHelper->loadProduct(
                    $this->variables->intValue($childId),
                    $this->variables->intValue($product->getStoreId())
                );
            } catch (Exception $exception) {
                $this->_logger->error($exception);
            }
        }

        return parent::getImage($product, $imageId, $attributes);
    }
}
