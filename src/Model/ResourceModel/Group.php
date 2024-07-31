<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\ResourceModel;

use FeWeDev\Base\Variables;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Group
    extends AbstractDb
{
    /** @var Variables */
    protected $variables;

    /**
     * @param Context $context
     * @param Variables $variables
     * @param string|null $connectionName
     */
    public function __construct(Context $context, Variables $variables, string $connectionName = null)
    {
        parent::__construct($context, $connectionName);

        $this->variables = $variables;
    }

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('catalog_attribute_landing_group', 'group_id');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     *
     * @return Select
     */
    protected function _getLoadSelect($field, $value, $object): Select
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        $select->join(['group_store' => $this->getTable('catalog_attribute_landing_group_store')],
            sprintf('%s.group_id = group_store.group_id', $this->getTable('catalog_attribute_landing_group')),
            ['store_ids' => 'GROUP_CONCAT(group_store.store_id)']);
        $select->group(sprintf('%s.group_id', $this->getTable('catalog_attribute_landing_group')));

        return $select;
    }

    /**
     * Perform actions after object load
     *
     * @param AbstractModel $object
     *
     * @return AbstractDb
     */
    protected function _afterLoad(AbstractModel $object): AbstractDb
    {
        parent::_afterLoad($object);

        $object->setData('store_ids', explode(',', $object->getData('store_ids')));

        return $this;
    }

    /**
     * Perform actions before object save
     *
     * @param AbstractModel $object
     *
     * @return AbstractDb
     */
    protected function _beforeSave(AbstractModel $object): AbstractDb
    {
        /** @var \Infrangible\CatalogAttributeLandingPage\Model\Group $object */
        parent::_beforeSave($object);

        if ($object->isObjectNew()) {
            $object->setCreatedAt(gmdate('Y-m-d H:i:s'));
        }

        $object->setUpdatedAt(gmdate('Y-m-d H:i:s'));

        if ($this->variables->isEmpty($object->getAttributeId2())) {
            $object->setAttributeId2(null);
        }

        if ($this->variables->isEmpty($object->getAttributeId3())) {
            $object->setAttributeId3(null);
        }

        if ($this->variables->isEmpty($object->getAttributeId4())) {
            $object->setAttributeId4(null);
        }

        if ($this->variables->isEmpty($object->getAttributeId5())) {
            $object->setAttributeId5(null);
        }

        return $this;
    }

    /**
     * Perform actions after object save
     *
     * @param AbstractModel $object
     *
     * @return AbstractDb
     */
    protected function _afterSave(AbstractModel $object): AbstractDb
    {
        parent::_afterSave($object);

        /** @var \Infrangible\CatalogAttributeLandingPage\Model\Group $object */
        $storeIds = $object->getData('store_ids');
        if (!is_array($storeIds)) {
            $storeIds = [];
        }

        $orgStoreIds = $object->getOrigData('store_ids');
        if (!is_array($orgStoreIds)) {
            $orgStoreIds = [];
        }

        $removedStoreIds = array_diff($orgStoreIds, $storeIds);

        foreach ($removedStoreIds as $removedStoreId) {
            $this->getConnection()->delete(
                $this->getTable('catalog_attribute_landing_group_store'),
                sprintf('group_id = %d AND store_id = %d', $object->getGroupId(), $removedStoreId));
        }

        $addedStoreIds = array_diff($storeIds, $orgStoreIds);

        foreach ($addedStoreIds as $addedStoreId) {
            $this->getConnection()->insert(
                $this->getTable('catalog_attribute_landing_group_store'),
                ['group_id' => $object->getGroupId(), 'store_id' => $addedStoreId]);
        }

        return $this;
    }
}
