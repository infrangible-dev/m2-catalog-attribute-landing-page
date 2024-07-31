<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Page;

use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Zend_Db_Expr;
use Zend_Db_Select;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Collection
    extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Page::class, \Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Page::class);
    }

    protected function _initSelect(): AbstractCollection
    {
        parent::_initSelect();

        $this->getSelect()->join(['page_store' => $this->getTable('catalog_attribute_landing_page_store')],
            'main_table.page_id = page_store.page_id', ['store_ids' => 'GROUP_CONCAT(page_store.store_id)']);
        $this->getSelect()->group('main_table.page_id');

        return $this;
    }

    public function getSelectCountSql(): Select
    {
        $innerSelect = clone parent::getSelectCountSql();

        $innerSelect->reset(Zend_Db_Select::COLUMNS);
        $innerSelect->columns(new Zend_Db_Expr('DISTINCT main_table.page_id'));

        $countSelect = $this->getConnection()->select();

        $countSelect->from(new Zend_Db_Expr(sprintf('(%s)', $innerSelect->assemble())), ['COUNT(*)']);

        return $countSelect;
    }

    /**
     * @param int|Store $store
     */
    public function addStoreFilter($store, bool $withAdmin = true): AbstractCollection
    {
        $storeIds = $store instanceof Store ? [$store->getId()] : [$store];

        if ($withAdmin) {
            $storeIds[] = 0;
        }

        $this->addFieldToFilter('page_store.store_id', ['in' => $storeIds]);

        return $this;
    }

    protected function _afterLoad(): AbstractCollection
    {
        parent::_afterLoad();

        /** @var AbstractModel $item */
        foreach ($this->_items as $item) {
            $item->setOrigData('store_ids', explode(',', $item->getData('store_ids')));
            $item->setData('store_ids', explode(',', $item->getData('store_ids')));
        }

        return $this;
    }
}
