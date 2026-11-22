<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer;

use Infrangible\Core\Helper\Stores;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Creates the product collection of the layer of the landing pages: the search collection with the search request of
 * the landing pages (see di.xml), without a category filter.
 */
class ItemCollectionProvider implements ItemCollectionProviderInterface
{
    /**
     * @param Stores $storeHelper
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(protected Stores $storeHelper, protected CollectionFactory $productCollectionFactory)
    {
    }

    /**
     * Returns the product collection of the current store view.
     *
     * @param Category $category
     *
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function getCollection(Category $category): Collection
    {
        $collection = $this->productCollectionFactory->create();

        $collection->setStoreId($this->storeHelper->getStore()->getId());

        return $collection;
    }
}
