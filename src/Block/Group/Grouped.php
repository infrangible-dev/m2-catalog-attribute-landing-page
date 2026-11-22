<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Block\Group;

use Exception;
use FeWeDev\Base\Arrays;
use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Model\AbstractLanding;
use Infrangible\CatalogAttributeLandingPage\Model\Group;
use Infrangible\CatalogAttributeLandingPage\Model\GroupFactory;
use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Page\CollectionFactory;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Registry;
use Infrangible\Core\Helper\Stores;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Psr\Log\LoggerInterface;

/**
 * Shows the landing pages of the current group, grouped by the values of their attributes: one level per attribute,
 * with a headline per value.
 */
class Grouped extends Template
{
    /** @var int level of the grouping, the first attribute is level 1 */
    private int $level = 1;

    /** @var Group|null */
    private ?Group $group = null;

    /** @var array<int|string, mixed>|null the pages by the values of their attributes */
    private ?array $groupedPages = null;

    /**
     * @param Template\Context $context
     * @param Registry $registryHelper
     * @param Stores $storeHelper
     * @param Variables $variables
     * @param Arrays $arrays
     * @param Attribute $eavAttributeHelper
     * @param LoggerInterface $logging
     * @param GroupFactory $groupFactory
     * @param CollectionFactory $pageCollectionFactory
     * @param array $data
     *
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        protected Registry $registryHelper,
        protected Stores $storeHelper,
        protected Variables $variables,
        protected Arrays $arrays,
        protected Attribute $eavAttributeHelper,
        protected LoggerInterface $logging,
        protected GroupFactory $groupFactory,
        protected CollectionFactory $pageCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Returns the level of the grouping.
     *
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Sets the level of the grouping.
     *
     * @param int $level
     *
     * @return void
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * Returns the current landing group, or an empty one outside of a landing group.
     *
     * @return Group
     */
    public function getGroup(): Group
    {
        if ($this->group === null) {
            $group = $this->registryHelper->registry('current_group');

            $this->group = $group instanceof Group ? $group : $this->groupFactory->create();
        }

        return $this->group;
    }

    /**
     * Returns the active landing pages of the store view with the attributes of the group, by their values.
     *
     * On the last level the values are lists of pages, on the levels above arrays of the next level, sorted by the
     * values.
     *
     * @return array<int|string, mixed>
     */
    public function getGroupedPages(): array
    {
        if ($this->groupedPages === null) {
            $groupedPages = [];

            try {
                foreach ($this->loadPages() as $page) {
                    $groupKeys = $this->getGroupKeys($page);

                    if ($groupKeys !== null) {
                        $groupedPages = $this->arrays->addDeepValue($groupedPages, $groupKeys, $page);
                    }
                }
            } catch (Exception $exception) {
                $this->logging->error($exception);
            }

            $this->sortRecursive($groupedPages);

            $this->groupedPages = $groupedPages;
        }

        return $this->groupedPages;
    }

    /**
     * Loads the active landing pages of the store view that use exactly the attributes of the group.
     *
     * @return Page[]
     * @throws LocalizedException
     */
    protected function loadPages(): array
    {
        $group = $this->getGroup();
        $pageCollection = $this->pageCollectionFactory->create();

        for ($position = 1; $position <= AbstractLanding::ATTRIBUTE_COUNT; $position++) {
            $attributeId = $group->getAttributeId($position);

            $pageCollection->addFieldToFilter(
                sprintf('main_table.attribute_id%d', $position),
                $attributeId === null ? ['null' => true] : ['eq' => $attributeId]
            );
        }

        $pageCollection->addActiveFilter();
        $pageCollection->addStoreFilter($this->variables->intValue($this->storeHelper->getStore()->getId()));
        $pageCollection->addOrder('main_table.headline', Collection::SORT_ORDER_ASC);

        return array_values(
            array_filter($pageCollection->getItems(), static fn(mixed $page): bool => $page instanceof Page)
        );
    }

    /**
     * Returns the labels of the values of the page by attribute, or null if a value is missing.
     *
     * @param Page $page
     *
     * @return string[]|null
     * @throws Exception
     */
    protected function getGroupKeys(Page $page): ?array
    {
        $groupKeys = [];

        for ($position = 1; $position <= AbstractLanding::ATTRIBUTE_COUNT; $position++) {
            $attributeId = $page->getAttributeId($position);

            if ($attributeId === null) {
                break;
            }

            $value = $page->getValue($position);

            if ($value === null || $value === []) {
                return null;
            }

            $attribute = $this->eavAttributeHelper->getAttribute(Product::ENTITY, (string)$attributeId);

            if (! $attribute->usesSource()) {
                $groupKeys[] = is_array($value) ? implode(', ', $value) : $value;

                continue;
            }

            $labels = [];

            // pages of a collection have the option IDs as comma-separated text, loaded pages as a list
            foreach (is_array($value) ? $value : explode(',', $value) as $optionId) {
                $label = $attribute->getSource()->getOptionText($optionId);

                if (is_string($label)) {
                    $labels[] = $label;
                }
            }

            if (count($labels) === 0) {
                return null;
            }

            $groupKeys[] = implode(', ', $labels);
        }

        return $groupKeys;
    }

    /**
     * Sorts the grouped pages naturally by their values, on all levels.
     *
     * @param array $groupedPages
     *
     * @phpstan-param array<int|string, mixed> $groupedPages
     *
     * @return void
     */
    protected function sortRecursive(array &$groupedPages): void
    {
        foreach ($groupedPages as &$value) {
            if (is_array($value)) {
                $this->sortRecursive($value);
            }
        }

        unset($value);

        ksort($groupedPages, SORT_NATURAL | SORT_FLAG_CASE);
    }

    /**
     * Sets the grouped pages of a lower level.
     *
     * @param array $groupedPages
     *
     * @phpstan-param array<int|string, mixed> $groupedPages
     *
     * @return void
     */
    public function setGroupedPages(array $groupedPages): void
    {
        $this->groupedPages = $groupedPages;
    }

    /**
     * Checks whether the list contains pages (last level) instead of further groups.
     *
     * @param array $pages
     *
     * @phpstan-param array<int|string, mixed> $pages
     *
     * @return bool
     */
    public function isPages(array $pages): bool
    {
        return reset($pages) instanceof Page;
    }

    /**
     * Returns the HTML of the pages of the last level.
     *
     * @return string
     */
    public function getPagesHtml(): string
    {
        try {
            $pagesBlock = $this->getLayout()->getBlock('landing_page.group.grouped.pages');
        } catch (LocalizedException $exception) {
            $this->logging->error($exception);

            return '';
        }

        if (! $pagesBlock instanceof Grouped\Pages) {
            return '';
        }

        $pagesBlock->setPages(
            array_values(array_filter($this->getGroupedPages(), static fn(mixed $page): bool => $page instanceof Page))
        );

        return $pagesBlock->toHtml();
    }

    /**
     * Returns the HTML of the groups of the next level.
     *
     * @param array $groupedPages
     *
     * @phpstan-param array<int|string, mixed> $groupedPages
     *
     * @return string
     */
    public function getGroupedPagesHtml(array $groupedPages): string
    {
        try {
            $groupedBlock = $this->getLayout()->createBlock(self::class);
        } catch (LocalizedException $exception) {
            $this->logging->error($exception);

            return '';
        }

        if (! $groupedBlock instanceof self) {
            return '';
        }

        $groupedBlock->setTemplate($this->getTemplate());
        $groupedBlock->setGroupedPages($groupedPages);
        $groupedBlock->setLevel($this->getLevel() + 1);

        return $groupedBlock->toHtml();
    }
}
