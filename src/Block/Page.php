<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Block;

use Infrangible\CatalogAttributeLandingPage\Model\Page as LandingPage;
use Infrangible\CatalogAttributeLandingPage\Model\PageFactory;
use Infrangible\Core\Helper\Registry;
use Infrangible\Core\Helper\Stores;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Psr\Log\LoggerInterface;

/**
 * Shows parts of the current landing page: the products, texts, images and CMS blocks.
 */
class Page extends AbstractLanding
{
    /** @var LandingPage|null */
    private ?LandingPage $page = null;

    /**
     * @param Template\Context $context
     * @param Registry $registryHelper
     * @param LoggerInterface $logging
     * @param Stores $storeHelper
     * @param PageFactory $pageFactory
     * @param array $data
     *
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registryHelper,
        LoggerInterface $logging,
        protected Stores $storeHelper,
        protected PageFactory $pageFactory,
        array $data = []
    ) {
        parent::__construct($context, $registryHelper, $logging, $data);
    }

    /**
     * Returns the current landing page, or an empty one outside of a landing page.
     *
     * @return LandingPage
     */
    public function getLandingObject(): LandingPage
    {
        return $this->getPage();
    }

    /**
     * Returns the current landing page, or an empty one outside of a landing page.
     *
     * @return LandingPage
     */
    public function getPage(): LandingPage
    {
        if ($this->page === null) {
            $page = $this->registryHelper->registry('current_page');

            $this->page = $page instanceof LandingPage ? $page : $this->pageFactory->create();
        }

        return $this->page;
    }

    /**
     * Returns the HTML of the product list.
     *
     * @return string
     */
    public function getProductListHtml(): string
    {
        return $this->getChildHtml('landing_page.products.list');
    }

    /**
     * Returns the HTML of the additional CMS block.
     *
     * @return string
     */
    public function getAdditionalCmsBlockHtml(): string
    {
        return $this->renderCmsBlock($this->getPage()->getAdditionalCmsBlockId());
    }

    /**
     * Returns the HTML of the SEO CMS block.
     *
     * @return string
     */
    public function getSeoCmsBlockHtml(): string
    {
        return $this->renderCmsBlock($this->getPage()->getSeoCmsBlockId());
    }

    /**
     * Returns the position of the image: product_list, description or cms.
     *
     * @return string
     */
    public function getImagePosition(): string
    {
        $imagePosition = $this->storeHelper->getStoreConfig(
            'infrangible_catalogattributelandingpage/general/image_position'
        );

        return is_string($imagePosition) ? $imagePosition : 'product_list';
    }

    /**
     * Returns the URL of the image if it is shown at the position, else null.
     *
     * @param string $position
     *
     * @return string|null
     */
    public function getImageUrlAt(string $position): ?string
    {
        if ($this->getImagePosition() !== $position) {
            return null;
        }

        try {
            return $this->getPage()->getImageUrl();
        } catch (NoSuchEntityException $exception) {
            $this->logging->error($exception);

            return null;
        }
    }

    /**
     * Returns the URL of the logo, or null if the page has none.
     *
     * @return string|null
     */
    public function getLogoUrl(): ?string
    {
        try {
            return $this->getPage()->getLogoUrl();
        } catch (NoSuchEntityException $exception) {
            $this->logging->error($exception);

            return null;
        }
    }
}
