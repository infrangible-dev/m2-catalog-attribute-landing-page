<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Block\Adminhtml\Group;

use Exception;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Grid
    extends \Infrangible\BackendWidget\Block\Grid
{
    protected function prepareCollection(AbstractDb $collection): void
    {
    }

    /**
     * @throws Exception
     */
    protected function prepareFields(): void
    {
        $this->addTextColumn('url_key', __('Url Key')->render());
        $this->addTextColumn('headline', __('Headline')->render());
        $this->addEavAttributeColumn('attribute_id1', __('Attribute #1')->render());
        $this->addEavAttributeColumn('attribute_id2', __('Attribute #2')->render());
        $this->addEavAttributeColumn('attribute_id3', __('Attribute #3')->render());
        $this->addEavAttributeColumn('attribute_id4', __('Attribute #4')->render());
        $this->addEavAttributeColumn('attribute_id5', __('Attribute #5')->render());
        $this->addYesNoColumn('active', __('Active')->render());
        $this->addStoreStructureColumn('store_ids');
        $this->addDatetimeColumn('created_at', __('Created At')->render());
        $this->addDatetimeColumn('updated_at', __('Updated At')->render());
    }

    /**
     * @return string[]
     */
    protected function getHiddenFieldNames(): array
    {
        return ['attribute_id2', 'attribute_id3', 'attribute_id4', 'attribute_id5'];
    }
}
