<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Block\Product;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ListProduct
    extends \Magento\Catalog\Block\Product\ListProduct
{
    /** @var Registry */
    protected $registryHelper;

    /** @var Attribute */
    protected $attributeHelper;

    /** @var Variables */
    protected $variables;

    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        Registry $registryHelper,
        Attribute $attributeHelper,
        Variables $variables,
        array $data = [])
    {
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);

        $this->registryHelper = $registryHelper;
        $this->attributeHelper = $attributeHelper;
        $this->variables = $variables;
    }

    /**
     * @param Category $category
     */
    public function prepareSortableFieldsByCategory($category): ListProduct
    {
        if (!$this->getDataUsingMethod('available_orders')) {
            /** @var Page $page */
            $page = $this->registryHelper->registry('current_page');

            $attributeOrders = $page->getAttributeOrders();

            if (!$this->variables->isEmpty($attributeOrders)) {
                $pageSortByOptions = [];

                if (is_array($attributeOrders)) {
                    foreach ($attributeOrders as $attributeCode) {
                        try {
                            $attribute = $this->attributeHelper->getAttribute(Product::ENTITY, $attributeCode);

                            $pageSortByOptions[$attributeCode] = $attribute->getStoreLabel();
                        } catch (Exception $exception) {
                        }
                    }
                }

                $this->setDataUsingMethod('available_orders', $pageSortByOptions);
            }
        }

        return parent::prepareSortableFieldsByCategory($category);
    }

    public function getDefaultSortBy(): ?string
    {
        /** @var Page $page */
        $page = $this->registryHelper->registry('current_page');

        return $page ? $page->getAttributeOrder() : null;
    }

    public function getDefaultDirection(): ?string
    {
        /** @var Page $page */
        $page = $this->registryHelper->registry('current_page');

        return $page ? $page->getAttributeOrderDirection() : null;
    }
}
