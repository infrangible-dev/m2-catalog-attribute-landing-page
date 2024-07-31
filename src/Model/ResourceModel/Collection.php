<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\ResourceModel;

use Exception;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\DefaultFilterStrategyApplyCheckerInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Search\Request\Builder;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Search\Api\SearchInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\SearchEngine;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Zend_Db_Select;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Collection
    extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{
    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param Helper $resourceHelper
     * @param UniversalFactory $universalFactory
     * @param StoreManagerInterface $storeManager
     * @param Manager $moduleManager
     * @param State $catalogProductFlatState
     * @param ScopeConfigInterface $scopeConfig
     * @param OptionFactory $productOptionFactory
     * @param Url $catalogUrl
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param DateTime $dateTime
     * @param GroupManagementInterface $groupManagement
     * @param QueryFactory $catalogSearchData
     * @param Builder $requestBuilder
     * @param SearchEngine $searchEngine
     * @param TemporaryStorageFactory $temporaryStorageFactory
     * @param AdapterInterface|null $connection
     * @param string $searchRequestName
     * @param SearchResultFactory|null $searchResultFactory
     * @param ProductLimitationFactory|null $productLimitationFactory
     * @param MetadataPool|null $metadataPool
     * @param SearchInterface|null $search
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param FilterBuilder|null $filterBuilder
     * @param SearchCriteriaResolverFactory|null $searchCriteriaResolverFactory
     * @param SearchResultApplierFactory|null $searchResultApplierFactory
     * @param TotalRecordsResolverFactory|null $totalRecordsResolverFactory
     * @param DefaultFilterStrategyApplyCheckerInterface|null $defaultFilterStrategyApplyChecker
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        Helper $resourceHelper,
        UniversalFactory $universalFactory,
        StoreManagerInterface $storeManager,
        Manager $moduleManager,
        State $catalogProductFlatState,
        ScopeConfigInterface $scopeConfig,
        OptionFactory $productOptionFactory,
        Url $catalogUrl,
        TimezoneInterface $localeDate,
        Session $customerSession,
        DateTime $dateTime,
        GroupManagementInterface $groupManagement,
        QueryFactory $catalogSearchData,
        Builder $requestBuilder,
        SearchEngine $searchEngine,
        TemporaryStorageFactory $temporaryStorageFactory,
        AdapterInterface $connection = null,
        $searchRequestName = 'landing_page_container',
        SearchResultFactory $searchResultFactory = null,
        ProductLimitationFactory $productLimitationFactory = null,
        MetadataPool $metadataPool = null,
        SearchInterface $search = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        FilterBuilder $filterBuilder = null,
        SearchCriteriaResolverFactory $searchCriteriaResolverFactory = null,
        SearchResultApplierFactory $searchResultApplierFactory = null,
        TotalRecordsResolverFactory $totalRecordsResolverFactory = null,
        DefaultFilterStrategyApplyCheckerInterface $defaultFilterStrategyApplyChecker = null)
    {
        parent::__construct(
            $entityFactory, $logger, $fetchStrategy, $eventManager, $eavConfig, $resource, $eavEntityFactory,
            $resourceHelper, $universalFactory, $storeManager, $moduleManager, $catalogProductFlatState, $scopeConfig,
            $productOptionFactory, $catalogUrl, $localeDate, $customerSession, $dateTime, $groupManagement,
            $catalogSearchData, $requestBuilder, $searchEngine, $temporaryStorageFactory, $connection,
            $searchRequestName, $searchResultFactory, $productLimitationFactory, $metadataPool, $search,
            $searchCriteriaBuilder, $filterBuilder, $searchCriteriaResolverFactory, $searchResultApplierFactory,
            $totalRecordsResolverFactory, $defaultFilterStrategyApplyChecker);

        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return void
     */
    private function initSearchCriteriaBuilder()
    {
        if ($this->searchCriteriaBuilder === null) {
            $this->searchCriteriaBuilder = ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
        }
    }

    /**
     * @param string $attribute
     * @param string $dir
     *
     * @return \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
     */
    public function addAttributeToSort(
        $attribute,
        $dir = \Magento\Framework\Data\Collection::SORT_ORDER_ASC): \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
    {
        $this->initSearchCriteriaBuilder();

        $this->searchCriteriaBuilder->addSortOrder($attribute, $dir);

        return parent::addAttributeToSort($attribute, $dir);
    }

    /**
     * @param string $attribute
     * @param string $dir
     *
     * @return \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection|Collection
     */
    public function setOrder($attribute, $dir = Zend_Db_Select::SQL_DESC)
    {
        $this->initSearchCriteriaBuilder();

        $this->searchCriteriaBuilder->addSortOrder($attribute, $dir);

        return parent::setOrder($attribute, $dir);
    }

    protected function _beforeLoad(): \Magento\Catalog\Model\ResourceModel\Product\Collection
    {
        return \Magento\Catalog\Model\ResourceModel\Product\Collection::_beforeLoad();
    }

    /**
     * @return \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection|Collection
     * @throws Exception
     */
    public function _loadEntities($printQuery = false, $logQuery = false)
    {
        $this->getSelect()->limitPage($this->getCurPage(), $this->_pageSize);
        $this->getSelect()->order('entity_id DESC');

        $this->setFlag('do_not_use_category_id', true);

        return parent::_loadEntities($printQuery, $logQuery);
    }
}
