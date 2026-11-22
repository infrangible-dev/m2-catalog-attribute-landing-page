<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * A landing group: lists the landing pages with the same attributes, grouped by their values.
 */
class Group extends AbstractLanding
{
    /**
     * Initializes the resource model.
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\Group::class);
    }
}
