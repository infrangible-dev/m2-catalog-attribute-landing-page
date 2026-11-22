<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\AdvancedSearch\Adapter\DataMapper;

use Infrangible\Core\Helper\Database;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;

/**
 * Adds the attribute set of the products to the search index, for the attribute set filter of the landing pages.
 */
class AttributeSet implements AdditionalFieldsProviderInterface
{
    /**
     * @param Database $databaseHelper
     */
    public function __construct(protected Database $databaseHelper)
    {
    }

    /**
     * Returns the attribute set ID of each product.
     *
     * @param array $productIds
     * @param int $storeId
     *
     * @phpstan-param array<int|string> $productIds
     *
     * @return array<int|string, array<string, mixed>>
     */
    public function getFields(array $productIds, $storeId): array
    {
        if (count($productIds) === 0) {
            return [];
        }

        $query = $this->databaseHelper->select(
            $this->databaseHelper->getTableName('catalog_product_entity'),
            ['entity_id', 'attribute_set_id']
        );

        $query->where('entity_id IN (?)', $productIds);

        return array_map(
            static fn(mixed $attributeSetId): array => ['attribute_set_id' => $attributeSetId],
            $this->databaseHelper->fetchPairs($query)
        );
    }
}
