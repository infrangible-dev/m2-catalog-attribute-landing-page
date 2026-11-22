<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Test\Unit\Model;

use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Helper\Data;
use Infrangible\CatalogAttributeLandingPage\Model\UrlKey;
use Infrangible\Core\Helper\Cms;
use Infrangible\Core\Helper\Stores;
use Infrangible\Core\Helper\UrlRewrite;
use Magento\Cms\Model\Page;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * Tests the check of the URL keys of landing pages and groups.
 */
#[AllowMockObjectsWithoutExpectations]
class UrlKeyTest extends TestCase
{
    /**
     * Tests that spaces and slashes at the start and end are removed.
     *
     * @return void
     */
    public function testNormalize(): void
    {
        $urlKey = $this->createUrlKey($this->createStub(Data::class));

        $this->assertSame('black-products', $urlKey->normalize(' /black-products/ '));
        $this->assertSame('a/b', $urlKey->normalize('a/b'));
        $this->assertSame('', $urlKey->normalize(null));
    }

    /**
     * Tests that an empty URL key is refused.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValidateEmpty(): void
    {
        $this->expectException(LocalizedException::class);

        $this->createUrlKey($this->createStub(Data::class))->validate('', [1]);
    }

    /**
     * Tests that the URL key of another page is refused, also of an inactive one, but not the own one.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValidatePage(): void
    {
        $helper = $this->createStub(Data::class);
        $helper->method('getPageIdByUrlKey')->willReturn(4);
        $helper->method('getGroupIdByUrlKey')->willReturn(null);

        $urlKey = $this->createUrlKey($helper);

        $urlKey->validate('black', [1], 4);

        $this->expectExceptionMessage('The URL key is used by another landing page.');

        $urlKey->validate('black', [1], 5);
    }

    /**
     * Tests that the URL key of a group is refused for a page.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValidateGroup(): void
    {
        $helper = $this->createStub(Data::class);
        $helper->method('getPageIdByUrlKey')->willReturn(null);
        $helper->method('getGroupIdByUrlKey')->willReturn(2);

        $this->expectExceptionMessage('The URL key is used by another landing group.');

        $this->createUrlKey($helper)->validate('colors', [1], 1);
    }

    /**
     * Tests that the URL key with the URL suffix of a CMS page is refused.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValidateCmsPage(): void
    {
        $helper = $this->createStub(Data::class);

        $cmsPage = $this->createStub(Page::class);
        $cmsPage->method('getId')->willReturn('3');

        $cmsHelper = $this->createMock(Cms::class);
        $cmsHelper->expects($this->once())->method('loadCmsPageByIdentifier')->with('about.html', 1)->willReturn(
            $cmsPage
        );

        $this->expectExceptionMessage('The URL key is used by a CMS page.');

        $this->createUrlKey($helper, $cmsHelper)->validate('about', [1]);
    }

    /**
     * Tests that the store ID 0 (all store views) checks the URL key in every store view.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValidateAllStores(): void
    {
        $helper = $this->createMock(Data::class);
        $helper->expects($this->exactly(2))->method('getPageIdByUrlKey')->willReturn(null);
        $helper->method('getGroupIdByUrlKey')->willReturn(null);

        $this->createUrlKey($helper)->validate('red', ['0', '2']);
    }

    /**
     * Returns the check with the helper, CMS pages and URL rewrites without matches, and the store views 1 and 2.
     *
     * @param Data $helper
     * @param Cms|null $cmsHelper
     *
     * @return UrlKey
     */
    private function createUrlKey(Data $helper, ?Cms $cmsHelper = null): UrlKey
    {
        $store1 = $this->createStub(Store::class);
        $store1->method('getId')->willReturn('1');

        $store2 = $this->createStub(Store::class);
        $store2->method('getId')->willReturn('2');

        $storeHelper = $this->createStub(Stores::class);
        $storeHelper->method('getStores')->willReturn([$store1, $store2]);
        $storeHelper->method('getStoreConfig')->willReturn('.html');

        // the stubs return pages and URL rewrites without ID
        return new UrlKey(
            $helper,
            $storeHelper,
            $cmsHelper ?? $this->createStub(Cms::class),
            $this->createStub(UrlRewrite::class),
            new Variables()
        );
    }
}
