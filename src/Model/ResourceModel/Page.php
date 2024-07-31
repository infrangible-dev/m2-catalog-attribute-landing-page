<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\ResourceModel;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Attribute;
use Magento\Catalog\Model\Product;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Page
    extends AbstractDb
{
    /** @var Variables */
    protected $variables;

    /** @var Attribute */
    protected $attributeHelper;

    /**
     * @param Variables $variables
     * @param Attribute $attributeHelper
     * @param Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        Variables $variables,
        Attribute $attributeHelper,
        Context $context,
        string $connectionName = null)
    {
        parent::__construct($context, $connectionName);

        $this->variables = $variables;
        $this->attributeHelper = $attributeHelper;
    }

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('catalog_attribute_landing_page', 'page_id');
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

        $select->join(['page_store' => $this->getTable('catalog_attribute_landing_page_store')],
            sprintf('%s.page_id = page_store.page_id', $this->getTable('catalog_attribute_landing_page')),
            ['store_ids' => 'GROUP_CONCAT(page_store.store_id)']);
        $select->group(sprintf('%s.page_id', $this->getTable('catalog_attribute_landing_page')));

        return $select;
    }

    /**
     * Perform actions after object load
     *
     * @param AbstractModel $object
     *
     * @return AbstractDb
     * @throws Exception
     */
    protected function _afterLoad(AbstractModel $object): AbstractDb
    {
        /** @var \Infrangible\CatalogAttributeLandingPage\Model\Page $object */
        parent::_afterLoad($object);

        $object->setData('store_ids', explode(',', $object->getData('store_ids')));

        if (!$this->variables->isEmpty($object->getAttributeId1())) {
            $attribute1 = $this->attributeHelper->getAttribute(
                Product::ENTITY, $this->variables->stringValue($object->getAttributeId1()));

            if ($attribute1->usesSource()) {
                $object->setValue1($object->getValue1() === null ? [] : explode(',', $object->getValue1()));
            }
        }

        if (!$this->variables->isEmpty($object->getAttributeId2())) {
            $attribute2 = $this->attributeHelper->getAttribute(
                Product::ENTITY, $this->variables->stringValue($object->getAttributeId2()));

            if ($attribute2->usesSource()) {
                $object->setValue2($object->getValue2() === null ? [] : explode(',', $object->getValue2()));
            }
        }

        if (!$this->variables->isEmpty($object->getAttributeId3())) {
            $attribute3 = $this->attributeHelper->getAttribute(
                Product::ENTITY, $this->variables->stringValue($object->getAttributeId3()));

            if ($attribute3->usesSource()) {
                $object->setValue3($object->getValue3() === null ? [] : explode(',', $object->getValue3()));
            }
        }

        if (!$this->variables->isEmpty($object->getAttributeId4())) {
            $attribute4 = $this->attributeHelper->getAttribute(
                Product::ENTITY, $this->variables->stringValue($object->getAttributeId4()));

            if ($attribute4->usesSource()) {
                $object->setValue4($object->getValue4() === null ? [] : explode(',', $object->getValue4()));
            }
        }

        if (!$this->variables->isEmpty($object->getAttributeId5())) {
            $attribute5 = $this->attributeHelper->getAttribute(
                Product::ENTITY, $this->variables->stringValue($object->getAttributeId5()));

            if ($attribute5->usesSource()) {
                $object->setValue5($object->getValue5() === null ? [] : explode(',', $object->getValue5()));
            }
        }

        if (!$this->variables->isEmpty($object->getAttributeOrders())) {
            $object->setAttributeOrders(
                $object->getAttributeOrders() === null ? [] : explode(',', $object->getAttributeOrders()));
        }

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
        /** @var \Infrangible\CatalogAttributeLandingPage\Model\Page $object */
        parent::_beforeSave($object);

        if ($object->isObjectNew()) {
            $object->setCreatedAt(gmdate('Y-m-d H:i:s'));
        }

        $object->setUpdatedAt(gmdate('Y-m-d H:i:s'));

        if (is_array($object->getValue1())) {
            $object->setValue1(implode(',', $object->getValue1()));
        }

        if ($this->variables->isEmpty($object->getAttributeId2())) {
            $object->setAttributeId2(null);
        }

        if ($this->variables->isEmpty($object->getValue2())) {
            $object->setValue2(null);
        }

        if (is_array($object->getValue2())) {
            $object->setValue2(implode(',', $object->getValue2()));
        }

        if ($this->variables->isEmpty($object->getAttributeId3())) {
            $object->setAttributeId3(null);
        }

        if ($this->variables->isEmpty($object->getValue3())) {
            $object->setValue3(null);
        }

        if (is_array($object->getValue3())) {
            $object->setValue3(implode(',', $object->getValue3()));
        }

        if ($this->variables->isEmpty($object->getAttributeId4())) {
            $object->setAttributeId4(null);
        }

        if ($this->variables->isEmpty($object->getValue4())) {
            $object->setValue4(null);
        }

        if (is_array($object->getValue4())) {
            $object->setValue4(implode(',', $object->getValue4()));
        }

        if ($this->variables->isEmpty($object->getAttributeId5())) {
            $object->setAttributeId5(null);
        }

        if ($this->variables->isEmpty($object->getValue5())) {
            $object->setValue5(null);
        }

        if (is_array($object->getValue5())) {
            $object->setValue5(implode(',', $object->getValue5()));
        }

        if ($this->variables->isEmpty($object->getAttributeOrders())) {
            $object->setAttributeOrders(null);
        }

        if (is_array($object->getAttributeOrders())) {
            $object->setAttributeOrders(implode(',', $object->getAttributeOrders()));
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

        /** @var \Infrangible\CatalogAttributeLandingPage\Model\Page $object */
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
                $this->getTable('catalog_attribute_landing_page_store'),
                sprintf('page_id = %d AND store_id = %d', $object->getPageId(), $removedStoreId));
        }

        $addedStoreIds = array_diff($storeIds, $orgStoreIds);

        foreach ($addedStoreIds as $addedStoreId) {
            $this->getConnection()->insert(
                $this->getTable('catalog_attribute_landing_page_store'),
                ['page_id' => $object->getPageId(), 'store_id' => $addedStoreId]);
        }

        return $this;
    }
}
