<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Controller\Adminhtml;

use FeWeDev\Base\Variables;
use Infrangible\BackendWidget\Controller\Backend\Object\Save as ObjectSave;
use Infrangible\BackendWidget\Model\Backend\Session;
use Infrangible\CatalogAttributeLandingPage\Model\UrlKey;
use Infrangible\Core\Helper\Cache;
use Infrangible\Core\Helper\Instances;
use Infrangible\Core\Helper\Registry;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Psr\Log\LoggerInterface;

/**
 * Saves a landing page or group after checking its URL key, and cleans the full page cache.
 */
abstract class AbstractSave extends ObjectSave
{
    /**
     * @param Registry $registryHelper
     * @param Instances $instanceHelper
     * @param Context $context
     * @param LoggerInterface $logging
     * @param Session $session
     * @param UrlKey $urlKey
     * @param Cache $cacheHelper
     * @param Variables $variables
     */
    public function __construct(
        Registry $registryHelper,
        Instances $instanceHelper,
        Context $context,
        LoggerInterface $logging,
        Session $session,
        protected UrlKey $urlKey,
        protected Cache $cacheHelper,
        protected Variables $variables
    ) {
        parent::__construct($registryHelper, $instanceHelper, $context, $logging, $session);
    }

    /**
     * Normalizes the URL key and checks that no other page, group, CMS page or URL rewrite uses it.
     *
     * @param AbstractModel $object
     *
     * @return void
     * @throws LocalizedException
     */
    protected function beforeSave(AbstractModel $object): void
    {
        parent::beforeSave($object);

        $urlKey = $this->urlKey->normalize($object->getData('url_key'));
        $storeIds = $object->getData('store_ids');
        $objectId = $object->getId() === null ? null : $this->variables->intValue($object->getId());

        $object->setData('url_key', $urlKey);

        [$pageId, $groupId] = $this->getUrlKeyOwnerIds($objectId);

        $this->urlKey->validate($urlKey, is_array($storeIds) ? $storeIds : [], $pageId, $groupId);
    }

    /**
     * Returns the IDs of the landing page and group that may have the URL key: the saved object.
     *
     * @param int|null $objectId
     *
     * @return array{0: int|null, 1: int|null}
     */
    abstract protected function getUrlKeyOwnerIds(?int $objectId): array;

    /**
     * Cleans the full page cache, which contains the landing pages and groups.
     *
     * @param AbstractModel $object
     *
     * @return void
     */
    protected function afterSave(AbstractModel $object): void
    {
        parent::afterSave($object);

        $this->cacheHelper->cleanFullPageCache();
    }
}
