<?php

declare(strict_types=1);

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
use Magento\Framework\UrlInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Router implements RouterInterface
{
    /** @var Stores */
    protected $storeHelper;

    /** @var Data */
    protected $catalogAttributeLandingPageHelper;

    /** @var ActionFactory */
    protected $actionFactory;

    /** @var Variables */
    protected $variables;

    public function __construct(
        Stores $storeHelper,
        Data $catalogAttributeLandingPageHelper,
        ActionFactory $actionFactory,
        Variables $variables
    ) {
        $this->storeHelper = $storeHelper;
        $this->catalogAttributeLandingPageHelper = $catalogAttributeLandingPageHelper;
        $this->actionFactory = $actionFactory;
        $this->variables = $variables;
    }

    /**
     * @throws \Exception
     */
    public function match(RequestInterface $request): ?ActionInterface
    {
        if (! $request instanceof Http) {
            return null;
        }

        $moduleName = $request->getModuleName();
        $actionName = $request->getActionName();

        if ($moduleName === 'attribute_landing_page' && $actionName === 'noRoute') {
            return null;
        }

        $identifier = trim(
            $request->getPathInfo(),
            '/'
        );

        $categoryUrlSuffix = $this->storeHelper->getStoreConfig('catalog/seo/category_url_suffix');

        $modelIdentifier = empty($categoryUrlSuffix) ? $identifier : substr(
            $identifier,
            0,
            -strlen($categoryUrlSuffix)
        );

        $catalogAttributeLandingPage = $this->catalogAttributeLandingPageHelper->loadPage(
            $modelIdentifier,
            $this->variables->intValue($this->storeHelper->getStore()->getId())
        );

        if ($catalogAttributeLandingPage && $catalogAttributeLandingPage->getId()) {
            $request->setModuleName('attribute_landing_page');
            $request->setControllerName('page');
            $request->setActionName('view');
            $request->setParam(
                'page_id',
                $catalogAttributeLandingPage->getId()
            );
            $request->setAlias(
                UrlInterface::REWRITE_REQUEST_PATH_ALIAS,
                $identifier
            );

            return $this->actionFactory->create(Forward::class);
        }

        $catalogAttributeLandingGroup = $this->catalogAttributeLandingPageHelper->loadGroup(
            $modelIdentifier,
            $this->variables->intValue($this->storeHelper->getStore()->getId())
        );

        if ($catalogAttributeLandingGroup && $catalogAttributeLandingGroup->getId()) {
            $request->setModuleName('attribute_landing_page');
            $request->setControllerName('group');
            $request->setActionName('view');
            $request->setParam(
                'group_id',
                $catalogAttributeLandingGroup->getId()
            );
            $request->setAlias(
                UrlInterface::REWRITE_REQUEST_PATH_ALIAS,
                $identifier
            );

            return $this->actionFactory->create(Forward::class);
        }

        return null;
    }
}
