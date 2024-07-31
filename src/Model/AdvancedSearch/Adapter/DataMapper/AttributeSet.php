<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\AdvancedSearch\Adapter\DataMapper;

use Infrangible\Core\Helper\Database;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class AttributeSet
    implements AdditionalFieldsProviderInterface
{
    /** @var Database */
    protected $databaseHelper;

    public function __construct(Database $databaseHelper)
    {
        $this->databaseHelper = $databaseHelper;
    }

    /**
     * Get additional fields for data mapper during search indexer based on product ids and store id.
     *
     * @param array $productIds
     * @param int $storeId
     *
     * @return array
     */
    public function getFields(array $productIds, $storeId): array
    {
        $query = $this->databaseHelper->select(
            $this->databaseHelper->getTableName('catalog_product_entity'), ['entity_id', 'attribute_set_id']);

        $query->where('entity_id IN (?)', $productIds);

        $fields = [];

        foreach ($this->databaseHelper->fetchPairs($query) as $productId => $attributeSetId) {
            $fields[$productId] = ['attribute_set_id' => $attributeSetId];
        }

        return $fields;
    }
}
