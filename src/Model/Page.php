<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model;

use Infrangible\Core\Helper\Stores;
use Infrangible\Core\Helper\Url;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @method int getPageId()
 * @method void setPageId(int $pageId)
 * @method int|null getAttributeId1()
 * @method void setAttributeId1(int|null $attributeId)
 * @method string|array|null getValue1()
 * @method void setValue1(string|array|null $value)
 * @method int|null getAttributeId2()
 * @method void setAttributeId2(int|null $attributeId)
 * @method string|array|null getValue2()
 * @method void setValue2(string|array|null $value)
 * @method int|null getAttributeId3()
 * @method void setAttributeId3(int|null $attributeId)
 * @method string|array|null getValue3()
 * @method void setValue3(string|array|null $value)
 * @method int|null getAttributeId4()
 * @method void setAttributeId4(int|null $attributeId)
 * @method string|array|null getValue4()
 * @method void setValue4(string|array|null $value)
 * @method int|null getAttributeId5()
 * @method void setAttributeId5(int|null $attributeId)
 * @method string|array|null getValue5()
 * @method void setValue5(string|array|null $value)
 * @method string|array|null getAttributeOrders()
 * @method void setAttributeOrders(string|array|null $attributeOrders)
 * @method string getAttributeOrder()
 * @method void setAttributeOrder(string $attributeOrder)
 * @method string getAttributeOrderDirection()
 * @method void setAttributeOrderDirection(string $attributeOrderDirection)
 * @method string getUrlKey()
 * @method void setUrlKey(string $urlKey)
 * @method string getHeadline()
 * @method void setHeadline(string $headline)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method int getCmsBlockId()
 * @method void setCmsBlockId(int $cmsBlockId)
 * @method string getImage()
 * @method void setImage(string $image)
 * @method string getLogo()
 * @method void setLogo(string $logo)
 * @method string getThumbnail()
 * @method void setThumbnail(string $thumbnail)
 * @method string getPageTitle()
 * @method void setPageTitle(string $pageTitle)
 * @method string getMetaDescription()
 * @method void setMetaDescription(string $metaDescription)
 * @method string getMetaKeywords()
 * @method void setMetaKeywords(string $metaKeywords)
 * @method int getActive()
 * @method void setActive(int $active)
 * @method int getCheckActive()
 * @method void setCheckActive(int $checkActive)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Page
    extends AbstractModel
{
    /** @var Url */
    protected $urlHelper;

    /** @var Stores */
    protected $storeHelper;

    /** @var string */
    protected $mediaUrl;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Url $urlHelper
     * @param Stores $storeHelper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Url $urlHelper,
        Stores $storeHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [])
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->urlHelper = $urlHelper;
        $this->storeHelper = $storeHelper;

        try {
            $this->mediaUrl = $storeHelper->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        } catch (NoSuchEntityException $exception) {
            $this->_logger->error($exception);
        }
    }

    public function _construct(): void
    {
        $this->_init(ResourceModel\Page::class);
    }

    public function getUrl(): string
    {
        $categoryUrlSuffix = $this->storeHelper->getStoreConfig('catalog/seo/category_url_suffix');

        $url = $this->urlHelper->getUrl('/') . $this->getUrlKey();

        if (!empty($categoryUrlSuffix)) {
            $url .= $categoryUrlSuffix;
        }

        return $url;
    }

    public function getImageUrl(): string
    {
        return sprintf('%s/%s', $this->mediaUrl, $this->getImage());
    }

    public function getLogoUrl(): string
    {
        return sprintf('%s/%s', $this->mediaUrl, $this->getLogo());
    }

    public function getThumbnailUrl(): string
    {
        return sprintf('%s/%s', $this->mediaUrl, $this->getThumbnail());
    }

    /**
     * Converts field names for setters and getters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unnecessary preg_replace
     *
     * @param string $name
     */
    protected function _underscore($name): string
    {
        return strtolower(trim(preg_replace('/(.)([A-Z])/', '$1_$2', $name), '_'));
    }
}
