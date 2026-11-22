<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer\Filter;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Filter of an attribute in the layered navigation of the landing pages, which also accepts several values (the values
 * of a page with a multiselect attribute).
 */
class Attribute extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute
{
    /**
     * Filters the products with the value or values of the request.
     *
     * @param RequestInterface $request
     *
     * @return $this
     * @throws LocalizedException
     */
    public function apply(RequestInterface $request): static
    {
        $attributeValue = $request->getParam($this->_requestVar);

        if ($attributeValue === null || $attributeValue === '' || $attributeValue === []) {
            return $this;
        }

        $attribute = $this->getAttributeModel();

        $this->getLayer()->getProductCollection()->addFieldToFilter($attribute->getAttributeCode(), $attributeValue);

        $labels = [];

        foreach ((array)$attributeValue as $value) {
            $optionId = filter_var($value, FILTER_VALIDATE_INT);
            $label = $optionId === false ? $value : $this->getOptionText($optionId);

            foreach ((array)$label as $labelPart) {
                if (is_string($labelPart)) {
                    $labels[] = $labelPart;
                }
            }
        }

        $this->getLayer()->getState()->addFilter(
            $this->_createItem(implode(',', array_unique($labels)), $attributeValue)
        );

        // the filter has no options anymore
        $this->setItems([]);

        return $this;
    }
}
