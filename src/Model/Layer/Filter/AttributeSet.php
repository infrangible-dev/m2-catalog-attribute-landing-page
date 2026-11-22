<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Filter of the attribute set of a landing page (request parameter attribute_set_id), without options in the layered
 * navigation.
 */
class AttributeSet extends AbstractFilter
{
    /**
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param DataBuilder $itemDataBuilder
     * @param array $data
     *
     * @phpstan-param array<string, mixed> $data
     *
     * @throws LocalizedException
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        array $data = []
    ) {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);

        $this->setRequestVar('attribute_set_id');
    }

    /**
     * Returns the name of the filter.
     *
     * @return string
     */
    public function getName(): string
    {
        return __('Attribute Set')->render();
    }

    /**
     * Filters the products of the attribute set of the request.
     *
     * @param RequestInterface $request
     *
     * @return $this
     */
    public function apply(RequestInterface $request): static
    {
        $attributeSetId = $request->getParam($this->_requestVar);

        if (is_numeric($attributeSetId)) {
            $this->getLayer()->getProductCollection()->addFieldToFilter('attribute_set_id', $attributeSetId);
        }

        return $this;
    }
}
