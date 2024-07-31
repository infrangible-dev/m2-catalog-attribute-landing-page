<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer;

use Infrangible\Core\Helper\Stores;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\StateKeyInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class StateKey
    implements StateKeyInterface
{
    /** @var Stores */
    protected $storeHelper;

    /** @var Session */
    protected $customerSession;

    public function __construct(Stores $storeHelper, Session $customerSession)
    {
        $this->storeHelper = $storeHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param Category $category
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function toString($category): string
    {
        return 'STORE_' . $this->storeHelper->getStore()->getId() . '_ATTRIBUTE_PAGE_' . '_CUSTGROUP_' .
            $this->customerSession->getCustomerGroupId();
    }
}
