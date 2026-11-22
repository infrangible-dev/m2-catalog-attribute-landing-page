<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Controller\Group;

use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Controller\AbstractView;
use Infrangible\CatalogAttributeLandingPage\Model\GroupFactory;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\GroupFactory as GroupResourceFactory;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Registry;
use Infrangible\Core\Helper\Stores;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

/**
 * Shows a landing group: the landing pages with the attributes of the group, grouped by their values.
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
     * @param GroupFactory $groupFactory
     * @param GroupResourceFactory $groupResourceFactory
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
        protected GroupFactory $groupFactory,
        protected GroupResourceFactory $groupResourceFactory
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
     * Shows the landing group of the request parameter group_id.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $group = $this->groupFactory->create();

        $this->groupResourceFactory->create()->load(
            $group,
            $this->variables->intValue($this->request->getParam('group_id'))
        );

        if (! $this->isAvailable($group)) {
            return $this->notFound();
        }

        $attributeCodes = $this->getAttributeCodes($group);

        if ($attributeCodes === null) {
            return $this->notFound();
        }

        $this->registryHelper->register('current_group', $group, true);

        return $this->createResultPage(
            $group,
            'infrangible_catalogattributelandingpage_group_view',
            [implode('_', $attributeCodes)]
        );
    }
}
