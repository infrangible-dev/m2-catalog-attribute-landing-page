<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Group;

use Infrangible\CatalogAttributeLandingPage\Model\Group;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\AbstractLandingCollection;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Group as GroupResource;

/**
 * Collection of landing groups.
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
        $this->_init(Group::class, GroupResource::class);
    }

    /**
     * Returns the name of the table with the store views.
     *
     * @return string
     */
    protected function getStoreTableName(): string
    {
        return 'catalog_attribute_landing_group_store';
    }
}
