<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Controller\Adminhtml\Page;

use Infrangible\CatalogAttributeLandingPage\Traits\Page;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class MassDelete
    extends \Infrangible\BackendWidget\Controller\Backend\Object\MassDelete
{
    use Page;

    protected function getObjectsDeletedMessage(): string
    {
        return __('%d page(s) have been deleted.')->render();
    }
}
