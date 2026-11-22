<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Test\Unit\Model\Layer;

use Infrangible\CatalogAttributeLandingPage\Model\Layer\State;
use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * Tests the state of the layer of the landing pages.
 */
#[AllowMockObjectsWithoutExpectations]
class StateTest extends TestCase
{
    /**
     * Tests that the filters of the attributes of the landing page are not active filters.
     *
     * @return void
     */
    public function testGetFilters(): void
    {
        $registryHelper = $this->createStub(Registry::class);
        $registryHelper->method('registry')->willReturn(['color']);

        $state = new State($registryHelper);

        $color = $this->createFilterItem('color');
        $size = $this->createFilterItem('size');

        $state->addFilter($color);
        $state->addFilter($size);

        $this->assertSame([$size], $state->getFilters());
    }

    /**
     * Returns a filter item of the attribute.
     *
     * @param string $attributeCode
     *
     * @return Item
     */
    private function createFilterItem(string $attributeCode): Item
    {
        $attribute = $this->createStub(AbstractAttribute::class);
        $attribute->method('getAttributeCode')->willReturn($attributeCode);

        $filter = $this->createStub(AbstractFilter::class);
        $filter->method('getData')->willReturnMap([['attribute_model', null, $attribute]]);

        $item = $this->createStub(Item::class);
        $item->method('getData')->willReturnMap([['filter', null, $filter]]);

        return $item;
    }
}
