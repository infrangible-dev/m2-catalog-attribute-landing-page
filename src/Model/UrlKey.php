<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model;

use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Helper\Data;
use Infrangible\Core\Helper\Cms;
use Infrangible\Core\Helper\Stores;
use Infrangible\Core\Helper\UrlRewrite;
use Magento\Framework\Exception\LocalizedException;

/**
 * Checks that the URL key of a landing page or group is not used by another page, group, CMS page or URL rewrite.
 */
class UrlKey
{
    /**
     * @param Data $catalogAttributeLandingPageHelper
     * @param Stores $storeHelper
     * @param Cms $cmsHelper
     * @param UrlRewrite $urlRewriteHelper
     * @param Variables $variables
     */
    public function __construct(
        protected Data $catalogAttributeLandingPageHelper,
        protected Stores $storeHelper,
        protected Cms $cmsHelper,
        protected UrlRewrite $urlRewriteHelper,
        protected Variables $variables
    ) {
    }

    /**
     * Returns the URL key without spaces and slashes at the start and end.
     *
     * @param mixed $urlKey
     *
     * @return string
     */
    public function normalize(mixed $urlKey): string
    {
        return trim($this->variables->stringValueOrDefault($urlKey), " \t\n\r\0\x0B/");
    }

    /**
     * Checks the URL key in the store views of the landing page or group (store ID 0: all store views).
     *
     * @param string $urlKey
     * @param array $storeIds
     * @param int|null $pageId the ID of the landing page that has the URL key, if any
     * @param int|null $groupId the ID of the landing group that has the URL key, if any
     *
     * @phpstan-param array<int|string, mixed> $storeIds
     *
     * @return void
     * @throws LocalizedException
     */
    public function validate(string $urlKey, array $storeIds, ?int $pageId = null, ?int $groupId = null): void
    {
        if ($urlKey === '') {
            throw new LocalizedException(__('Please enter a URL key.'));
        }

        foreach ($this->getCheckedStoreIds($storeIds) as $storeId) {
            $this->validateInStore($urlKey, $storeId, $pageId, $groupId);
        }
    }

    /**
     * Returns the IDs of the store views to check: all store views for store ID 0.
     *
     * @param array $storeIds
     *
     * @phpstan-param array<int|string, mixed> $storeIds
     *
     * @return int[]
     */
    protected function getCheckedStoreIds(array $storeIds): array
    {
        $checkedStoreIds = [];

        foreach ($storeIds as $storeId) {
            $storeId = $this->variables->intValue($storeId);

            if ($storeId === 0) {
                foreach ($this->storeHelper->getStores() as $store) {
                    $checkedStoreIds[] = $this->variables->intValue($store->getId());
                }
            } else {
                $checkedStoreIds[] = $storeId;
            }
        }

        return array_values(array_unique($checkedStoreIds));
    }

    /**
     * Checks the URL key in the store view.
     *
     * @param string $urlKey
     * @param int $storeId
     * @param int|null $pageId
     * @param int|null $groupId
     *
     * @return void
     * @throws LocalizedException
     */
    protected function validateInStore(string $urlKey, int $storeId, ?int $pageId, ?int $groupId): void
    {
        $usingPageId = $this->catalogAttributeLandingPageHelper->getPageIdByUrlKey($urlKey, $storeId);

        if ($usingPageId !== null && $usingPageId !== $pageId) {
            throw new LocalizedException(__('The URL key is used by another landing page.'));
        }

        $usingGroupId = $this->catalogAttributeLandingPageHelper->getGroupIdByUrlKey($urlKey, $storeId);

        if ($usingGroupId !== null && $usingGroupId !== $groupId) {
            throw new LocalizedException(__('The URL key is used by another landing group.'));
        }

        $suffix = $this->storeHelper->getStoreConfig('catalog/seo/category_url_suffix');
        $requestPath = $urlKey . (is_string($suffix) ? $suffix : '');

        if ($this->cmsHelper->loadCmsPageByIdentifier($requestPath, $storeId)->getId()) {
            throw new LocalizedException(__('The URL key is used by a CMS page.'));
        }

        if ($this->urlRewriteHelper->loadUrlRewrite($requestPath, $storeId)->getId()) {
            throw new LocalizedException(__('The URL key is used by a URL rewrite.'));
        }
    }
}
