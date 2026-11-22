<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Test\Unit\Model;

use Infrangible\CatalogAttributeLandingPage\Test\Unit\CreatesPages;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * Tests the values of landing pages.
 */
#[AllowMockObjectsWithoutExpectations]
class PageTest extends TestCase
{
    use CreatesPages;

    /**
     * Tests the typed values of the database fields, which are texts or null.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValues(): void
    {
        $page = $this->createPage(
            [
                'attribute_id1'             => '93',
                'attribute_id2'             => null,
                'value1'                    => ['58', 49],
                'value2'                    => '',
                'value3'                    => 'cotton',
                'attribute_set_id'          => '9',
                'attribute_orders'          => 'name,,price',
                'attribute_order_direction' => 'down',
                'cms_block_id'              => '0',
                'store_ids'                 => ['0', '2'],
                'headline'                  => 'Red',
                'page_title'                => ''
            ]
        );

        $this->assertSame(93, $page->getAttributeId(1));
        $this->assertNull($page->getAttributeId(2));
        $this->assertSame(['58', '49'], $page->getValue(1));
        $this->assertNull($page->getValue(2));
        $this->assertSame('cotton', $page->getValue(3));
        $this->assertSame(9, $page->getAttributeSetId());
        $this->assertSame(['name', 'price'], $page->getAttributeOrders());
        // only asc and desc are directions
        $this->assertNull($page->getAttributeOrderDirection());
        $this->assertNull($page->getCmsBlockId());
        $this->assertSame([0, 2], $page->getStoreIds());
        // the title falls back to the headline
        $this->assertSame('Red', $page->getPageTitle());
        $this->assertNull($page->getImage());
    }

    /**
     * Tests that the fields of the attributes can be read and set by the forms of the admin.
     *
     * Magento's magic methods convert getAttributeId1() into attribute_id_1 (since 2.4.7 also the methods that the
     * forms use), so that the forms showed no attributes.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testNumberedFields(): void
    {
        $page = $this->createPage(['attribute_id1' => '93', 'value1' => ['58']]);

        $this->assertSame('93', $page->getDataUsingMethod('attribute_id1'));
        $this->assertSame(['58'], $page->getDataUsingMethod('value1'));

        $page->setDataUsingMethod('attribute_id2', '144');

        $this->assertSame('144', $page->getData('attribute_id2'));
        $this->assertSame('Red', $this->createPage(['headline' => 'Red'])->getDataUsingMethod('headline'));
    }

    /**
     * Tests the URL path with the URL suffix of categories.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testGetUrlPath(): void
    {
        $this->assertSame('red.html', $this->createPage(['url_key' => 'red'], '.html')->getUrlPath());
        $this->assertSame('red', $this->createPage(['url_key' => 'red'])->getUrlPath());
    }

    /**
     * Tests the URL of the image: without double slash, null without image.
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testGetImageUrl(): void
    {
        $page = $this->createPage(['image' => '/wysiwyg/red.jpg', 'logo' => 'logo.png']);

        $this->assertSame('https://example.com/media/wysiwyg/red.jpg', $page->getImageUrl());
        $this->assertSame('https://example.com/media/logo.png', $page->getLogoUrl());
        $this->assertNull($page->getThumbnailUrl());
    }
}
