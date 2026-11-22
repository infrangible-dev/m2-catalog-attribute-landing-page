<?php /** @noinspection PhpDeprecationInspection The constructor of Magento's layer needs the registry. */

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model;

use Infrangible\CatalogAttributeLandingPage\Model\Layer\StateFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\ContextInterface;
use Magento\Catalog\Model\Layer\State;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

/**
 * The layer of the layered navigation of the landing pages, whose state hides the filters of the page attributes.
 */
class Layer extends \Magento\Catalog\Model\Layer
{
    /**
     * @param ContextInterface $context
     * @param \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory
     * @param CollectionFactory $attributeCollectionFactory
     * @param Product $catalogProduct
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StateFactory $catalogAttributeLayerStateFactory
     * @param array $data
     *
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        ContextInterface $context,
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
        CollectionFactory $attributeCollectionFactory,
        Product $catalogProduct,
        StoreManagerInterface $storeManager,
        Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        protected StateFactory $catalogAttributeLayerStateFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $layerStateFactory,
            $attributeCollectionFactory,
            $catalogProduct,
            $storeManager,
            $registry,
            $categoryRepository,
            $data
        );
    }

    /**
     * Returns the state of the layer with the filters of the landing page.
     *
     * @return State
     */
    public function getState(): State
    {
        $state = $this->getData('state');

        if (! $state instanceof State) {
            $state = $this->catalogAttributeLayerStateFactory->create();

            $this->setData('state', $state);
        }

        return $state;
    }
}
