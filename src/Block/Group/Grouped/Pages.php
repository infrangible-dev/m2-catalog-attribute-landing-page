<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Block\Group\Grouped;

use Exception;
use Infrangible\CatalogAttributeLandingPage\Block\Group\Grouped\Pages\ProductList;
use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Magento\Framework\View\Element\Template;

/**
 * Shows the landing pages of the last level of a landing group, each with its first products.
 */
class Pages extends Template
{
    /** @var Page[] */
    private array $pages = [];

    /**
     * Returns the landing pages.
     *
     * @return Page[]
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * Sets the landing pages.
     *
     * @param Page[] $pages
     *
     * @return void
     */
    public function setPages(array $pages): void
    {
        $this->pages = $pages;
    }

    /**
     * Returns the HTML of the first products of the landing page.
     *
     * @param Page $page
     * @param int $limit
     *
     * @return string
     */
    public function getPageProductsHtml(Page $page, int $limit = 5): string
    {
        $productListBlock = $this->getChildBlock('product_list');

        if (! $productListBlock instanceof ProductList) {
            return '';
        }

        try {
            $productListBlock->createProductCollection($page, $limit);
        } catch (Exception $exception) {
            $this->_logger->error($exception);

            return '';
        }

        return $productListBlock->toHtml();
    }

    /**
     * Returns the URL of the thumbnail of the landing page, or null if it has none.
     *
     * @param Page $page
     *
     * @return string|null
     */
    public function getThumbnailUrl(Page $page): ?string
    {
        try {
            return $page->getThumbnailUrl();
        } catch (Exception $exception) {
            $this->_logger->error($exception);

            return null;
        }
    }
}
