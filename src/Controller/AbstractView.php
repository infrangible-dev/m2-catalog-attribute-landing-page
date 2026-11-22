<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Controller;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Model\AbstractLanding;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Registry;
use Infrangible\Core\Helper\Stores;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

/**
 * Shows an active landing page or group of the current store view.
 */
abstract class AbstractView implements HttpGetActionInterface
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
     */
    public function __construct(
        protected RequestInterface $request,
        protected PageFactory $resultPageFactory,
        protected ForwardFactory $resultForwardFactory,
        protected Registry $registryHelper,
        protected Attribute $eavAttributeHelper,
        protected Stores $storeHelper,
        protected Variables $variables,
        protected LoggerInterface $logging
    ) {
    }

    /**
     * Returns the result that shows the page "not found".
     *
     * @return Forward
     */
    protected function notFound(): Forward
    {
        return $this->resultForwardFactory->create()->forward('noRoute');
    }

    /**
     * Checks that the landing page or group exists, is active and belongs to the current store view.
     *
     * @param AbstractLanding $object
     *
     * @return bool
     */
    protected function isAvailable(AbstractLanding $object): bool
    {
        if (! $object->getId() || ! $object->getData('active')) {
            return false;
        }

        try {
            $storeId = $this->variables->intValue($this->storeHelper->getStore()->getId());
        } catch (NoSuchEntityException $exception) {
            $this->logging->error($exception);

            return false;
        }

        $storeIds = $object->getStoreIds();

        return in_array(0, $storeIds, true) || in_array($storeId, $storeIds, true);
    }

    /**
     * Returns the codes of the attributes by position, or null if an attribute is missing or not filterable.
     *
     * The layered navigation can only filter the products with filterable attributes.
     *
     * @param AbstractLanding $object
     *
     * @return array<int, string>|null
     */
    protected function getAttributeCodes(AbstractLanding $object): ?array
    {
        $attributeCodes = [];

        for ($position = 1; $position <= AbstractLanding::ATTRIBUTE_COUNT; $position++) {
            $attributeId = $object->getAttributeId($position);

            if ($attributeId === null) {
                continue;
            }

            try {
                $attribute = $this->eavAttributeHelper->getAttribute(Product::ENTITY, (string)$attributeId);
            } catch (Exception $exception) {
                $this->logging->error($exception);

                return null;
            }

            if (! $attribute->getData('is_filterable')) {
                $this->logging->error(
                    sprintf(
                        'Landing page or group uses the attribute %s, which is not filterable',
                        $attribute->getAttributeCode()
                    )
                );

                return null;
            }

            $attributeCodes[ $position ] = $attribute->getAttributeCode();
        }

        return $attributeCodes;
    }

    /**
     * Creates the result page with the layout handles and the title, description and keywords of the object.
     *
     * The handles are the base handle, the base handle with the attribute codes (and values, if given) and the base
     * handle with the ID of the object, so that layout files can change single pages or all pages of an attribute.
     *
     * @param AbstractLanding $object
     * @param string $baseHandle
     * @param string[] $handleSuffixes
     *
     * @return ResultPage
     */
    protected function createResultPage(AbstractLanding $object, string $baseHandle, array $handleSuffixes): ResultPage
    {
        $resultPage = $this->resultPageFactory->create();

        $resultPage->addHandle($baseHandle);

        foreach ($handleSuffixes as $handleSuffix) {
            $resultPage->addHandle(sprintf('%s_%s', $baseHandle, $this->getHandlePart($handleSuffix)));
        }

        $resultPage->addHandle(sprintf('%s_%d', $baseHandle, $this->variables->intValue($object->getId())));

        $pageConfig = $resultPage->getConfig();

        $pageConfig->getTitle()->set($object->getPageTitle());

        $description = $object->getMetaDescription();

        if ($description === '') {
            $description = trim((string)preg_replace('/\s+/', ' ', strip_tags($object->getDescription())));
        }

        if ($description !== '') {
            $pageConfig->setDescription($description);
        }

        if ($object->getMetaKeywords() !== '') {
            $pageConfig->setKeywords($object->getMetaKeywords());
        }

        return $resultPage;
    }

    /**
     * Returns the text as part of a layout handle: lower case, with an underscore instead of other characters.
     *
     * @param string $text
     *
     * @return string
     */
    protected function getHandlePart(string $text): string
    {
        return trim((string)preg_replace('/[^a-z0-9]+/', '_', strtolower($text)), '_');
    }
}
