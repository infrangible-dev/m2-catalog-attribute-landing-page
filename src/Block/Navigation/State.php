<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Block\Navigation;

use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\View\Element\Template\Context;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class State
    extends \Magento\LayeredNavigation\Block\Navigation\State
{
    /** @var Registry */
    protected $registryHelper;

    public function __construct(
        Context $context,
        Resolver $layerResolver,
        Registry $registryHelper,
        array $data = [])
    {
        parent::__construct($context, $layerResolver, $data);

        $this->registryHelper = $registryHelper;
    }

    public function getActiveFilters(): array
    {
        $filters = parent::getActiveFilters();

        if (is_array($filters)) {
            $currentFilters = $this->registryHelper->registry('attribute_filter');

            if (is_array($currentFilters)) {
                foreach ($currentFilters as $currentFilterAttributeCode) {
                    /** @var Item $filter */
                    foreach ($filters as $key => $filter) {
                        $filterFilter = $filter->getData('filter');

                        if ($filterFilter instanceof AbstractFilter) {
                            $filterAttribute = $filterFilter->getData('attribute_model');

                            if ($filterAttribute instanceof AbstractAttribute) {
                                if ($filterAttribute->getAttributeCode() == $currentFilterAttributeCode) {
                                    unset($filters[$key]);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $filters;
    }
}
