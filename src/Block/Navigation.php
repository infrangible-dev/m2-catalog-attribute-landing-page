<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Block;

use Infrangible\CatalogAttributeLandingPage\Model\Layer\FilterList;
use Magento\Catalog\Model\Layer\AvailabilityFlagInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\View\Element\Template\Context;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Navigation
    extends \Magento\LayeredNavigation\Block\Navigation
{
    public function __construct(
        Context $context,
        Resolver $layerResolver,
        FilterList $filterList,
        AvailabilityFlagInterface $visibilityFlag,
        array $data = [])
    {
        parent::__construct($context, $layerResolver, $filterList, $visibilityFlag, $data);
    }
}
