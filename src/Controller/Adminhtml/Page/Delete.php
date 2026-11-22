<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Controller\Adminhtml\Page;

use Infrangible\CatalogAttributeLandingPage\Traits\Page;

/**
 * Deletes a landing page.
 */
class Delete extends \Infrangible\BackendWidget\Controller\Backend\Object\Delete
{
    use Page;

    /**
     * Returns the message after the deletion.
     *
     * @return string
     */
    protected function getObjectDeletedMessage(): string
    {
        return __('The page has been deleted.')->render();
    }
}
