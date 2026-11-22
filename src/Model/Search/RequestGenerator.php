<?php /** @noinspection PhpDeprecationInspection Magento's own search request generator uses it, no replacement. */

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\Search;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Model\Search\RequestGenerator as CatalogSearchRequestGenerator;
use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorResolver;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;

/**
 * Generates the search request of the landing pages: a filter and an aggregation for each filterable attribute (as the
 * search request of categories) and a filter of the attribute set.
 */
class RequestGenerator
{
    /**
     * @param CollectionFactory $productAttributeCollectionFactory
     * @param GeneratorResolver $generatorResolver
     */
    public function __construct(
        protected CollectionFactory $productAttributeCollectionFactory,
        protected GeneratorResolver $generatorResolver
    ) {
    }

    /**
     * Returns the search request of the container.
     *
     * @param string $container
     *
     * @return array<string, mixed>
     */
    public function generateRequest(string $container): array
    {
        $queryReferences = [];
        $queries = [];
        $filters = [];
        $aggregations = [];

        foreach ($this->getFilterableAttributes() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $backendType = $attribute->getBackendType();

            // price and categories have filters in search_request.xml
            if ($backendType === null || in_array($attributeCode, ['price', 'category_ids'], true)) {
                continue;
            }

            $queryName = $attributeCode . '_query';
            $filterName = $attributeCode . CatalogSearchRequestGenerator::FILTER_SUFFIX;
            $bucketName = $attributeCode . CatalogSearchRequestGenerator::BUCKET_SUFFIX;
            $generator = $this->generatorResolver->getGeneratorForType($backendType);

            $queryReferences[] = ['clause' => 'must', 'ref' => $queryName];
            $queries[ $queryName ] = $this->getFilterQuery($queryName, $filterName);
            $filters[ $filterName ] = $generator->getFilterData($attribute, $filterName);
            $aggregations[ $bucketName ] = $generator->getAggregationData($attribute, $bucketName);
        }

        // the filter of the attribute set
        $queryName = 'attribute_set_query';
        $filterName = 'attribute_set' . CatalogSearchRequestGenerator::FILTER_SUFFIX;

        $queryReferences[] = ['clause' => 'must', 'ref' => $queryName];
        $queries[ $queryName ] = $this->getFilterQuery($queryName, $filterName);
        $filters[ $filterName ] = [
            'type'  => FilterInterface::TYPE_TERM,
            'name'  => $filterName,
            'field' => 'attribute_set_id',
            'value' => '$attribute_set_id$'
        ];

        $queries[ $container ] = ['queryReference' => $queryReferences];

        return ['queries' => $queries, 'filters' => $filters, 'aggregations' => $aggregations];
    }

    /**
     * Returns the query that applies the filter.
     *
     * @param string $queryName
     * @param string $filterName
     *
     * @return array<string, mixed>
     */
    protected function getFilterQuery(string $queryName, string $filterName): array
    {
        return [
            'name'            => $queryName,
            'type'            => QueryInterface::TYPE_FILTER,
            'filterReference' => [['clause' => 'must', 'ref' => $filterName]]
        ];
    }

    /**
     * Returns the filterable attributes of products.
     *
     * @return Attribute[]
     */
    protected function getFilterableAttributes(): array
    {
        $productAttributes = $this->productAttributeCollectionFactory->create();

        $productAttributes->addFieldToFilter(EavAttributeInterface::IS_FILTERABLE, ['in' => [1, 2]]);

        return array_values(
            array_filter(
                $productAttributes->getItems(),
                static fn(mixed $attribute): bool => $attribute instanceof Attribute
            )
        );
    }
}
