<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Block;

use Infrangible\CatalogAttributeLandingPage\Model\PageFactory;
use Infrangible\Core\Helper\Registry;
use Infrangible\Core\Helper\Stores;
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
class Page
    extends Template
{
    /** @var Registry */
    protected $registryHelper;

    /** @var Stores */
    protected $storeHelper;

    /** @var LoggerInterface */
    protected $logging;

    /** @var PageFactory */
    protected $pageFactory;

    /** @var \Infrangible\CatalogAttributeLandingPage\Model\Page */
    private $page;

    public function __construct(
        Template\Context $context,
        Registry $registryHelper,
        Stores $storeHelper,
        LoggerInterface $logging,
        PageFactory $pageFactory,
        array $data = [])
    {
        parent::__construct($context, $data);

        $this->registryHelper = $registryHelper;
        $this->storeHelper = $storeHelper;
        $this->logging = $logging;
        $this->pageFactory = $pageFactory;
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareLayout(): Page
    {
        parent::_prepareLayout();

        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');

        if ($breadcrumbsBlock instanceof Breadcrumbs) {
            $page = $this->getPage();

            $breadcrumbsBlock->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl()]);

            $breadcrumbsBlock->addCrumb(
                'landing_page', ['label' => $page->getHeadline(), 'title' => $page->getHeadline()]);
        }

        return $this;
    }

    public function getPage(): \Infrangible\CatalogAttributeLandingPage\Model\Page
    {
        if ($this->page === null) {
            $this->page = $this->registryHelper->registry('current_page');

            if (!$this->page || !$this->page->getId()) {
                $this->page = $this->pageFactory->create();
            }
        }

        return $this->page;
    }

    public function getHeadline(): string
    {
        return $this->stripTags($this->getPage()->getHeadline());
    }

    public function getProductListHtml(): string
    {
        return $this->getChildHtml('landing_page.products.list');
    }

    public function getCmsBlockHtml(): string
    {
        if (!$this->getData('cms_block_html')) {
            try {
                $layout = $this->getLayout();

                /** @var Block $cmsBlock */
                $cmsBlock = $layout->createBlock(Block::class);

                $blockId = $this->getPage()->getCmsBlockId();

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

    public function getAdditionalCmsBlockHtml(): string
    {
        if (!$this->getData('additional_cms_block_html')) {
            try {
                $layout = $this->getLayout();

                /** @var Block $cmsBlock */
                $cmsBlock = $layout->createBlock(Block::class);

                $blockId = $this->getPage()->getData('additional_cms_block_id');

                if ($blockId) {
                    $cmsBlock->setData('block_id', $blockId);

                    $html = $cmsBlock->toHtml();
                } else {
                    $html = '';
                }

                $this->setData('additional_cms_block_html', $html);
            } catch (LocalizedException $exception) {
                $this->logging->error($exception);
            }
        }

        return $this->getData('additional_cms_block_html');
    }

    public function getSeoCmsBlockHtml(): string
    {
        if (!$this->getData('seo_cms_block_html')) {
            try {
                $layout = $this->getLayout();

                /** @var Block $cmsBlock */
                $cmsBlock = $layout->createBlock(Block::class);

                $blockId = $this->getPage()->getData('seo_cms_block_id');

                if ($blockId) {
                    $cmsBlock->setData('block_id', $blockId);

                    $html = $cmsBlock->toHtml();
                } else {
                    $html = '';
                }

                $this->setData('seo_cms_block_html', $html);
            } catch (LocalizedException $exception) {
                $this->logging->error($exception);
            }
        }

        return $this->getData('seo_cms_block_html');
    }

    public function getImagePosition(): ?string
    {
        return $this->storeHelper->getStoreConfig('infrangible_catalogattributelandingpage/general/image_position');
    }
}
