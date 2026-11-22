<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\Layer;

use Infrangible\Core\Helper\Stores;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\StateKeyInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Key of the state of the layer of the landing pages: by store view and customer group.
 */
class StateKey implements StateKeyInterface
{
    /**
     * @param Stores $storeHelper
     * @param Session $customerSession
     */
    public function __construct(protected Stores $storeHelper, protected Session $customerSession)
    {
    }

    /**
     * Returns the key of the state.
     *
     * @param Category $category
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function toString($category): string
    {
        return sprintf(
            'STORE_%s_ATTRIBUTE_PAGE_CUSTGROUP_%s',
            $this->storeHelper->getStore()->getId(),
            $this->customerSession->getCustomerGroupId()
        );
    }
}
