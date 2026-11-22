<?php /** @noinspection PhpDeprecationInspection Magento's product list needs the context of product blocks. */

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Block\Product;

use Exception;
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
 * Product list of a landing page: sortable by the attributes of the page, sorted by its sort attribute and direction.
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $urlHelper
     * @param Registry $registryHelper
     * @param Attribute $attributeHelper
     * @param array $data
     *
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        protected Registry $registryHelper,
        protected Attribute $attributeHelper,
        array $data = []
    ) {
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    /**
     * Returns the current landing page, or null outside of a landing page.
     *
     * @return Page|null
     */
    protected function getPage(): ?Page
    {
        $page = $this->registryHelper->registry('current_page');

        return $page instanceof Page ? $page : null;
    }

    /**
     * Sets the attributes of the page as the available sort orders, before the defaults of the category.
     *
     * @param Category $category
     *
     * @return $this
     */
    public function prepareSortableFieldsByCategory($category): static
    {
        $attributeOrders = $this->getPage()?->getAttributeOrders() ?? [];

        if (! $this->getDataUsingMethod('available_orders') && count($attributeOrders) > 0) {
            $sortByOptions = [];

            foreach ($attributeOrders as $attributeCode) {
                try {
                    $attribute = $this->attributeHelper->getAttribute(Product::ENTITY, $attributeCode);

                    $sortByOptions[ $attributeCode ] = $attribute->getStoreLabel();
                } catch (Exception $exception) {
                    $this->_logger->error($exception);
                }
            }

            $this->setDataUsingMethod('available_orders', $sortByOptions);
        }

        parent::prepareSortableFieldsByCategory($category);

        return $this;
    }

    /**
     * Returns the sort attribute of the page.
     *
     * @return string|null
     */
    public function getDefaultSortBy(): ?string
    {
        return $this->getPage()?->getAttributeOrder();
    }

    /**
     * Returns the sort direction of the page, or the default direction.
     *
     * @return string|null
     */
    public function getDefaultDirection(): ?string
    {
        $defaultDirection = $this->getData('default_direction');

        return $this->getPage()?->getAttributeOrderDirection() ??
            (is_string($defaultDirection) ? $defaultDirection : null);
    }
}
