<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Controller\Adminhtml\Group;

use Infrangible\CatalogAttributeLandingPage\Traits\Group;

/**
 * Shows the grid of the landing groups.
 */
class Index extends \Infrangible\BackendWidget\Controller\Backend\Object\Index
{
    use Group;
}
