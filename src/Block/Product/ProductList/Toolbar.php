<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Block\Product\ProductList;

use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Toolbar
    extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    /** @var Registry */
    protected $registryHelper;

    /**
     * @param Context $context
     * @param Session $catalogSession
     * @param Config $catalogConfig
     * @param \Magento\Catalog\Model\Product\ProductList\Toolbar $toolbarModel
     * @param EncoderInterface $urlEncoder
     * @param ProductList $productListHelper
     * @param PostHelper $postDataHelper
     * @param Registry $registryHelper
     * @param array $data
     * @param ToolbarMemorizer|null $toolbarMemorizer
     * @param \Magento\Framework\App\Http\Context|null $httpContext
     * @param FormKey|null $formKey
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        Config $catalogConfig,
        \Magento\Catalog\Model\Product\ProductList\Toolbar $toolbarModel,
        EncoderInterface $urlEncoder,
        ProductList $productListHelper,
        PostHelper $postDataHelper,
        Registry $registryHelper,
        array $data = [],
        ToolbarMemorizer $toolbarMemorizer = null,
        \Magento\Framework\App\Http\Context $httpContext = null,
        FormKey $formKey = null)
    {
        parent::__construct(
            $context, $catalogSession, $catalogConfig, $toolbarModel, $urlEncoder, $productListHelper, $postDataHelper,
            $data, $toolbarMemorizer, $httpContext, $formKey);

        $this->registryHelper = $registryHelper;
    }

    /**
     * Get order field
     *
     * @return null|string
     */
    protected function getOrderField(): ?string
    {
        if ($this->_orderField === null) {
            /** @var Page $page */
            $page = $this->registryHelper->registry('current_page');

            $this->_orderField = $page->getAttributeOrder();
        }

        return $this->_orderField;
    }
}
