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
 * Returns the grid of the landing pages (AJAX).
 */
class Grid extends \Infrangible\BackendWidget\Controller\Backend\Object\Grid
{
    use Page;
}
