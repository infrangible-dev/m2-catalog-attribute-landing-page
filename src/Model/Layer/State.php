<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer;

use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class State
    extends \Magento\Catalog\Model\Layer\State
{
    /** @var Registry */
    protected $registryHelper;

    public function __construct(Registry $registryHelper, array $data = [])
    {
        parent::__construct($data);

        $this->registryHelper = $registryHelper;
    }

    public function getFilters(): array
    {
        $filters = parent::getFilters();

        if (is_array($filters)) {
            $currentFilters = $this->registryHelper->registry('attribute_filter');

            if (is_array($currentFilters)) {
                foreach ($currentFilters as $currentFilterAttributeCode) {
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
