<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Controller\Adminhtml\Page;

use Infrangible\CatalogAttributeLandingPage\Traits\Page;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Delete
    extends \Infrangible\BackendWidget\Controller\Backend\Object\Delete
{
    use Page;

    protected function getObjectDeletedMessage(): string
    {
        return __('The page has been deleted.')->render();
    }
}
