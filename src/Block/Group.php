<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Block;

use Infrangible\CatalogAttributeLandingPage\Model\Group as LandingGroup;
use Infrangible\CatalogAttributeLandingPage\Model\GroupFactory;
use Infrangible\Core\Helper\Registry;
use Magento\Framework\View\Element\Template;
use Psr\Log\LoggerInterface;

/**
 * Shows the current landing group: its description, CMS block and the grouped landing pages.
 */
class Group extends AbstractLanding
{
    /** @var LandingGroup|null */
    private ?LandingGroup $group = null;

    /**
     * @param Template\Context $context
     * @param Registry $registryHelper
     * @param LoggerInterface $logging
     * @param GroupFactory $groupFactory
     * @param array $data
     *
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registryHelper,
        LoggerInterface $logging,
        protected GroupFactory $groupFactory,
        array $data = []
    ) {
        parent::__construct($context, $registryHelper, $logging, $data);
    }

    /**
     * Returns the current landing group, or an empty one outside of a landing group.
     *
     * @return LandingGroup
     */
    public function getLandingObject(): LandingGroup
    {
        if ($this->group === null) {
            $group = $this->registryHelper->registry('current_group');

            $this->group = $group instanceof LandingGroup ? $group : $this->groupFactory->create();
        }

        return $this->group;
    }

    /**
     * Returns the HTML of the grouped landing pages.
     *
     * @return string
     */
    public function getGroupedPagesHtml(): string
    {
        return $this->getChildHtml('landing_page.group.grouped');
    }
}
