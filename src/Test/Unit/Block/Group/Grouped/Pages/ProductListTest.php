<?php /** @noinspection PhpDeprecationInspection The product list needs the context of product blocks. */

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Test\Unit\Block\Group\Grouped\Pages;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Block\Group\Grouped\Pages\ProductList;
use Infrangible\CatalogAttributeLandingPage\Test\Unit\CreatesPages;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Database;
use Infrangible\Core\Helper\Product;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\ViewModel\Product\OptionsData;
use Magento\Framework\DB\Select;
use Magento\Framework\Url\EncoderInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * Tests the first products of a landing page in the list of a landing group.
 */
#[AllowMockObjectsWithoutExpectations]
class ProductListTest extends TestCase
{
    use CreatesPages;

    /**
     * Tests the filters of all attributes of the page: one of the options of multiselect attributes (finset, also for
     * the comma-separated values of pages in collections), the value of other attributes, and the attribute set.
     *
     * Before, only the first attribute was filtered, and multiselect values were compared with "in".
     *
     * @return void
     * @throws Exception
     */
    public function testCreateProductCollection(): void
    {
        $page = $this->createPage(
            [
                'attribute_id1'    => '137',
                'value1'           => '11,8',
                'attribute_id2'    => '93',
                'value2'           => '58',
                'attribute_set_id' => '9'
            ]
        );

        $filters = [];

        $productIdsCollection = $this->createStub(Collection::class);
        $productIdsCollection->method('getSelect')->willReturn($this->createStub(Select::class));
        $productIdsCollection->method('addAttributeToFilter')->willReturnCallback(
            function (string $attributeCode, array $condition) use (&$filters, $productIdsCollection): Collection {
                $filters[ $attributeCode ] = $condition;

                return $productIdsCollection;
            }
        );

        $productHelper = $this->createStub(Product::class);
        $productHelper->method('getProductCollection')->willReturnOnConsecutiveCalls(
            $productIdsCollection,
            $this->createStub(Collection::class)
        );

        $databaseHelper = $this->createStub(Database::class);
        $databaseHelper->method('fetchPairs')->willReturn([]);

        $attributeHelper = $this->createStub(Attribute::class);
        $attributeHelper->method('getAttribute')->willReturnMap(
            [
                [\Magento\Catalog\Model\Product::ENTITY, '137', $this->createAttribute('activity', 'multiselect')],
                [\Magento\Catalog\Model\Product::ENTITY, '93', $this->createAttribute('color', 'select')]
            ]
        );

        $catalogConfig = $this->createStub(Config::class);
        $catalogConfig->method('getProductAttributes')->willReturn([]);

        $context = $this->createStub(Context::class);
        $context->method('getCatalogConfig')->willReturn($catalogConfig);

        $productList = new ProductList(
            $context,
            $productHelper,
            $this->createStub(EncoderInterface::class),
            $this->createStub(Visibility::class),
            $attributeHelper,
            new Variables(),
            $databaseHelper,
            $this->createStub(OptionsData::class)
        );

        $productList->createProductCollection($page);

        $this->assertSame(
            [
                'activity'         => [['finset' => '11'], ['finset' => '8']],
                'color'            => ['eq' => '58'],
                'attribute_set_id' => ['eq' => 9]
            ],
            $filters
        );
    }

    /**
     * Returns an attribute with options.
     *
     * @param string $attributeCode
     * @param string $frontendInput
     *
     * @return EavAttribute
     */
    private function createAttribute(string $attributeCode, string $frontendInput): EavAttribute
    {
        $attribute = $this->createStub(EavAttribute::class);
        $attribute->method('getAttributeCode')->willReturn($attributeCode);
        $attribute->method('getFrontendInput')->willReturn($frontendInput);
        $attribute->method('usesSource')->willReturn(true);

        return $attribute;
    }
}
