<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Collection of landing pages or groups with the IDs of their store views.
 */
abstract class AbstractLandingCollection extends AbstractCollection
{
    /**
     * Returns the name of the table with the store views.
     *
     * @return string
     */
    abstract protected function getStoreTableName(): string;

    /**
     * Joins the store views: a left join, so that the grid also shows objects without store views.
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _initSelect(): static
    {
        parent::_initSelect();

        // the ID field of the collection is only set after the select
        $idFieldName = $this->getResource()->getIdFieldName();

        $this->getSelect()->joinLeft(
            ['landing_store' => $this->getTable($this->getStoreTableName())],
            sprintf('main_table.%s = landing_store.%s', $idFieldName, $idFieldName),
            ['store_ids' => 'GROUP_CONCAT(landing_store.store_id)']
        );

        $this->getSelect()->group(sprintf('main_table.%s', $idFieldName));

        return $this;
    }

    /**
     * Filters the objects of the store view, and of all store views (store ID 0) if requested.
     *
     * @param int $storeId
     * @param bool $withAdmin
     *
     * @return $this
     */
    public function addStoreFilter(int $storeId, bool $withAdmin = true): static
    {
        $storeIds = [$storeId];

        if ($withAdmin) {
            $storeIds[] = 0;
        }

        $this->addFieldToFilter('landing_store.store_id', ['in' => $storeIds]);

        return $this;
    }

    /**
     * Filters the active objects.
     *
     * @return $this
     */
    public function addActiveFilter(): static
    {
        $this->addFieldToFilter('active', ['eq' => 1]);

        return $this;
    }

    /**
     * Converts the IDs of the store views into lists.
     *
     * @return $this
     */
    protected function _afterLoad(): static
    {
        parent::_afterLoad();

        foreach ($this->_items as $item) {
            if (! $item instanceof AbstractModel) {
                continue;
            }

            $storeIds = $item->getData('store_ids');
            $storeIds = is_string($storeIds) && $storeIds !== '' ? array_map('intval', explode(',', $storeIds)) : [];

            $item->setData('store_ids', $storeIds);
            $item->setOrigData('store_ids', $storeIds);
        }

        return $this;
    }
}
