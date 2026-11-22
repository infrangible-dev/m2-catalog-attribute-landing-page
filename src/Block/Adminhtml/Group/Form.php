<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Block\Adminhtml\Group;

use Infrangible\CatalogAttributeLandingPage\Block\Adminhtml\AbstractForm;
use Infrangible\CatalogAttributeLandingPage\Model\AbstractLanding;

/**
 * Form of a landing group: URL key, texts, attributes, store views and SEO.
 */
class Form extends AbstractForm
{
    /**
     * Adds the fields of the landing group.
     *
     * @param \Magento\Framework\Data\Form $form
     *
     * @return void
     */
    protected function prepareFields(\Magento\Framework\Data\Form $form): void
    {
        $this->addGeneralFieldset($form);

        $fieldSet = $form->addFieldset('attribute', ['legend' => __('Attribute')]);

        for ($position = 1; $position <= AbstractLanding::ATTRIBUTE_COUNT; $position++) {
            $this->addEavAttributeProductFilterableField(
                $fieldSet,
                sprintf('attribute_id%d', $position),
                __('Attribute #%1', $position)->render(),
                $position === 1
            );
        }

        $this->addAvailabilityFieldset($form);
        $this->addSeoFieldset($form);
    }
}
