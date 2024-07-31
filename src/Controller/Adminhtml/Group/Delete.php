<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Controller\Adminhtml\Group;

use Infrangible\CatalogAttributeLandingPage\Traits\Group;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Delete
    extends \Infrangible\BackendWidget\Controller\Backend\Object\Delete
{
    use Group;

    protected function getObjectDeletedMessage(): string
    {
        return __('The group has been deleted.')->render();
    }
}
