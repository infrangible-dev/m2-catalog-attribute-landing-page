<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\ResourceModel;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Model\AbstractLanding as LandingModel;
use Infrangible\Core\Helper\Attribute;
use Magento\Catalog\Model\Product;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Loads and saves landing pages: the values of attributes with options are lists of option IDs.
 */
class Page extends AbstractLanding
{
    /**
     * @param Variables $variables
     * @param Attribute $attributeHelper
     * @param Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        Variables $variables,
        protected Attribute $attributeHelper,
        Context $context,
        ?string $connectionName = null
    ) {
        parent::__construct($variables, $context, $connectionName);
    }

    /**
     * Initializes the table and its ID field.
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('catalog_attribute_landing_page', 'page_id');
    }

    /**
     * Returns the name of the table with the store views.
     *
     * @return string
     */
    protected function getStoreTableName(): string
    {
        return 'catalog_attribute_landing_page_store';
    }

    /**
     * Converts the values of attributes with options and the sort attributes into lists.
     *
     * @param AbstractModel $object
     *
     * @return $this
     * @throws Exception
     */
    protected function _afterLoad(AbstractModel $object): static
    {
        parent::_afterLoad($object);

        for ($position = 1; $position <= LandingModel::ATTRIBUTE_COUNT; $position++) {
            $attributeId = $object->getData(sprintf('attribute_id%d', $position));

            if ($this->variables->isEmpty($attributeId)) {
                continue;
            }

            $attribute = $this->attributeHelper->getAttribute(
                Product::ENTITY,
                $this->variables->stringValue($attributeId)
            );

            if ($attribute->usesSource()) {
                $valueKey = sprintf('value%d', $position);
                $value = $object->getData($valueKey);

                $object->setData($valueKey, is_string($value) && $value !== '' ? explode(',', $value) : []);
            }
        }

        $attributeOrders = $object->getData('attribute_orders');

        $object->setData(
            'attribute_orders',
            is_string($attributeOrders) && $attributeOrders !== '' ? explode(',', $attributeOrders) : []
        );

        return $this;
    }

    /**
     * Converts the lists of values and sort attributes into texts, and empty values to null.
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object): static
    {
        parent::_beforeSave($object);

        for ($position = 1; $position <= LandingModel::ATTRIBUTE_COUNT; $position++) {
            $this->implodeList($object, sprintf('value%d', $position));

            if ($position > 1) {
                $this->setNullIfEmpty($object, sprintf('value%d', $position));
            }
        }

        $this->implodeList($object, 'attribute_orders');

        foreach (['attribute_orders', 'attribute_order', 'attribute_order_direction', 'attribute_set_id',
                     'additional_cms_block_id', 'seo_cms_block_id', 'image', 'logo', 'thumbnail'] as $key) {

            $this->setNullIfEmpty($object, $key);
        }

        return $this;
    }

    /**
     * Converts a list in the field into a comma-separated text.
     *
     * @param AbstractModel $object
     * @param string $key
     *
     * @return void
     */
    private function implodeList(AbstractModel $object, string $key): void
    {
        $value = $object->getData($key);

        if (is_array($value)) {
            $object->setData(
                $key,
                implode(
                    ',',
                    array_filter(
                        array_map([$this->variables, 'stringValue'], $value),
                        static fn(string $item): bool => $item !== ''
                    )
                )
            );
        }
    }
}
