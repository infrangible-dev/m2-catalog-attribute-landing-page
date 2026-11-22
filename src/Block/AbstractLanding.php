<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Block;

use Infrangible\CatalogAttributeLandingPage\Model\AbstractLanding as LandingModel;
use Infrangible\Core\Helper\Registry;
use Magento\Cms\Block\Widget\Block;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Breadcrumbs;
use Psr\Log\LoggerInterface;

/**
 * Shows the current landing page or group: breadcrumbs, texts and CMS blocks.
 */
abstract class AbstractLanding extends Template
{
    /** @var array<int, string> rendered CMS blocks by ID */
    private array $cmsBlockHtml = [];

    /**
     * @param Template\Context $context
     * @param Registry $registryHelper
     * @param LoggerInterface $logging
     * @param array $data
     *
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        protected Registry $registryHelper,
        protected LoggerInterface $logging,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Returns the current landing page or group.
     *
     * @return LandingModel
     */
    abstract public function getLandingObject(): LandingModel;

    /**
     * Adds the breadcrumbs of the landing page or group.
     *
     * @return $this
     */
    protected function _prepareLayout(): static
    {
        parent::_prepareLayout();

        try {
            $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        } catch (LocalizedException $exception) {
            $this->logging->error($exception);

            return $this;
        }

        if ($breadcrumbsBlock instanceof Breadcrumbs) {
            $headline = $this->getLandingObject()->getHeadline();

            $breadcrumbsBlock->addCrumb(
                'home',
                ['label' => __('Home'), 'title' => __('Go to Home Page'), 'link' => $this->getBaseUrl()]
            );
            $breadcrumbsBlock->addCrumb('landing_page', ['label' => $headline, 'title' => $headline]);
        }

        return $this;
    }

    /**
     * Returns the headline as text without tags.
     *
     * @return string
     */
    public function getHeadline(): string
    {
        return strip_tags($this->getLandingObject()->getHeadline());
    }

    /**
     * Returns the description (HTML, as entered in the admin).
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getLandingObject()->getDescription();
    }

    /**
     * Returns the HTML of the CMS block of the landing page or group.
     *
     * @return string
     */
    public function getCmsBlockHtml(): string
    {
        return $this->renderCmsBlock($this->getLandingObject()->getCmsBlockId());
    }

    /**
     * Returns the HTML of the active CMS block with the ID, or an empty text without ID or if it cannot be rendered.
     *
     * @param int|null $blockId
     *
     * @return string
     */
    protected function renderCmsBlock(?int $blockId): string
    {
        if ($blockId === null) {
            return '';
        }

        if (! array_key_exists($blockId, $this->cmsBlockHtml)) {
            try {
                /** @var Block $cmsBlock */
                $cmsBlock = $this->getLayout()->createBlock(Block::class);

                $cmsBlock->setData('block_id', $blockId);
                $cmsBlock->setTemplate('Magento_Cms::widget/static_block/default.phtml');

                $html = $cmsBlock->toHtml();
                $text = $cmsBlock->getData('text');

                // the template also renders inactive blocks, as an empty element
                $this->cmsBlockHtml[ $blockId ] = is_string($text) && trim($text) !== '' ? $html : '';
            } catch (LocalizedException $exception) {
                $this->logging->error($exception);

                $this->cmsBlockHtml[ $blockId ] = '';
            }
        }

        return $this->cmsBlockHtml[ $blockId ];
    }
}
