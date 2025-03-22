<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model;

use Infrangible\Core\Helper\Stores;
use Infrangible\Core\Helper\Url;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @method int getGroupId()
 * @method void setGroupId(int $groupId)
 * @method int|null getAttributeId1()
 * @method void setAttributeId1(int|null $attributeId)
 * @method int|null getAttributeId2()
 * @method void setAttributeId2(int|null $attributeId)
 * @method int|null getAttributeId3()
 * @method void setAttributeId3(int|null $attributeId)
 * @method int|null getAttributeId4()
 * @method void setAttributeId4(int|null $attributeId)
 * @method int|null getAttributeId5()
 * @method void setAttributeId5(int|null $attributeId)
 * @method string getUrlKey()
 * @method void setUrlKey(string $urlKey)
 * @method string getHeadline()
 * @method void setHeadline(string $headline)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method int getCmsBlockId()
 * @method void setCmsBlockId(int $cmsBlockId)
 * @method string getPageTitle()
 * @method void setPageTitle(string $pageTitle)
 * @method string getMetaDescription()
 * @method void setMetaDescription(string $metaDescription)
 * @method string getMetaKeywords()
 * @method void setMetaKeywords(string $metaKeywords)
 * @method int getActive()
 * @method void setActive(int $active)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Group extends AbstractModel
{
    /** @var Url */
    protected $urlHelper;

    /** @var Stores */
    protected $storeHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        Url $urlHelper,
        Stores $storeHelper,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->urlHelper = $urlHelper;
        $this->storeHelper = $storeHelper;
    }

    public function _construct(): void
    {
        $this->_init(ResourceModel\Group::class);
    }

    /**
     * Converts field names for setters and getters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unnecessary preg_replace
     *
     * @param string $name
     *
     */
    protected function _underscore($name): string
    {
        return strtolower(
            trim(
                preg_replace(
                    '/(.)([A-Z])/',
                    '$1_$2',
                    $name
                ),
                '_'
            )
        );
    }

    public function getUrl(): string
    {
        return $this->urlHelper->getUrl('/') . $this->getUrlPath();
    }

    public function getUrlPath(): string
    {
        $urlPath = $this->getUrlKey();

        $categoryUrlSuffix = $this->storeHelper->getStoreConfig('catalog/seo/category_url_suffix');

        if (! empty($categoryUrlSuffix)) {
            $urlPath .= $categoryUrlSuffix;
        }

        return $urlPath;
    }
}
