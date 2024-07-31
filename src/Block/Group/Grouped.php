<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Block\Group;

use Exception;
use FeWeDev\Base\Arrays;
use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Block\Group\Grouped\Pages;
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
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Element\Template;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Grouped
    extends Template
{
    /** @var Registry */
    protected $registryHelper;

    /** @var Stores */
    protected $storeHelper;

    /** @var Variables */
    protected $variables;

    /** @var Arrays */
    protected $arrays;

    /** @var Attribute */
    protected $eavAttributeHelper;

    /** @var LoggerInterface */
    protected $logging;

    /** @var GroupFactory */
    protected $groupFactory;

    /** @var CollectionFactory */
    protected $pageCollectionFactory;

    /** @var int */
    private $level = 1;

    /** @var Group */
    private $group;

    /** @var array */
    private $groupedPages;

    public function __construct(
        Template\Context $context,
        Registry $registryHelper,
        Stores $storeHelper,
        Variables $variables,
        Arrays $arrays,
        Attribute $eavAttributeHelper,
        LoggerInterface $logging,
        GroupFactory $groupFactory,
        CollectionFactory $pageCollectionFactory,
        array $data = [])
    {
        parent::__construct($context, $data);

        $this->registryHelper = $registryHelper;
        $this->storeHelper = $storeHelper;
        $this->variables = $variables;
        $this->arrays = $arrays;
        $this->eavAttributeHelper = $eavAttributeHelper;
        $this->logging = $logging;
        $this->groupFactory = $groupFactory;
        $this->pageCollectionFactory = $pageCollectionFactory;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level)
    {
        $this->level = $level;
    }

    public function getGroup(): Group
    {
        if ($this->group === null) {
            $this->group = $this->registryHelper->registry('current_group');

            if (!$this->group || !$this->group->getId()) {
                $this->group = $this->groupFactory->create();
            }
        }

        return $this->group;
    }

    public function getGroupedPages(): array
    {
        if ($this->groupedPages === null) {
            $groupedPages = [];

            try {
                $pageCollection = $this->pageCollectionFactory->create();

                for ($i = 1; $i <= 5; $i++) {
                    $attributeId = $this->getGroup()->getDataUsingMethod(sprintf('attribute_id%d', $i));

                    $pageCollection->addFieldToFilter(
                        sprintf('attribute_id%d', $i),
                        $this->variables->isEmpty($attributeId) ? ['null' => true] : ['eq' => $attributeId]);

                    $pageCollection->addFieldToFilter(
                        sprintf('value%d', $i),
                        $this->variables->isEmpty($attributeId) ? ['null' => true] : ['notnull' => true]);
                }

                $pageCollection->addFieldToFilter('active', ['eq' => 1]);
                $pageCollection->addStoreFilter($this->storeHelper->getStore()->getId());
                $pageCollection->addOrder('main_table.headline', Collection::SORT_ORDER_ASC);
                $pageCollection->load();

                $items = $pageCollection->getItems();

                $pages = array_values($items);

                /** @var Page $page */
                foreach ($pages as $page) {
                    $groupKeys = [];

                    for ($i = 1; $i <= 5; $i++) {
                        $attributeId = $page->getDataUsingMethod(sprintf('attribute_id%d', $i));

                        if ($this->variables->isEmpty($attributeId)) {
                            break;
                        }

                        $attribute = $this->eavAttributeHelper->getAttribute(Product::ENTITY, $attributeId);

                        $value = $page->getDataUsingMethod(sprintf('value%d', $i));

                        if ($attribute->usesSource()) {
                            $value = $attribute->getSource()->getOptionText($value);
                        }

                        if (!is_string($value) && !is_int($value)) {
                            continue 2;
                        }

                        $groupKeys[] = $value;
                    }

                    $groupedPages = $this->arrays->addDeepValue($groupedPages, $groupKeys, $page);
                }
            } catch (NotFoundException|Exception $exception) {
                $this->logging->error($exception->getMessage());
            }

            $this->ksortRecursive($groupedPages);

            $this->groupedPages = $groupedPages;
        }

        return $this->groupedPages;
    }

    protected function ksortRecursive(array &$array): bool
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->ksortRecursive($value);
            }
        }

        return ksort($array, SORT_NATURAL | SORT_FLAG_CASE);
    }

    public function setGroupedPages(array $groupedPages)
    {
        $this->groupedPages = $groupedPages;
    }

    public function isPages(array $pages): bool
    {
        $page = reset($pages);

        return $page instanceof Page;
    }

    public function getPagesHtml(): string
    {
        $pagesBlock = $this->loadPagesBlock();

        if ($pagesBlock) {
            $pagesBlock->setPages($this->getGroupedPages());

            return $this->renderPagesHtml($pagesBlock);
        } else {
            return '';
        }
    }

    public function loadPagesBlock(): ?Pages
    {
        try {
            $pagesBlock = $this->getLayout()->getBlock('landing_page.group.grouped.pages');
        } catch (LocalizedException $exception) {
            return null;
        }

        if ($pagesBlock instanceof Pages) {
            return $pagesBlock;
        }

        return null;
    }

    public function renderPagesHtml(Pages $pagesBlock): string
    {
        return $pagesBlock->toHtml();
    }

    public function getGroupedPagesHtml(array $groupedPages): string
    {
        try {
            /** @var Grouped $groupedBlock */
            $groupedBlock = $this->getLayout()->createBlock(get_class($this));
        } catch (LocalizedException $exception) {
            $this->logging->error($exception);

            return '';
        }

        $groupedBlock->setTemplate($this->getTemplate());
        $groupedBlock->setGroupedPages($groupedPages);
        $groupedBlock->setLevel($this->getLevel() + 1);

        return $groupedBlock->toHtml();
    }
}
