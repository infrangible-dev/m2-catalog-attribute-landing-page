<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Block\Adminhtml;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;

/**
 * Form of a landing page or group: the fields that both have.
 */
abstract class AbstractForm extends \Infrangible\BackendWidget\Block\Form
{
    /**
     * Adds the fieldset with the URL key, headline, description and CMS block.
     *
     * @param Form $form
     *
     * @return Fieldset
     */
    protected function addGeneralFieldset(Form $form): Fieldset
    {
        $fieldSet = $form->addFieldset('general', ['legend' => __('General')]);

        $this->addTextField($fieldSet, 'url_key', __('URL Key')->render(), true);
        $this->addTextField($fieldSet, 'headline', __('Headline')->render(), true);
        $this->addTextareaField($fieldSet, 'description', __('Description')->render());
        $this->addCmsBlockSelectField($fieldSet, 'cms_block_id');

        return $fieldSet;
    }

    /**
     * Adds the fieldset with the store views and the active flag.
     *
     * @param Form $form
     *
     * @return Fieldset
     */
    protected function addAvailabilityFieldset(Form $form): Fieldset
    {
        $fieldSet = $form->addFieldset('availability', ['legend' => __('Availability')]);

        $this->addStoreMultiselectField($fieldSet, 'store_ids');
        $this->addYesNoField($fieldSet, 'active', __('Active')->render());

        return $fieldSet;
    }

    /**
     * Adds the fieldset with the page title, meta description and meta keywords.
     *
     * @param Form $form
     *
     * @return void
     */
    protected function addSeoFieldset(Form $form): void
    {
        $fieldSet = $form->addFieldset('seo', ['legend' => __('SEO')]);

        $this->addTextField($fieldSet, 'page_title', __('Page Title')->render());
        $this->addTextareaField($fieldSet, 'meta_description', __('Meta Description')->render());
        $this->addTextareaField($fieldSet, 'meta_keywords', __('Meta Keywords')->render());
    }
}
