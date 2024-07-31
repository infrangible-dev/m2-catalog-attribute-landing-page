<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Plugin\Catalog\Model\ResourceModel\Product;

use Magento\Eav\Model\Entity\Attribute\AttributeInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Collection
{
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param AttributeInterface|int|string|array $attribute
     * @param null|string|array $condition
     * @param string|null $joinType
     *
     * @return array
     * @noinspection PhpUnusedParameterInspection
     */
    public function beforeAddAttributeToFilter(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject,
        $attribute,
        $condition = null,
        string $joinType = null): array
    {
        if (is_array($condition)) {
            $preparedConditions = [];

            foreach ($condition as $operator => $value) {
                if ($operator === 'eq' && is_array($value)) {
                    $preparedConditions['in'] = $value;
                } else {
                    $preparedConditions[$operator] = $value;
                }
            }

            return [$attribute, $preparedConditions, $joinType];
        }

        return [$attribute, $condition, $joinType];
    }
}
