<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Positions of the image of a landing page.
 */
class ImagePosition implements OptionSourceInterface
{
    /**
     * Returns the positions for a select field.
     *
     * @return array<int, array<string, mixed>>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'headline', 'label' => __('Before Headline')],
            ['value' => 'description', 'label' => __('Before Description')],
            ['value' => 'cms', 'label' => __('Before CMS Block')],
            ['value' => 'product_list', 'label' => __('Before Product List')]
        ];
    }
}
