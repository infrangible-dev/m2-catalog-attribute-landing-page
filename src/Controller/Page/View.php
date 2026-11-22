<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Controller\Page;

use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Controller\AbstractView;
use Infrangible\CatalogAttributeLandingPage\Helper\Data;
use Infrangible\CatalogAttributeLandingPage\Model\PageFactory as LandingPageFactory;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\PageFactory as LandingPageResourceFactory;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Registry;
use Infrangible\Core\Helper\Stores;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

/**
 * Shows a landing page: the products with the attribute values of the page in the layered navigation of the layer
 * "landing_page".
 */
class View extends AbstractView
{
    /**
     * @param RequestInterface $request
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Registry $registryHelper
     * @param Attribute $eavAttributeHelper
     * @param Stores $storeHelper
     * @param Variables $variables
     * @param LoggerInterface $logging
     * @param Data $helper
     * @param LandingPageFactory $pageFactory
     * @param LandingPageResourceFactory $pageResourceFactory
     * @param Resolver $layerResolver
     */
    public function __construct(
        RequestInterface $request,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Registry $registryHelper,
        Attribute $eavAttributeHelper,
        Stores $storeHelper,
        Variables $variables,
        LoggerInterface $logging,
        protected Data $helper,
        protected LandingPageFactory $pageFactory,
        protected LandingPageResourceFactory $pageResourceFactory,
        protected Resolver $layerResolver
    ) {
        parent::__construct(
            $request,
            $resultPageFactory,
            $resultForwardFactory,
            $registryHelper,
            $eavAttributeHelper,
            $storeHelper,
            $variables,
            $logging
        );
    }

    /**
     * Shows the landing page of the request parameter page_id.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $page = $this->pageFactory->create();

        $this->pageResourceFactory->create()->load(
            $page,
            $this->variables->intValue($this->request->getParam('page_id'))
        );

        if (! $this->isAvailable($page)) {
            return $this->notFound();
        }

        $attributeCodes = $this->getAttributeCodes($page);

        if ($attributeCodes === null) {
            return $this->notFound();
        }

        $attributeValues = [];

        foreach ($attributeCodes as $position => $attributeCode) {
            $value = $page->getValue($position);

            $this->helper->addValue($attributeCode, $value);

            $attributeValues[] = is_array($value) ? implode('_', $value) : (string)$value;
        }

        $attributeSetId = $page->getAttributeSetId();

        if ($attributeSetId !== null) {
            $this->helper->addValue('attribute_set_id', $attributeSetId);
        }

        $this->registryHelper->register('current_page', $page, true);
        $this->registryHelper->register('attribute_filter', array_values($attributeCodes), true);

        $this->layerResolver->create('landing_page');

        $attributeCodesHandle = implode('_', $attributeCodes);

        return $this->createResultPage(
            $page,
            'infrangible_catalogattributelandingpage_page_view',
            [$attributeCodesHandle, sprintf('%s_%s', $attributeCodesHandle, implode('_', $attributeValues))]
        );
    }
}
