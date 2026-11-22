<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Helper;

use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Model\Group;
use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\AbstractLandingCollection;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Loads landing pages and groups by their URL key, and passes the attribute values of a page to the layered
 * navigation.
 */
class Data
{
    /**
     * @param RequestInterface $request
     * @param PageCollectionFactory $pageCollectionFactory
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param Variables $variables
     */
    public function __construct(
        protected RequestInterface $request,
        protected PageCollectionFactory $pageCollectionFactory,
        protected GroupCollectionFactory $groupCollectionFactory,
        protected Variables $variables
    ) {
    }

    /**
     * Sets the value of the attribute as request parameter, which the filters of the layered navigation apply.
     *
     * @param string $attributeCode
     * @param mixed $attributeValue
     *
     * @return void
     */
    public function addValue(string $attributeCode, mixed $attributeValue): void
    {
        $this->request->setParams([$attributeCode => $attributeValue]);
    }

    /**
     * Returns the active landing page with the URL key in the store view, or null if there is none.
     *
     * @param string $urlKey
     * @param int $storeId
     *
     * @return Page|null
     */
    public function loadPage(string $urlKey, int $storeId): ?Page
    {
        $page = $this->loadByUrlKey($this->pageCollectionFactory->create(), $urlKey, $storeId, true);

        return $page instanceof Page ? $page : null;
    }

    /**
     * Returns the active landing group with the URL key in the store view, or null if there is none.
     *
     * @param string $urlKey
     * @param int $storeId
     *
     * @return Group|null
     */
    public function loadGroup(string $urlKey, int $storeId): ?Group
    {
        $group = $this->loadByUrlKey($this->groupCollectionFactory->create(), $urlKey, $storeId, true);

        return $group instanceof Group ? $group : null;
    }

    /**
     * Returns the ID of a landing page with the URL key in the store view, also an inactive one, or null.
     *
     * @param string $urlKey
     * @param int $storeId
     *
     * @return int|null
     */
    public function getPageIdByUrlKey(string $urlKey, int $storeId): ?int
    {
        $object = $this->loadByUrlKey($this->pageCollectionFactory->create(), $urlKey, $storeId, false);

        return $object === null ? null : $this->variables->intValue($object->getId());
    }

    /**
     * Returns the ID of a landing group with the URL key in the store view, also an inactive one, or null.
     *
     * @param string $urlKey
     * @param int $storeId
     *
     * @return int|null
     */
    public function getGroupIdByUrlKey(string $urlKey, int $storeId): ?int
    {
        $object = $this->loadByUrlKey($this->groupCollectionFactory->create(), $urlKey, $storeId, false);

        return $object === null ? null : $this->variables->intValue($object->getId());
    }

    /**
     * Returns the first object of the collection with the URL key in the store view (or all store views).
     *
     * @param AbstractLandingCollection $collection
     * @param string $urlKey
     * @param int $storeId
     * @param bool $activeOnly
     *
     * @return Page|Group|null
     */
    protected function loadByUrlKey(
        AbstractLandingCollection $collection,
        string $urlKey,
        int $storeId,
        bool $activeOnly
    ): Page|Group|null {
        $collection->addFieldToFilter('url_key', ['eq' => $urlKey]);
        $collection->addStoreFilter($storeId);

        if ($activeOnly) {
            $collection->addActiveFilter();
        }

        $collection->setPageSize(1);

        $object = $collection->getFirstItem();

        return ($object instanceof Page || $object instanceof Group) && $object->getId() ? $object : null;
    }
}
