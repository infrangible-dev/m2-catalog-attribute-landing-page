<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Controller\Adminhtml\Group;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\BackendWidget\Model\Backend\Session;
use Infrangible\CatalogAttributeLandingPage\Helper\Data;
use Infrangible\CatalogAttributeLandingPage\Traits\Group;
use Infrangible\Core\Helper\Cache;
use Infrangible\Core\Helper\Cms;
use Infrangible\Core\Helper\Instances;
use Infrangible\Core\Helper\Registry;
use Infrangible\Core\Helper\Stores;
use Infrangible\Core\Helper\UrlRewrite;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\AbstractModel;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Save
    extends \Infrangible\BackendWidget\Controller\Backend\Object\Save
{
    use Group;

    /** @var Stores */
    protected $storeHelper;

    /** @var Cms */
    protected $cmsHelper;

    /** @var UrlRewrite */
    protected $urlRewriteHelper;

    /** @var Cache */
    protected $cacheHelper;

    /** @var Data */
    protected $catalogAttributeLandingPageHelper;

    /** @var Variables */
    protected $variables;

    public function __construct(
        Registry $registryHelper,
        Instances $instanceHelper,
        Stores $storeHelper,
        Cms $cmsHelper,
        UrlRewrite $urlRewriteHelper,
        Cache $cacheHelper,
        Data $catalogAttributeLandingPageHelper,
        Context $context,
        LoggerInterface $logging,
        Session $session,
        Variables $variables)
    {
        parent::__construct($registryHelper, $instanceHelper, $context, $logging, $session);

        $this->storeHelper = $storeHelper;
        $this->cmsHelper = $cmsHelper;
        $this->urlRewriteHelper = $urlRewriteHelper;
        $this->cacheHelper = $cacheHelper;
        $this->catalogAttributeLandingPageHelper = $catalogAttributeLandingPageHelper;
        $this->variables = $variables;
    }

    protected function getObjectCreatedMessage(): string
    {
        return __('The group has been created.')->render();
    }

    protected function getObjectUpdatedMessage(): string
    {
        return __('The group has been saved.')->render();
    }

    /**
     * @param AbstractModel $object
     *
     * @throws Exception
     */
    protected function beforeSave(AbstractModel $object)
    {
        parent::beforeSave($object);

        $objectId = $object->getId() === null ? null : $this->variables->intValue($object->getId());

        $urlKey = $object->getData('url_key');
        $storeIds = $object->getData('store_ids');

        if (is_array($storeIds)) {
            foreach ($storeIds as $storeId) {
                $storeId = $this->variables->intValue($storeId);
                if ($storeId === 0) {
                    foreach ($this->storeHelper->getStores() as $store) {
                        $this->checkUrlKey($urlKey, $this->variables->intValue($store->getId()), $objectId);
                    }
                } else {
                    $this->checkUrlKey($urlKey, $storeId, $objectId);
                }
            }
        }
    }

    /**
     * @param string $urlKey
     * @param int $storeId
     * @param int|null $catalogAttributeLandingGroupId
     *
     * @throws Exception
     */
    protected function checkUrlKey(string $urlKey, int $storeId, int $catalogAttributeLandingGroupId = null)
    {
        $catalogAttributeLandingPage = $this->catalogAttributeLandingPageHelper->loadPage($urlKey, $storeId);

        if ($catalogAttributeLandingPage && $catalogAttributeLandingPage->getId()) {
            throw new Exception(__('The url key is use by a catalog attribute landing page!'));
        }

        $catalogAttributeLandingGroup = $this->catalogAttributeLandingPageHelper->loadGroup($urlKey, $storeId);

        if ($catalogAttributeLandingGroup && $catalogAttributeLandingGroup->getId() &&
            $catalogAttributeLandingGroup->getId() != $catalogAttributeLandingGroupId) {
            throw new Exception(__('The url key is use by another catalog attribute landing group!'));
        }

        $categoryUrlSuffix = $this->storeHelper->getStoreConfig('catalog/seo/category_url_suffix');

        if (!empty($categoryUrlSuffix)) {
            $urlKey .= $categoryUrlSuffix;
        }

        $cmsPage = $this->cmsHelper->loadCmsPageByIdentifier($urlKey, $storeId);

        if ($cmsPage->getId()) {
            throw new Exception(__('The url key is use by a CMS page!'));
        }

        $urlRewrite = $this->urlRewriteHelper->loadUrlRewrite($urlKey, $storeId);

        if ($urlRewrite->getId()) {
            throw new Exception(__('The url key is use by a URL rewrite!'));
        }
    }

    protected function afterSave(AbstractModel $object): void
    {
        parent::afterSave($object);

        $this->cacheHelper->cleanFullPageCache();
    }
}
