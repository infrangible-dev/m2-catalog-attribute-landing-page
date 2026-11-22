<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\ResourceModel;

/**
 * Loads and saves landing groups.
 */
class Group extends AbstractLanding
{
    /**
     * Initializes the table and its ID field.
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('catalog_attribute_landing_group', 'group_id');
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
