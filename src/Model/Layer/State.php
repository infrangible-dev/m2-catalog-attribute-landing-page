<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer;

use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * State of the layer of the landing pages: the filters of the attributes of the page are applied, but not shown as
 * active filters, because they cannot be removed.
 */
class State extends \Magento\Catalog\Model\Layer\State
{
    /**
     * @param Registry $registryHelper
     * @param array $data
     *
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(protected Registry $registryHelper, array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Returns the applied filters without those of the attributes of the landing page.
     *
     * @return Item[]
     */
    public function getFilters(): array
    {
        $filters = parent::getFilters();
        $pageAttributeCodes = $this->registryHelper->registry('attribute_filter');

        if (! is_array($pageAttributeCodes)) {
            return $filters;
        }

        return array_values(
            array_filter(
                $filters,
                static function (Item $filterItem) use ($pageAttributeCodes): bool {
                    $filter = $filterItem->getData('filter');
                    $attribute = $filter instanceof AbstractFilter ? $filter->getData('attribute_model') : null;

                    return ! ($attribute instanceof AbstractAttribute &&
                        in_array($attribute->getAttributeCode(), $pageAttributeCodes, true));
                }
            )
        );
    }
}
