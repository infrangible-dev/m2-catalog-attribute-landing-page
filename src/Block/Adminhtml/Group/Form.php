<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Block\Adminhtml\Group;

use Exception;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Form extends \Infrangible\BackendWidget\Block\Form
{
    /**
     * @throws Exception
     */
    protected function prepareFields(\Magento\Framework\Data\Form $form)
    {
        $fieldSet = $form->addFieldset(
            'general',
            ['legend' => __('General')]
        );

        $this->addTextField(
            $fieldSet,
            'url_key',
            __('Url Key')->render(),
            true
        );
        $this->addTextField(
            $fieldSet,
            'headline',
            __('Headline')->render(),
            true
        );
        $this->addTextareaField(
            $fieldSet,
            'description',
            __('Description')->render()
        );
        $this->addCmsBlockSelectField(
            $fieldSet,
            'cms_block_id'
        );

        $fieldSet = $form->addFieldset(
            'attribute',
            ['legend' => __('Attribute')]
        );

        $this->addEavAttributeProductFilterableField(
            $fieldSet,
            'attribute_id1',
            __('Attribute #1')->render(),
            true
        );
        $this->addEavAttributeProductFilterableField(
            $fieldSet,
            'attribute_id2',
            __('Attribute #2')->render()
        );
        $this->addEavAttributeProductFilterableField(
            $fieldSet,
            'attribute_id3',
            __('Attribute #3')->render()
        );
        $this->addEavAttributeProductFilterableField(
            $fieldSet,
            'attribute_id4',
            __('Attribute #4')->render()
        );
        $this->addEavAttributeProductFilterableField(
            $fieldSet,
            'attribute_id5',
            __('Attribute #5')->render()
        );

        $fieldSet = $form->addFieldset(
            'availability',
            ['legend' => __('Availability')]
        );

        $this->addStoreMultiselectField(
            $fieldSet,
            'store_ids'
        );
        $this->addYesNoField(
            $fieldSet,
            'active',
            __('Active')->render()
        );

        $fieldSet = $form->addFieldset(
            'seo',
            ['legend' => __('SEO')]
        );

        $this->addTextField(
            $fieldSet,
            'page_title',
            __('Page Title')->render()
        );
        $this->addTextareaField(
            $fieldSet,
            'meta_description',
            __('Meta Description')->render()
        );
        $this->addTextareaField(
            $fieldSet,
            'meta_keywords',
            __('Meta Keywords')->render()
        );
    }
}
