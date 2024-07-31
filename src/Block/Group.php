<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Block;

use Infrangible\CatalogAttributeLandingPage\Model\GroupFactory;
use Infrangible\Core\Helper\Registry;
use Magento\Cms\Block\Block;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Breadcrumbs;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Group
    extends Template
{
    /** @var Registry */
    protected $registryHelper;

    /** @var LoggerInterface */
    protected $logging;

    /** @var GroupFactory */
    protected $groupFactory;

    /** @var \Infrangible\CatalogAttributeLandingPage\Model\Group */
    private $group;

    public function __construct(
        Template\Context $context,
        Registry $registryHelper,
        LoggerInterface $logging,
        GroupFactory $groupFactory,
        array $data = [])
    {
        parent::__construct($context, $data);

        $this->registryHelper = $registryHelper;
        $this->logging = $logging;
        $this->groupFactory = $groupFactory;
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareLayout(): Group
    {
        parent::_prepareLayout();

        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');

        if ($breadcrumbsBlock instanceof Breadcrumbs) {
            $group = $this->getGroup();

            $breadcrumbsBlock->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl()]);

            $breadcrumbsBlock->addCrumb(
                'landing_page_group', ['label' => $group->getHeadline(), 'title' => $group->getHeadline()]);
        }

        return $this;
    }

    public function getGroup(): \Infrangible\CatalogAttributeLandingPage\Model\Group
    {
        if ($this->group === null) {
            $this->group = $this->registryHelper->registry('current_group');

            if (!$this->group || !$this->group->getId()) {
                $this->group = $this->groupFactory->create();
            }
        }

        return $this->group;
    }

    public function getHeadline(): string
    {
        return $this->escapeHtml($this->getGroup()->getHeadline());
    }

    public function getDescription(): string
    {
        return $this->getGroup()->getDescription();
    }

    public function getCmsBlockHtml(): string
    {
        if (!$this->getData('cms_block_html')) {
            try {
                $layout = $this->getLayout();

                /** @var Block $cmsBlock */
                $cmsBlock = $layout->createBlock(Block::class);

                $blockId = $this->getGroup()->getCmsBlockId();

                if ($blockId) {
                    $cmsBlock->setData('block_id', $blockId);

                    $html = $cmsBlock->toHtml();
                } else {
                    $html = '';
                }

                $this->setData('cms_block_html', $html);
            } catch (LocalizedException $exception) {
                $this->logging->error($exception);
            }
        }

        return $this->getData('cms_block_html');
    }

    public function getGroupedPagesHtml(): string
    {
        return $this->getChildHtml('landing_page.group.grouped');
    }
}
