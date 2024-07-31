<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

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
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Layer
    extends \Magento\Catalog\Model\Layer
{
    /** @var StateFactory */
    protected $catalogAttributeLayerStateFactory;

    public function __construct(
        ContextInterface $context,
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
        CollectionFactory $attributeCollectionFactory,
        Product $catalogProduct,
        StoreManagerInterface $storeManager,
        Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        StateFactory $catalogAttributeLayerStateFactory,
        array $data = [])
    {
        parent::__construct(
            $context, $layerStateFactory, $attributeCollectionFactory, $catalogProduct, $storeManager, $registry,
            $categoryRepository, $data);

        $this->catalogAttributeLayerStateFactory = $catalogAttributeLayerStateFactory;
    }

    public function getState(): State
    {
        $state = $this->getData('state');

        if ($state === null) {
            $state = $this->catalogAttributeLayerStateFactory->create();
            $this->setData('state', $state);
        }

        return $state;
    }
}
