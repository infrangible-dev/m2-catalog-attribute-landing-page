<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Controller\Page;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Helper\Data;
use Infrangible\CatalogAttributeLandingPage\Model\PageFactory;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Model\Layer\Category\FilterableAttributeList;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class View extends Action
{
    /** @var Registry */
    protected $registryHelper;

    /** @var Variables */
    protected $variables;

    /** @var Attribute */
    protected $eavAttributeHelper;

    /** @var Data */
    protected $helper;

    /** @var LoggerInterface */
    protected $logging;

    /** @var PageFactory */
    protected $pageFactory;

    /** @var \Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\PageFactory */
    protected $pageResourceFactory;

    /** @var Resolver */
    protected $layerResolver;

    /** @var FilterableAttributeList */
    protected $filterableAttributeList;

    public function __construct(
        Context $context,
        Registry $registryHelper,
        Variables $variables,
        Attribute $eavAttributeHelper,
        Data $helper,
        LoggerInterface $logging,
        PageFactory $pageFactory,
        \Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\PageFactory $pageResourceFactory,
        Resolver $layerResolver,
        FilterableAttributeList $filterableAttributeList
    ) {
        parent::__construct($context);

        $this->registryHelper = $registryHelper;
        $this->variables = $variables;
        $this->eavAttributeHelper = $eavAttributeHelper;
        $this->helper = $helper;
        $this->logging = $logging;
        $this->pageFactory = $pageFactory;
        $this->pageResourceFactory = $pageResourceFactory;
        $this->layerResolver = $layerResolver;
        $this->filterableAttributeList = $filterableAttributeList;
    }

    /**
     * @return ResultInterface|void
     */
    public function execute()
    {
        $pageId = $this->getRequest()->getParam('page_id');

        if (! $pageId) {
            $this->_forward('noRoute');

            return;
        }

        $page = $this->pageFactory->create();

        $this->pageResourceFactory->create()->load(
            $page,
            $pageId
        );

        if (! $page->getId()) {
            $this->_forward('noRoute');

            return;
        }

        $this->registryHelper->register(
            'current_page',
            $page,
            true
        );

        $filterableAttributes = $this->filterableAttributeList->getList();

        $attributeFilterCodes = [];
        $attributeFilterValues = [];

        for ($i = 1; $i <= 5; $i++) {
            $attributeId = $page->getDataUsingMethod(
                sprintf(
                    'attribute_id%d',
                    $i
                )
            );

            if ($this->variables->isEmpty($attributeId)) {
                continue;
            }

            try {
                $attribute = $this->eavAttributeHelper->getAttribute(
                    Product::ENTITY,
                    $attributeId
                );

                $attributeCode = $attribute->getAttributeCode();

                $isFilterableAttribute = false;

                foreach ($filterableAttributes as $filterableAttribute) {
                    if ($filterableAttribute->getAttributeCode() === $attributeCode) {
                        $isFilterableAttribute = true;
                        break;
                    }
                }

                if (! $isFilterableAttribute) {
                    throw new Exception(
                        sprintf(
                            'Use of non filterable attribute: %s',
                            $attributeCode
                        )
                    );
                }
            } catch (Exception $exception) {
                $this->logging->error($exception);

                $this->_forward('noRoute');
                return;
            }

            $attributeValue = $page->getDataUsingMethod(
                sprintf(
                    'value%d',
                    $i
                )
            );

            $this->helper->addValue(
                $attributeCode,
                $attributeValue
            );

            $attributeFilterCodes[] = $attributeCode;
            $attributeFilterValues[] = is_array($attributeValue) ? implode(
                '_',
                $attributeValue
            ) : $attributeValue;
        }

        $attributeSetId = $page->getDataUsingMethod('attribute_set_id');

        if (! $this->variables->isEmpty($attributeSetId)) {
            $this->helper->addValue(
                'attribute_set_id',
                $attributeSetId
            );
        }

        $this->registryHelper->register(
            'attribute_filter',
            $attributeFilterCodes,
            true
        );

        $this->layerResolver->create('landing_page');

        $update = $this->_view->getLayout()->getUpdate();

        $update->addHandle('default');

        $this->_view->addActionLayoutHandles();
        $update->addHandle('infrangible_catalogattributelandingpage_page_view');
        $update->addHandle(
            sprintf(
                'infrangible_catalogattributelandingpage_page_view_%s',
                implode(
                    '_',
                    $attributeFilterCodes
                )
            )
        );
        $update->addHandle(
            sprintf(
                'infrangible_catalogattributelandingpage_page_view_%s_%s',
                implode(
                    '_',
                    $attributeFilterCodes
                ),
                implode(
                    '_',
                    $attributeFilterValues
                )
            )
        );
        $update->addHandle(
            sprintf(
                'infrangible_catalogattributelandingpage_page_view_%d',
                $page->getId()
            )
        );
        $this->_view->loadLayoutUpdates();

        $this->_view->generateLayoutXml()->generateLayoutBlocks();

        $pageConfig = $this->_view->getPage()->getConfig();

        $pageConfig->getTitle()->set(
            $this->variables->isEmpty($page->getPageTitle()) ? $page->getHeadline() : $page->getPageTitle()
        );

        if (! $this->variables->isEmpty($page->getMetaDescription())) {
            $pageConfig->setDescription($page->getMetaDescription());
        } elseif (! $this->variables->isEmpty($page->getDescription())) {
            $pageConfig->setDescription($page->getDescription());
        }

        if (! $this->variables->isEmpty($page->getMetaKeywords())) {
            $pageConfig->setKeywords($page->getMetaKeywords());
        }

        return $this->_view->getPage();
    }
}
