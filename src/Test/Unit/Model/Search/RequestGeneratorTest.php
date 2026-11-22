<?php /** @noinspection PhpDeprecationInspection Magento's own search request generator uses it, no replacement. */

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Test\Unit\Model\Search;

use Infrangible\CatalogAttributeLandingPage\Model\Search\RequestGenerator;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorInterface;
use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorResolver;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * Tests the search request of the landing pages.
 */
#[AllowMockObjectsWithoutExpectations]
class RequestGeneratorTest extends TestCase
{
    /**
     * Tests the filters and aggregations of the filterable attributes (without price and categories, which have
     * their own) and the filter of the attribute set.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testGenerateRequest(): void
    {
        $collection = $this->createStub(Collection::class);
        $collection->method('getItems')->willReturn(
            [$this->createAttribute('color'), $this->createAttribute('price'), $this->createAttribute('category_ids')]
        );

        $collectionFactory = $this->createStub(CollectionFactory::class);
        $collectionFactory->method('create')->willReturn($collection);

        $generator = $this->createStub(GeneratorInterface::class);
        $generator->method('getFilterData')->willReturn(['type' => 'termFilter']);
        $generator->method('getAggregationData')->willReturn(['type' => 'termBucket']);

        $generatorResolver = $this->createStub(GeneratorResolver::class);
        $generatorResolver->method('getGeneratorForType')->willReturn($generator);

        $request = (new RequestGenerator($collectionFactory, $generatorResolver))->generateRequest('container');

        $queries = $request[ 'queries' ] ?? null;
        $filters = $request[ 'filters' ] ?? null;
        $aggregations = $request[ 'aggregations' ] ?? null;

        $this->assertSame(
            [
                'queryReference' => [
                    ['clause' => 'must', 'ref' => 'color_query'],
                    ['clause' => 'must', 'ref' => 'attribute_set_query']
                ]
            ],
            is_array($queries) ? $queries[ 'container' ] ?? null : null
        );
        $this->assertSame(['color_filter', 'attribute_set_filter'], is_array($filters) ? array_keys($filters) : null);
        $this->assertSame(['color_bucket'], is_array($aggregations) ? array_keys($aggregations) : null);
    }

    /**
     * Returns a filterable attribute with options.
     *
     * @param string $attributeCode
     *
     * @return Attribute
     */
    private function createAttribute(string $attributeCode): Attribute
    {
        $attribute = $this->createStub(Attribute::class);
        $attribute->method('getAttributeCode')->willReturn($attributeCode);
        $attribute->method('getBackendType')->willReturn('int');

        return $attribute;
    }
}
