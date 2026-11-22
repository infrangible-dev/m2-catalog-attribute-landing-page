<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Test\Unit\Block\Group;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Block\Group\Grouped;
use Infrangible\CatalogAttributeLandingPage\Model\Group;
use Infrangible\CatalogAttributeLandingPage\Model\GroupFactory;
use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Page\Collection;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Page\CollectionFactory;
use Infrangible\CatalogAttributeLandingPage\Test\Unit\CreatesPages;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Registry;
use Infrangible\Core\Helper\Stores;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests the grouping of the landing pages of a group.
 */
#[AllowMockObjectsWithoutExpectations]
class GroupedTest extends TestCase
{
    use CreatesPages;

    /**
     * Tests the grouping by the labels of the options, sorted naturally on all levels.
     *
     * The values of pages in collections are comma-separated texts: before, pages with several values of a
     * multiselect attribute were left out.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testGetGroupedPages(): void
    {
        $redM = $this->createPage(
            ['attribute_id1' => '93', 'value1' => '58', 'attribute_id2' => '144', 'value2' => '168']
        );
        $redL = $this->createPage(
            ['attribute_id1' => '93', 'value1' => '58', 'attribute_id2' => '144', 'value2' => '169']
        );
        $blueM = $this->createPage(
            ['attribute_id1' => '93', 'value1' => '50', 'attribute_id2' => '144', 'value2' => '168']
        );
        $redBlue = $this->createPage(
            ['attribute_id1' => '93', 'value1' => '58,50', 'attribute_id2' => '144', 'value2' => '168']
        );
        $missing = $this->createPage(
            ['attribute_id1' => '93', 'value1' => '58', 'attribute_id2' => '144', 'value2' => '']
        );

        $grouped = $this->createGrouped([$redM, $redL, $blueM, $redBlue, $missing]);

        $this->assertSame(
            ['Blue' => ['M' => $blueM], 'Red' => ['L' => $redL, 'M' => $redM], 'Red, Blue' => ['M' => $redBlue]],
            $grouped->getGroupedPages()
        );
        $this->assertFalse($grouped->isPages($grouped->getGroupedPages()));
        $this->assertTrue($grouped->isPages(['L' => $redL]));
    }

    /**
     * Returns the block with the pages of the collection, the option labels Red (58), Blue (50), M (168) and L (169),
     * and a group with the attributes 93 and 144.
     *
     * @param Page[] $pages
     *
     * @return Grouped
     */
    private function createGrouped(array $pages): Grouped
    {
        $collection = $this->createStub(Collection::class);
        $collection->method('getItems')->willReturn($pages);

        $collectionFactory = $this->createStub(CollectionFactory::class);
        $collectionFactory->method('create')->willReturn($collection);

        $source = $this->createStub(AbstractSource::class);
        $source->method('getOptionText')->willReturnMap(
            [['58', 'Red'], ['50', 'Blue'], ['168', 'M'], ['169', 'L']]
        );

        $attribute = $this->createStub(EavAttribute::class);
        $attribute->method('usesSource')->willReturn(true);
        $attribute->method('getSource')->willReturn($source);

        $eavAttributeHelper = $this->createStub(Attribute::class);
        $eavAttributeHelper->method('getAttribute')->willReturn($attribute);

        $group = $this->createStub(Group::class);
        $group->method('getAttributeId')->willReturnMap([[1, 93], [2, 144], [3, null], [4, null], [5, null]]);

        $registryHelper = $this->createStub(Registry::class);
        $registryHelper->method('registry')->willReturn($group);

        $store = $this->createStub(Store::class);
        $store->method('getId')->willReturn('1');

        $storeHelper = $this->createStub(Stores::class);
        $storeHelper->method('getStore')->willReturn($store);

        return new Grouped(
            $this->createStub(Context::class),
            $registryHelper,
            $storeHelper,
            new Variables(),
            new Arrays(),
            $eavAttributeHelper,
            $this->createStub(LoggerInterface::class),
            $this->createStub(GroupFactory::class),
            $collectionFactory
        );
    }
}
