<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Block\Adminhtml\Page;

use Exception;
use Infrangible\CatalogAttributeLandingPage\Block\Adminhtml\AbstractForm;
use Infrangible\CatalogAttributeLandingPage\Model\AbstractLanding;

/**
 * Form of a landing page: URL key, texts, images, attributes and their values, sorting, store views and SEO.
 */
class Form extends AbstractForm
{
    /**
     * Adds the fields of the landing page.
     *
     * @param \Magento\Framework\Data\Form $form
     *
     * @return void
     * @throws Exception
     */
    protected function prepareFields(\Magento\Framework\Data\Form $form): void
    {
        $fieldSet = $this->addGeneralFieldset($form);

        $this->addCmsBlockSelectField($fieldSet, 'additional_cms_block_id', __('Additional Block')->render());
        $this->addCmsBlockSelectField($fieldSet, 'seo_cms_block_id', __('SEO Block')->render());
        $this->addWysiwygField($fieldSet, 'image', __('Image')->render());
        $this->addWysiwygField($fieldSet, 'logo', __('Logo')->render());
        $this->addWysiwygField($fieldSet, 'thumbnail', __('Thumbnail')->render());

        for ($position = 1; $position <= AbstractLanding::ATTRIBUTE_COUNT; $position++) {
            $fieldSet = $form->addFieldset(
                sprintf('attribute%d', $position),
                ['legend' => __('Attribute #%1', $position)]
            );

            $this->addEavAttributeProductFilterableFieldWithUpdate(
                $fieldSet,
                sprintf('attribute_id%d', $position),
                __('Attribute')->render(),
                [sprintf('value%d', $position)],
                $position === 1,
                $position === 1
            );
            $this->addEavAttributeValueField(
                $fieldSet,
                sprintf('attribute_id%d', $position),
                sprintf('value%d', $position),
                __('Attribute Value')->render(),
                $position === 1,
                $position === 1
            );
        }

        $fieldSet = $form->addFieldset('attribute_set', ['legend' => __('Attribute Set')]);

        $this->addEavAttributeSetField($fieldSet, 'attribute_set_id', __('Attribute Set')->render());

        $fieldSet = $form->addFieldset('order', ['legend' => __('Attribute Order')]);

        $this->addAttributeSortByField(
            $fieldSet,
            'attribute_orders',
            __('Available Attributes')->render(),
            false,
            true
        );
        $this->addAttributeSortByField($fieldSet, 'attribute_order', __('Attribute')->render());
        $this->addOptionsField(
            $fieldSet,
            'attribute_order_direction',
            __('Direction')->render(),
            [
                ['value' => '', 'label' => __('-- Please Select --')],
                ['value' => 'asc', 'label' => __('Ascending')],
                ['value' => 'desc', 'label' => __('Descending')]
            ],
            ''
        );

        $this->addAvailabilityFieldset($form);

        $this->addSeoFieldset($form);
    }
}
