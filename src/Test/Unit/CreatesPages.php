<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Test\Unit;

use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Page as PageResource;
use Infrangible\Core\Helper\Stores;
use Infrangible\Core\Helper\Url;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Store\Model\Store;

/**
 * Creates landing pages with data for tests.
 */
trait CreatesPages
{
    /**
     * Returns a page with the data and the URL suffix of categories, in a store with the media URL
     * https://example.com/media/.
     *
     * @param array $data
     * @param string|null $urlSuffix
     *
     * @phpstan-param array<string, mixed> $data
     *
     * @return Page
     * @throws LocalizedException
     */
    private function createPage(array $data, ?string $urlSuffix = null): Page
    {
        $store = $this->createStub(Store::class);
        $store->method('getBaseUrl')->willReturn('https://example.com/media/');

        $storeHelper = $this->createStub(Stores::class);
        $storeHelper->method('getStoreConfig')->willReturn($urlSuffix);
        $storeHelper->method('getStore')->willReturn($store);

        $resource = $this->createStub(PageResource::class);
        $resource->method('getIdFieldName')->willReturn('page_id');

        /** @noinspection PhpDeprecationInspection, PhpFullyQualifiedNameUsageInspection the models need the registry */
        $registry = $this->createStub(\Magento\Framework\Registry::class);

        return new Page(
            $this->createStub(Context::class),
            $registry,
            $this->createStub(Url::class),
            $storeHelper,
            new Variables(),
            $resource,
            null,
            $data
        );
    }
}
