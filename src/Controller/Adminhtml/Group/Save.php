<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Controller\Adminhtml\Group;

use Infrangible\CatalogAttributeLandingPage\Controller\Adminhtml\AbstractSave;
use Infrangible\CatalogAttributeLandingPage\Traits\Group;

/**
 * Saves a landing group.
 */
class Save extends AbstractSave
{
    use Group;

    /**
     * Returns the message after the creation.
     *
     * @return string
     */
    protected function getObjectCreatedMessage(): string
    {
        return __('The group has been created.')->render();
    }

    /**
     * Returns the message after the update.
     *
     * @return string
     */
    protected function getObjectUpdatedMessage(): string
    {
        return __('The group has been saved.')->render();
    }

    /**
     * Returns the IDs of the landing page and group that may have the URL key: the saved group.
     *
     * @param int|null $objectId
     *
     * @return array{0: int|null, 1: int|null}
     */
    protected function getUrlKeyOwnerIds(?int $objectId): array
    {
        return [null, $objectId];
    }
}
