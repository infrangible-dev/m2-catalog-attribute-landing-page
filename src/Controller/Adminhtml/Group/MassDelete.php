<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Controller\Adminhtml\Group;

use Infrangible\CatalogAttributeLandingPage\Traits\Group;
use Magento\Framework\Phrase;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class MassDelete
    extends \Infrangible\BackendWidget\Controller\Backend\Object\MassDelete
{
    use Group;

    /**
     * @return Phrase
     */
    protected function getObjectsDeletedMessage(): string
    {
        return __('%d group(s) have been deleted.')->render();
    }
}
