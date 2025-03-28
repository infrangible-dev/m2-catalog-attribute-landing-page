<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer\Category;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class FilterableAttributeList extends \Magento\Catalog\Model\Layer\Category\FilterableAttributeList
{
    /**
     * @param Collection $collection
     */
    protected function _prepareAttributeCollection($collection): Collection
    {
        $collection->addFieldToFilter(
            'additional_table.is_filterable',
            ['gt' => 0]
        );

        return $collection;
    }
}
