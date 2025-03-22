<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Block\Group\Grouped\Pages;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Variables;
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
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Url\EncoderInterface;
use Zend_Db_Select;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ProductList extends AbstractProduct
{
    /** @var Product */
    protected $productHelper;

    /** @var EncoderInterface */
    protected $encoder;

    /** @var Visibility */
    protected $catalogProductVisibility;

    /** @var Attribute */
    protected $attributeHelper;

    /** @var Variables */
    protected $variables;

    /** @var Database */
    protected $databaseHelper;

    /** @var Arrays */
    protected $arrays;

    /** @var Collection */
    private $productCollection;

    /** @var array */
    private $productIds = [];

    public function __construct(
        Context $context,
        Product $productHelper,
        EncoderInterface $encoder,
        Visibility $catalogProductVisibility,
        Attribute $attributeHelper,
        Variables $variables,
        Database $databaseHelper,
        Arrays $arrays,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->productHelper = $productHelper;
        $this->encoder = $encoder;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->attributeHelper = $attributeHelper;
        $this->variables = $variables;
        $this->databaseHelper = $databaseHelper;
        $this->arrays = $arrays;
    }

    public function createProductCollection(Page $page, int $limit = 5): void
    {
        $productIdsCollection = $this->productHelper->getProductCollection();
        $productIdsCollection->addStoreFilter();

        $attributeId = $page->getAttributeId1();

        if ($attributeId) {
            $attributeValue = $page->getValue1();

            $this->addAttributeFilter(
                $productIdsCollection,
                $this->variables->stringValue($attributeId),
                $attributeValue
            );
        }

        $attributeSetId = $page->getAttributeSetId();

        if ($attributeSetId) {
            $productIdsCollection->addAttributeToFilter(
                'attribute_set_id',
                $attributeSetId
            );
        }

        $productIdsSelect = $productIdsCollection->getSelect();

        $productIdsSelect->joinLeft(
            'catalog_product_super_link',
            'catalog_product_super_link.product_id = e.entity_id'
        );
        $productIdsSelect->reset(Zend_Db_Select::COLUMNS);
        $productIdsSelect->columns(
            [
                'parent_id' => new \Zend_Db_Expr(
                    'IF(catalog_product_super_link.parent_id, catalog_product_super_link.parent_id, e.entity_id)'
                ),
                'child_id'  => 'e.entity_id'
            ]
        );

        $this->productIds = $this->databaseHelper->fetchPairs($productIdsSelect);

        $this->productCollection = $this->productHelper->getProductCollection();

        $this->productCollection->addAttributeToFilter(
            'entity_id',
            ['in' => array_keys($this->productIds)]
        );
        $this->productCollection->addMinimalPrice();
        $this->productCollection->addFinalPrice();
        $this->productCollection->addTaxPercents();
        $this->productCollection->addAttributeToSelect($this->_catalogConfig->getProductAttributes());
        $this->productCollection->addUrlRewrite();
        $this->productCollection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $this->productCollection->addStoreFilter();

        $attributeOrder = $page->getAttributeOrder();
        $attributeOrderDirection = $page->getAttributeOrderDirection();

        $this->productCollection->addAttributeToSort(
            $attributeOrder ? : 'position',
            $attributeOrderDirection ? : 'asc'
        );

        $this->productCollection->setPage(
            1,
            $limit
        );
    }

    protected function addAttributeFilter(
        Collection $productIdsCollection,
        string $attributeId,
        $attributeValue
    ) {
        try {
            $attribute = $this->attributeHelper->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $this->variables->stringValue($attributeId)
            );

            $productIdsCollection->addAttributeToFilter(
                $attribute->getAttributeCode(),
                ['eq' => $attributeValue]
            );
        } catch (\Exception $exception) {
        }
    }

    public function getProductCollection(): Collection
    {
        return $this->productCollection;
    }

    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product): array
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data'   => [
                'product'                               => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->encoder->encode($url),
            ]
        ];
    }

    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType,
        $renderZone = Render::ZONE_ITEM_LIST,
        array $arguments = []
    ): string {
        return parent::getProductPriceHtml(
            $product,
            FinalPrice::PRICE_CODE,
            $renderZone,
            $arguments
        );
    }

    public function getImage($product, $imageId, $attributes = []): Image
    {
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $childId = $this->arrays->getValue(
                $this->productIds,
                $this->variables->stringValue($product->getId())
            );

            if ($childId) {
                try {
                    $product = $this->productHelper->loadProduct(
                        $this->variables->intValue($childId),
                        $product->getStoreId()
                    );
                } catch (\Exception $exception) {
                }
            }
        }

        return parent::getImage(
            $product,
            $imageId,
            $attributes
        );
    }
}
