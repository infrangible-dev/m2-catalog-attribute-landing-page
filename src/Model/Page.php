<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;

/**
 * A landing page: the products with the attribute values of the page, with texts, images and CMS blocks.
 */
class Page extends AbstractLanding
{
    /**
     * Initializes the resource model.
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\Page::class);
    }

    /**
     * Returns the value of the attribute at the position: the option IDs of attributes with options, else a text.
     *
     * @param int $position
     *
     * @return string|string[]|null
     */
    public function getValue(int $position): string|array|null
    {
        $value = $this->getData(sprintf('value%d', $position));

        if (is_array($value)) {
            return array_map([$this->variables, 'stringValue'], array_values($value));
        }

        return $this->variables->isEmpty($value) ? null : $this->variables->stringValue($value);
    }

    /**
     * Returns the ID of the attribute set that the products need, or null for all attribute sets.
     *
     * @return int|null
     */
    public function getAttributeSetId(): ?int
    {
        return $this->getIntValue('attribute_set_id');
    }

    /**
     * Returns the codes of the attributes that the products of the page can be sorted by.
     *
     * @return string[]
     */
    public function getAttributeOrders(): array
    {
        $attributeOrders = $this->getData('attribute_orders');

        if (is_string($attributeOrders)) {
            $attributeOrders = explode(',', $attributeOrders);
        }

        return is_array($attributeOrders) ?
            array_values(array_filter(array_map([$this->variables, 'stringValue'], $attributeOrders))) : [];
    }

    /**
     * Returns the code of the attribute that the products are sorted by, or null for the default.
     *
     * @return string|null
     */
    public function getAttributeOrder(): ?string
    {
        return $this->getStringValue('attribute_order');
    }

    /**
     * Returns the direction of the sort order (asc or desc), or null for the default.
     *
     * @return string|null
     */
    public function getAttributeOrderDirection(): ?string
    {
        $direction = $this->getStringValue('attribute_order_direction');

        return in_array($direction, ['asc', 'desc'], true) ? $direction : null;
    }

    /**
     * Returns the ID of the additional CMS block, or null if none is selected.
     *
     * @return int|null
     */
    public function getAdditionalCmsBlockId(): ?int
    {
        return $this->getIntValue('additional_cms_block_id');
    }

    /**
     * Returns the ID of the SEO CMS block, or null if none is selected.
     *
     * @return int|null
     */
    public function getSeoCmsBlockId(): ?int
    {
        return $this->getIntValue('seo_cms_block_id');
    }

    /**
     * Returns the path of the image in the media directory, or null if the page has none.
     *
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->getStringValue('image');
    }

    /**
     * Returns the path of the logo in the media directory, or null if the page has none.
     *
     * @return string|null
     */
    public function getLogo(): ?string
    {
        return $this->getStringValue('logo');
    }

    /**
     * Returns the path of the thumbnail in the media directory, or null if the page has none.
     *
     * @return string|null
     */
    public function getThumbnail(): ?string
    {
        return $this->getStringValue('thumbnail');
    }

    /**
     * Returns the URL of the image, or null if the page has none.
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getImageUrl(): ?string
    {
        return $this->getMediaUrl($this->getImage());
    }

    /**
     * Returns the URL of the logo, or null if the page has none.
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getLogoUrl(): ?string
    {
        return $this->getMediaUrl($this->getLogo());
    }

    /**
     * Returns the URL of the thumbnail, or null if the page has none.
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getThumbnailUrl(): ?string
    {
        return $this->getMediaUrl($this->getThumbnail());
    }

    /**
     * Returns the URL of the file in the media directory of the current store, or null without a file.
     *
     * @param string|null $path
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    protected function getMediaUrl(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        return rtrim($this->storeHelper->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/') . '/' .
            ltrim($path, '/');
    }

    /**
     * Returns the text value of the field, or null if it is empty.
     *
     * @param string $key
     *
     * @return string|null
     */
    protected function getStringValue(string $key): ?string
    {
        $value = $this->getData($key);

        return $this->variables->isEmpty($value) ? null : $this->variables->stringValue($value);
    }
}
