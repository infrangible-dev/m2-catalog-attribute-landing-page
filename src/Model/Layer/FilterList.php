<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer;

use Infrangible\CatalogAttributeLandingPage\Model\Layer\Filter\Attribute;
use Infrangible\CatalogAttributeLandingPage\Model\Layer\Filter\AttributeSet;
use Magento\Catalog\Model\Config\LayerCategoryConfig;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Category\FilterableAttributeList;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\CatalogSearch\Model\Layer\Filter\Category;
use Magento\CatalogSearch\Model\Layer\Filter\Decimal;
use Magento\CatalogSearch\Model\Layer\Filter\Price;
use Magento\Framework\ObjectManagerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class FilterList
    extends Layer\FilterList
{
    public function __construct(
        ObjectManagerInterface $objectManager,
        FilterableAttributeList $filterableAttributes,
        LayerCategoryConfig $layerCategoryConfig)
    {
        parent::__construct($objectManager, $filterableAttributes, $layerCategoryConfig, [
            'attribute' => Attribute::class,
            'price' => Price::class,
            'decimal' => Decimal::class,
            'category' => Category::class,
            'attribute_set' => AttributeSet::class]);
    }

    /**
     * @return AbstractFilter[]
     */
    public function getFilters(Layer $layer): array
    {
        if (!count($this->filters)) {
            parent::getFilters($layer);

            $this->filters[] = $this->objectManager->create($this->filterTypes['attribute_set'], ['layer' => $layer]);
        }

        return $this->filters;
    }
}
