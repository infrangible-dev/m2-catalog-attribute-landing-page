<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class AttributeSet
    extends AbstractFilter
{
    /**
     * @throws LocalizedException
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        array $data = [])
    {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);

        $this->setRequestVar('attribute_set_id');
    }

    public function getName(): Phrase
    {
        return __('Attribute Set');
    }

    public function apply(RequestInterface $request): AttributeSet
    {
        $attributeValue = $request->getParam($this->_requestVar);

        if (empty($attributeValue)) {
            return $this;
        }

        $productCollection = $this->getLayer()->getProductCollection();

        $productCollection->addFieldToFilter('attribute_set_id', $attributeValue);

        $productCollection->getSelect()->where('attribute_set_id = ?', $attributeValue);

        return $this;
    }
}
