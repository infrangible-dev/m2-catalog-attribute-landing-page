<?php /** @noinspection PhpDeprecationInspection The constructor of Magento's models needs the registry. */

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model;

use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Stores;
use Infrangible\Core\Helper\Url;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * A landing page or group: a URL key with up to five product attributes, store views and SEO texts.
 */
abstract class AbstractLanding extends AbstractModel
{
    /** Number of attributes of a landing page or group. */
    public const ATTRIBUTE_COUNT = 5;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Url $urlHelper
     * @param Stores $storeHelper
     * @param Variables $variables
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     *
     * @phpstan-param AbstractDb<AbstractModel>|null $resourceCollection
     * @phpstan-param array<string, mixed> $data
     *
     * @throws LocalizedException
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected Url $urlHelper,
        protected Stores $storeHelper,
        protected Variables $variables,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Returns the URL of the landing page or group in the current store.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->urlHelper->getUrl('/') . $this->getUrlPath();
    }

    /**
     * Returns the path of the URL: the URL key with the URL suffix of categories.
     *
     * @return string
     */
    public function getUrlPath(): string
    {
        $suffix = $this->storeHelper->getStoreConfig('catalog/seo/category_url_suffix');

        return $this->getUrlKey() . (is_string($suffix) ? $suffix : '');
    }

    /**
     * Returns the URL key.
     *
     * @return string
     */
    public function getUrlKey(): string
    {
        return $this->variables->stringValueOrDefault($this->getData('url_key'));
    }

    /**
     * Returns the headline.
     *
     * @return string
     */
    public function getHeadline(): string
    {
        return $this->variables->stringValueOrDefault($this->getData('headline'));
    }

    /**
     * Returns the description (HTML).
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->variables->stringValueOrDefault($this->getData('description'));
    }

    /**
     * Returns the ID of the CMS block, or null if none is selected.
     *
     * @return int|null
     */
    public function getCmsBlockId(): ?int
    {
        return $this->getIntValue('cms_block_id');
    }

    /**
     * Returns the title of the page, or the headline if no title is set.
     *
     * @return string
     */
    public function getPageTitle(): string
    {
        $pageTitle = $this->variables->stringValueOrDefault($this->getData('page_title'));

        return $pageTitle === '' ? $this->getHeadline() : $pageTitle;
    }

    /**
     * Returns the meta description.
     *
     * @return string
     */
    public function getMetaDescription(): string
    {
        return $this->variables->stringValueOrDefault($this->getData('meta_description'));
    }

    /**
     * Returns the meta keywords.
     *
     * @return string
     */
    public function getMetaKeywords(): string
    {
        return $this->variables->stringValueOrDefault($this->getData('meta_keywords'));
    }

    /**
     * Returns the ID of the attribute at the position (1 to 5), or null if the position is not used.
     *
     * @param int $position
     *
     * @return int|null
     */
    public function getAttributeId(int $position): ?int
    {
        return $this->getIntValue(sprintf('attribute_id%d', $position));
    }

    /**
     * Returns the IDs of the store views.
     *
     * @return int[]
     */
    public function getStoreIds(): array
    {
        $storeIds = $this->getData('store_ids');

        return is_array($storeIds) ? array_map([$this->variables, 'intValue'], array_values($storeIds)) : [];
    }

    /**
     * Returns the ID in the field, or null if it is empty or not positive.
     *
     * @param string $key
     *
     * @return int|null
     */
    protected function getIntValue(string $key): ?int
    {
        $value = $this->getData($key);
        $intValue = $this->variables->isEmpty($value) ? 0 : $this->variables->intValue($value);

        return $intValue > 0 ? $intValue : null;
    }

    /**
     * Returns the value of the field, for fields that end with a number (attribute_id1) directly.
     *
     * The magic methods of Magento convert getAttributeId1() into attribute_id_1, so that the fields of the attributes
     * cannot be read with them (the forms of the admin read their values with this method).
     *
     * @param string $key
     * @param mixed $args
     *
     * @return mixed
     */
    public function getDataUsingMethod($key, $args = null): mixed
    {
        return $this->isNumberedField($key) ? $this->getData($key) : parent::getDataUsingMethod($key, $args);
    }

    /**
     * Sets the value of the field, for fields that end with a number (attribute_id1) directly.
     *
     * @param string $key
     * @param mixed $args
     *
     * @return $this
     */
    public function setDataUsingMethod($key, $args = []): static
    {
        if ($this->isNumberedField($key)) {
            $this->setData($key, $args);
        } else {
            parent::setDataUsingMethod($key, $args);
        }

        return $this;
    }

    /**
     * Checks whether the name of the field ends with a number.
     *
     * @param mixed $key
     *
     * @return bool
     */
    protected function isNumberedField(mixed $key): bool
    {
        return is_string($key) && preg_match('/^[a-z_]+\\d$/', $key) === 1;
    }
}
