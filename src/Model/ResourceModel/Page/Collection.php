<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Page;

use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\AbstractLandingCollection;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Page as PageResource;

/**
 * Collection of landing pages.
 */
class Collection extends AbstractLandingCollection
{
    /**
     * Sets the model and the resource model.
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(Page::class, PageResource::class);
    }

    /**
     * Returns the name of the table with the store views.
     *
     * @return string
     */
    protected function getStoreTableName(): string
    {
        return 'catalog_attribute_landing_page_store';
    }
}
