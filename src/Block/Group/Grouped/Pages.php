<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Block\Group\Grouped;

use Infrangible\CatalogAttributeLandingPage\Block\Group\Grouped\Pages\ProductList;
use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Pages extends Template
{
    /** @var Page[] */
    private $pages;

    /**
     * @return Page[]
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * @param Page[] $pages
     */
    public function setPages(array $pages)
    {
        $this->pages = $pages;
    }

    public function getPageProductsHtml(Page $page, int $limit = 5): string
    {
        /** @var ProductList $productListBlock */
        $productListBlock = $this->getChildBlock('product_list');

        $productListBlock->createProductCollection(
            $page,
            $limit
        );

        return $productListBlock->toHtml();
    }
}
