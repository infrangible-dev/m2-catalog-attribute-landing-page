<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer;

use Infrangible\CatalogAttributeLandingPage\Model\Layer\Category\FilterableAttributeList;
use Infrangible\CatalogAttributeLandingPage\Model\Layer\Filter\Attribute;
use Infrangible\CatalogAttributeLandingPage\Model\Layer\Filter\AttributeSet;
use Magento\Catalog\Model\Config\LayerCategoryConfig;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\CatalogSearch\Model\Layer\Filter\Category;
use Magento\CatalogSearch\Model\Layer\Filter\Decimal;
use Magento\CatalogSearch\Model\Layer\Filter\Price;
use Magento\Framework\ObjectManagerInterface;

/**
 * Filters of the layer of the landing pages: the filters of the search, with the attribute filter of the landing
 * pages and the filter of the attribute set.
 */
class FilterList extends Layer\FilterList
{
    /**
     * @param ObjectManagerInterface $objectManager
     * @param FilterableAttributeList $filterableAttributes
     * @param LayerCategoryConfig $layerCategoryConfig
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        FilterableAttributeList $filterableAttributes,
        LayerCategoryConfig $layerCategoryConfig
    ) {
        parent::__construct(
            $objectManager,
            $filterableAttributes,
            $layerCategoryConfig,
            [
                self::ATTRIBUTE_FILTER => Attribute::class,
                self::PRICE_FILTER     => Price::class,
                self::DECIMAL_FILTER   => Decimal::class,
                self::CATEGORY_FILTER  => Category::class,
                'attribute_set'        => AttributeSet::class
            ]
        );
    }

    /**
     * Returns the filters of the layer, with the filter of the attribute set.
     *
     * @param Layer $layer
     *
     * @return AbstractFilter[]
     */
    public function getFilters(Layer $layer): array
    {
        if (count($this->filters) === 0) {
            parent::getFilters($layer);

            $attributeSetFilter = $this->objectManager->create(
                $this->filterTypes[ 'attribute_set' ],
                ['layer' => $layer]
            );

            if ($attributeSetFilter instanceof AbstractFilter) {
                $this->filters[] = $attributeSetFilter;
            }
        }

        return $this->filters;
    }
}
