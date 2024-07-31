<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer\Filter;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Attribute
    extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute
{
    /**
     * @throws LocalizedException
     */
    public function apply(RequestInterface $request): Attribute
    {
        $attributeValue = $request->getParam($this->_requestVar);

        if (empty($attributeValue) && !is_numeric($attributeValue)) {
            return $this;
        }

        $attribute = $this->getAttributeModel();

        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        $productCollection->addFieldToFilter($attribute->getAttributeCode(), $attributeValue);

        $labels = [];
        foreach ((array) $attributeValue as $value) {
            $label = $this->getOptionText($value);

            $labels[] = is_array($label) ? $label : [$label];
        }

        $label = implode(',', array_unique(array_merge([], ...$labels)));

        $this->getLayer()->getState()->addFilter($this->_createItem($label, $attributeValue));

        $this->setItems([]); // set items to disable show filtering

        return $this;
    }
}