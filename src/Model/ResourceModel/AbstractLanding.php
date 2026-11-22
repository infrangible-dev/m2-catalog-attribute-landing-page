<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\ResourceModel;

use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Model\AbstractLanding as LandingModel;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Loads and saves landing pages and groups with their store views (in a table of their own).
 */
abstract class AbstractLanding extends AbstractDb
{
    /**
     * @param Variables $variables
     * @param Context $context
     * @param string|null $connectionName
     */
    public function __construct(protected Variables $variables, Context $context, ?string $connectionName = null)
    {
        parent::__construct($context, $connectionName);
    }

    /**
     * Returns the name of the table with the store views.
     *
     * @return string
     */
    abstract protected function getStoreTableName(): string;

    /**
     * Returns the select of the object, with the IDs of its store views.
     *
     * A left join, so that objects without store views can still be loaded and edited.
     *
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     *
     * @return Select
     * @throws LocalizedException
     */
    protected function _getLoadSelect($field, $value, $object): Select
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        $mainTable = $this->getMainTable();
        $idFieldName = $this->getIdFieldName();

        $select->joinLeft(
            ['landing_store' => $this->getTable($this->getStoreTableName())],
            sprintf('%s.%s = landing_store.%s', $mainTable, $idFieldName, $idFieldName),
            ['store_ids' => 'GROUP_CONCAT(landing_store.store_id)']
        );

        $select->group(sprintf('%s.%s', $mainTable, $idFieldName));

        return $select;
    }

    /**
     * Converts the IDs of the store views into a list.
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object): static
    {
        parent::_afterLoad($object);

        $storeIds = $object->getData('store_ids');

        $object->setData(
            'store_ids',
            is_string($storeIds) && $storeIds !== '' ? array_map('intval', explode(',', $storeIds)) : []
        );

        return $this;
    }

    /**
     * Sets the times of creation and update, and empty attributes to null.
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object): static
    {
        parent::_beforeSave($object);

        $now = gmdate('Y-m-d H:i:s');

        if ($object->isObjectNew() || $this->variables->isEmpty($object->getData('created_at'))) {
            $object->setData('created_at', $now);
        }

        $object->setData('updated_at', $now);

        for ($position = 2; $position <= LandingModel::ATTRIBUTE_COUNT; $position++) {
            $this->setNullIfEmpty($object, sprintf('attribute_id%d', $position));
        }

        $this->setNullIfEmpty($object, 'cms_block_id');

        return $this;
    }

    /**
     * Saves the store views of the object.
     *
     * @param AbstractModel $object
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _afterSave(AbstractModel $object): static
    {
        parent::_afterSave($object);

        $objectId = $this->variables->intValue($object->getId());
        $storeIds = $this->getStoreIdList($object->getData('store_ids'));
        $origStoreIds = $this->getStoreIdList($object->getOrigData('store_ids'));

        $connection = $this->getConnection();

        if ($connection === false) {
            throw new LocalizedException(__('No database connection.'));
        }

        $storeTableName = $this->getTable($this->getStoreTableName());
        $idFieldName = $this->getIdFieldName();

        $removedStoreIds = array_values(array_diff($origStoreIds, $storeIds));

        if (count($removedStoreIds) > 0) {
            $connection->delete(
                $storeTableName,
                [sprintf('%s = ?', $idFieldName) => $objectId, 'store_id IN (?)' => $removedStoreIds]
            );
        }

        $addedStoreIds = array_values(array_diff($storeIds, $origStoreIds));

        if (count($addedStoreIds) > 0) {
            $connection->insertMultiple(
                $storeTableName,
                array_map(
                    static fn(int $storeId): array => [$idFieldName => $objectId, 'store_id' => $storeId],
                    $addedStoreIds
                )
            );
        }

        return $this;
    }

    /**
     * Sets the field to null if it is empty, so that the column gets NULL instead of an empty text.
     *
     * @param AbstractModel $object
     * @param string $key
     *
     * @return void
     */
    protected function setNullIfEmpty(AbstractModel $object, string $key): void
    {
        if ($this->variables->isEmpty($object->getData($key))) {
            $object->setData($key);
        }
    }

    /**
     * Returns the store IDs of the value (a list of IDs from the form or the database) without duplicates.
     *
     * @param mixed $value
     *
     * @return int[]
     */
    protected function getStoreIdList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(
            array_unique(
                array_map(
                    [$this->variables, 'intValue'],
                    array_filter($value, fn(mixed $storeId): bool => ! $this->variables->isEmpty($storeId) ||
                        $storeId === 0 || $storeId === '0')
                )
            )
        );
    }
}
