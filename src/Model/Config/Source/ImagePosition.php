<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ImagePosition
    implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'headline',
                'label' => __('Before Headline')],
            [
                'value' => 'description',
                'label' => __('Before Description')],
            [
                'value' => 'cms',
                'label' => __('Before CMS Block')],
            [
                'value' => 'product_list',
                'label' => __('Before Product List')]];
    }
}
