<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Helper;

use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Infrangible\CatalogAttributeLandingPage\Model\Group;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Data
{
    /** @var Http */
    protected $request;

    /** @var ResourceModel\Page\CollectionFactory */
    protected $pageCollectionFactory;

    /** @var ResourceModel\Group\CollectionFactory */
    protected $groupCollectionFactory;

    public function __construct(
        RequestInterface $request,
        ResourceModel\Page\CollectionFactory $pageCollectionFactory,
        ResourceModel\Group\CollectionFactory $groupCollectionFactory)
    {
        $this->request = $request;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    public function addValue(string $attributeCode, $attributeValue)
    {
        $this->request->setParam($attributeCode, $attributeValue);
    }

    public function loadPage(string $urlKey, int $storeId): ?Page
    {
        $pageCollection = $this->pageCollectionFactory->create();

        $pageCollection->addFieldToFilter('url_key', ['eq' => $urlKey]);
        $pageCollection->addFieldToFilter('active', ['eq' => 1]);
        $pageCollection->addStoreFilter($storeId);

        $pageCollection->load();

        /** @var Page $page */
        $page = $pageCollection->getFirstItem();

        return $page;
    }

    public function loadGroup(string $urlKey, int $storeId): ?Group
    {
        $groupCollection = $this->groupCollectionFactory->create();

        $groupCollection->addFieldToFilter('url_key', ['eq' => $urlKey]);
        $groupCollection->addFieldToFilter('active', ['eq' => 1]);
        $groupCollection->addStoreFilter($storeId);

        $groupCollection->load();

        /** @var Group $group */
        $group = $groupCollection->getFirstItem();

        return $group;
    }
}
