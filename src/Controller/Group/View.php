<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Controller\Group;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Model\GroupFactory;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Registry;
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
class View
    extends Action
{
    /** @var Registry */
    protected $registryHelper;

    /** @var Variables */
    protected $variableHelper;

    /** @var Attribute */
    protected $eavAttributeHelper;

    /** @var LoggerInterface */
    protected $logging;

    /** @var GroupFactory */
    protected $groupFactory;

    /** @var \Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\GroupFactory */
    protected $groupResourceFactory;

    public function __construct(
        Context $context,
        Registry $registryHelper,
        Variables $variableHelper,
        Attribute $eavAttributeHelper,
        LoggerInterface $logging,
        GroupFactory $groupFactory,
        \Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\GroupFactory $groupResourceFactory)
    {
        parent::__construct($context);

        $this->registryHelper = $registryHelper;
        $this->variableHelper = $variableHelper;
        $this->eavAttributeHelper = $eavAttributeHelper;
        $this->logging = $logging;
        $this->groupFactory = $groupFactory;
        $this->groupResourceFactory = $groupResourceFactory;
    }

    /**
     * @return ResultInterface|void
     */
    public function execute()
    {
        $groupId = $this->getRequest()->getParam('group_id');

        if (!$groupId) {
            $this->_forward('noRoute');
            return;
        }

        $group = $this->groupFactory->create();

        $this->groupResourceFactory->create()->load($group, $groupId);

        if (!$group->getId()) {
            $this->_forward('noRoute');
            return;
        }

        $this->registryHelper->register('current_group', $group, true);

        $attributeFilterCodes = [];

        for ($i = 1; $i <= 5; $i++) {
            $attributeId = $group->getDataUsingMethod(sprintf('attribute_id%d', $i));

            if ($this->variableHelper->isEmpty($attributeId)) {
                continue;
            }

            try {
                $attribute = $this->eavAttributeHelper->getAttribute(Product::ENTITY, $attributeId);

                $attributeCode = $attribute->getAttributeCode();
            } catch (Exception $exception) {
                $this->logging->error($exception);

                $this->_forward('noRoute');
                return;
            }

            $attributeFilterCodes[] = $attributeCode;
        }

        $update = $this->_view->getLayout()->getUpdate();

        $update->addHandle('default');

        $this->_view->addActionLayoutHandles();
        $update->addHandle('infrangible_catalogattributelandingpage_group_view');
        $update->addHandle(
            sprintf(
                'infrangible_catalogattributelandingpage_group_view_%s', implode('_', $attributeFilterCodes)));
        $update->addHandle(sprintf('infrangible_catalogattributelandingpage_group_view_%d', $group->getId()));
        $this->_view->loadLayoutUpdates();

        $this->_view->generateLayoutXml()->generateLayoutBlocks();

        $pageConfig = $this->_view->getPage()->getConfig();

        $pageConfig->getTitle()->set(
            $this->variableHelper->isEmpty($group->getPageTitle()) ? $group->getHeadline() : $group->getPageTitle());

        if (!$this->variableHelper->isEmpty($group->getMetaDescription())) {
            $pageConfig->setDescription($group->getMetaDescription());
        } else if (!$this->variableHelper->isEmpty($group->getDescription())) {
            $pageConfig->setDescription($group->getDescription());
        }

        if (!$this->variableHelper->isEmpty($group->getMetaKeywords())) {
            $pageConfig->setKeywords($group->getMetaKeywords());
        }

        return $this->_view->getPage();
    }
}
