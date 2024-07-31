<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer;

use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Collection;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\CollectionFactory;
use Infrangible\Core\Helper\Stores;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ItemCollectionProvider
    implements ItemCollectionProviderInterface
{
    /** @var Stores */
    protected $storeHelper;

    /** @var CollectionFactory */
    protected $productCollectionFactory;

    public function __construct(Stores $storeHelper, CollectionFactory $productCollectionFactory)
    {
        $this->storeHelper = $storeHelper;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getCollection(Category $category): Collection
    {
        $collection = $this->productCollectionFactory->create();

        $collection->setStoreId($this->storeHelper->getStore()->getId());

        return $collection;
    }
}
