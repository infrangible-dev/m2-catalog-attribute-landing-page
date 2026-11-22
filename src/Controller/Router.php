<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Controller;

use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Helper\Data;
use Infrangible\Core\Helper\Stores;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

/**
 * Forwards the URLs of the landing pages and groups (URL key with the URL suffix of categories) to their views.
 */
class Router implements RouterInterface
{
    /** Front name of the views of the landing pages and groups. */
    public const FRONT_NAME = 'attribute_landing_page';

    /**
     * @param Stores $storeHelper
     * @param Data $catalogAttributeLandingPageHelper
     * @param ActionFactory $actionFactory
     * @param Variables $variables
     * @param LoggerInterface $logging
     */
    public function __construct(
        protected Stores $storeHelper,
        protected Data $catalogAttributeLandingPageHelper,
        protected ActionFactory $actionFactory,
        protected Variables $variables,
        protected LoggerInterface $logging
    ) {
    }

    /**
     * Forwards the request to the view of the active landing page or group with the URL of the request.
     *
     * @param RequestInterface $request
     *
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request): ?ActionInterface
    {
        if (! $request instanceof Http ||
            ($request->getModuleName() === self::FRONT_NAME && $request->getActionName() === 'noRoute')) {

            return null;
        }

        $identifier = trim($request->getPathInfo(), '/');
        $urlKey = $this->getUrlKey($identifier);

        if ($urlKey === null) {
            return null;
        }

        try {
            $storeId = $this->variables->intValue($this->storeHelper->getStore()->getId());
        } catch (NoSuchEntityException $exception) {
            $this->logging->error($exception);

            return null;
        }

        $page = $this->catalogAttributeLandingPageHelper->loadPage($urlKey, $storeId);
        $group = $page === null ? $this->catalogAttributeLandingPageHelper->loadGroup($urlKey, $storeId) : null;

        if ($page !== null) {
            return $this->forward($request, $identifier, 'page', 'page_id', $page->getId());
        }

        if ($group !== null) {
            return $this->forward($request, $identifier, 'group', 'group_id', $group->getId());
        }

        return null;
    }

    /**
     * Returns the URL key of the path: without the URL suffix of categories, or null if the path does not end with it.
     *
     * @param string $identifier
     *
     * @return string|null
     */
    protected function getUrlKey(string $identifier): ?string
    {
        $identifier = urldecode($identifier);

        $suffix = $this->storeHelper->getStoreConfig('catalog/seo/category_url_suffix');
        $suffix = is_string($suffix) ? $suffix : '';

        if ($suffix !== '') {
            if (! str_ends_with($identifier, $suffix)) {
                return null;
            }

            $identifier = substr($identifier, 0, -strlen($suffix));
        }

        return $identifier === '' ? null : $identifier;
    }

    /**
     * Forwards the request to the view of the controller with the ID.
     *
     * @param Http $request
     * @param string $identifier
     * @param string $controllerName
     * @param string $idParameter
     * @param mixed $id
     *
     * @return ActionInterface
     */
    protected function forward(
        Http $request,
        string $identifier,
        string $controllerName,
        string $idParameter,
        mixed $id
    ): ActionInterface {
        $request->setModuleName(self::FRONT_NAME);
        $request->setControllerName($controllerName);
        $request->setActionName('view');
        $request->setParam($idParameter, $id);
        $request->setAlias(UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $identifier);

        return $this->actionFactory->create(Forward::class);
    }
}
