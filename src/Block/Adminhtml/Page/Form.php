<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Block\Adminhtml\Page;

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
    protected function prepareFields(\Magento\Framework\Data\Form $form): void
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
        $this->addCmsBlockSelectField(
            $fieldSet,
            'additional_cms_block_id',
            __('Additional Block')->render()
        );
        $this->addCmsBlockSelectField(
            $fieldSet,
            'seo_cms_block_id',
            __('SEO Block')->render()
        );
        $this->addWysiwygField(
            $fieldSet,
            'image',
            __('Image')->render()
        );
        $this->addWysiwygField(
            $fieldSet,
            'logo',
            __('Logo')->render()
        );
        $this->addWysiwygField(
            $fieldSet,
            'thumbnail',
            __('Thumbnail')->render()
        );

        $fieldSet = $form->addFieldset(
            'attribute1',
            ['legend' => __('Attribute #1')]
        );

        $this->addEavAttributeProductFilterableFieldWithUpdate(
            $fieldSet,
            'attribute_id1',
            __('Attribute')->render(),
            ['value1'],
            true,
            true
        );
        $this->addEavAttributeValueField(
            $fieldSet,
            'attribute_id1',
            'value1',
            __('Attribute Value')->render(),
            true,
            true
        );

        $fieldSet = $form->addFieldset(
            'attribute2',
            ['legend' => __('Attribute #2')]
        );

        $this->addEavAttributeProductFilterableFieldWithUpdate(
            $fieldSet,
            'attribute_id2',
            __('Attribute')->render(),
            ['value2']
        );
        $this->addEavAttributeValueField(
            $fieldSet,
            'attribute_id2',
            'value2',
            __('Attribute Value')->render()
        );

        $fieldSet = $form->addFieldset(
            'attribute3',
            ['legend' => __('Attribute #3')]
        );

        $this->addEavAttributeProductFilterableFieldWithUpdate(
            $fieldSet,
            'attribute_id3',
            __('Attribute')->render(),
            ['value3']
        );
        $this->addEavAttributeValueField(
            $fieldSet,
            'attribute_id3',
            'value3',
            __('Attribute Value')->render()
        );

        $fieldSet = $form->addFieldset(
            'attribute4',
            ['legend' => __('Attribute #4')]
        );

        $this->addEavAttributeProductFilterableFieldWithUpdate(
            $fieldSet,
            'attribute_id4',
            __('Attribute')->render(),
            ['value4']
        );
        $this->addEavAttributeValueField(
            $fieldSet,
            'attribute_id4',
            'value4',
            __('Attribute Value')->render()
        );

        $fieldSet = $form->addFieldset(
            'attribute5',
            ['legend' => __('Attribute #5')]
        );

        $this->addEavAttributeProductFilterableFieldWithUpdate(
            $fieldSet,
            'attribute_id5',
            __('Attribute')->render(),
            ['value5']
        );
        $this->addEavAttributeValueField(
            $fieldSet,
            'attribute_id5',
            'value5',
            __('Attribute Value')->render()
        );

        $fieldSet = $form->addFieldset(
            'attribute_set',
            ['legend' => __('Attribute Set')]
        );

        $this->addEavAttributeSetField(
            $fieldSet,
            'attribute_set_id',
            __('Attribute Set')->render()
        );

        $fieldSet = $form->addFieldset(
            'order',
            ['legend' => __('Attribute Order')]
        );

        $this->addAttributeSortByField(
            $fieldSet,
            'attribute_orders',
            __('Available Attributes')->render(),
            false,
            true
        );
        $this->addAttributeSortByField(
            $fieldSet,
            'attribute_order',
            __('Attribute')->render()
        );

        $attributeOrderDirection = [
            ['value' => '', 'label' => '-- Please select --'],
            ['value' => 'asc', 'label' => 'Ascending'],
            ['value' => 'desc', 'label' => 'Descending']
        ];

        $this->addOptionsField(
            $fieldSet,
            'attribute_order_direction',
            __('Direction')->render(),
            $attributeOrderDirection,
            ''
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
        $this->addYesNoField(
            $fieldSet,
            'check_active',
            __('Check Active')->render()
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
