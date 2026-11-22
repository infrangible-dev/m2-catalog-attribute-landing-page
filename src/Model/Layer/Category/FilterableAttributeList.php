<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer\Category;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;

/**
 * The attributes of the layered navigation of the landing pages: all filterable attributes.
 */
class FilterableAttributeList extends \Magento\Catalog\Model\Layer\Category\FilterableAttributeList
{
    /**
     * Filters the filterable attributes.
     *
     * @param Collection $collection
     *
     * @return Collection
     */
    protected function _prepareAttributeCollection($collection): Collection
    {
        $collection->addFieldToFilter('additional_table.is_filterable', ['gt' => 0]);

        return $collection;
    }
}
