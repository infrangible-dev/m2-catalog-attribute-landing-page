<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Test\Unit\Controller;

use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Controller\Page\View;
use Infrangible\CatalogAttributeLandingPage\Helper\Data;
use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Infrangible\CatalogAttributeLandingPage\Model\PageFactory as LandingPageFactory;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\Page as PageResource;
use Infrangible\CatalogAttributeLandingPage\Model\ResourceModel\PageFactory as LandingPageResourceFactory;
use Infrangible\CatalogAttributeLandingPage\Test\Unit\CreatesPages;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Registry;
use Infrangible\Core\Helper\Stores;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests the view of a landing page.
 */
#[AllowMockObjectsWithoutExpectations]
class PageViewTest extends TestCase
{
    use CreatesPages;

    /**
     * Tests that inactive pages and pages of other store views are not found, also with a direct link to the view.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testExecuteNotAvailable(): void
    {
        $data = ['page_id' => '4', 'attribute_id1' => '93', 'value1' => ['49'], 'store_ids' => [0]];

        $this->assertNotFound($this->createView($this->createPage($data + ['active' => '0'])));
        $this->assertNotFound($this->createView($this->createPage(['active' => '1', 'store_ids' => [2]] + $data)));
        $this->assertNotFound($this->createView($this->createPage(['active' => '1', 'store_ids' => []] + $data)));
    }

    /**
     * Tests that a page with an attribute that is not filterable is not found.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testExecuteNotFilterable(): void
    {
        $page = $this->createPage(
            ['page_id' => '4', 'active' => '1', 'attribute_id1' => '93', 'value1' => ['49'], 'store_ids' => [0]]
        );

        $this->assertNotFound($this->createView($page, $this->createAttribute(false)));
    }

    /**
     * Tests the view: the values as request parameters of the layered navigation, the layer, the layout handles and
     * the title of the page.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testExecute(): void
    {
        $page = $this->createPage(
            [
                'page_id'          => '5',
                'active'           => '1',
                'store_ids'        => [1],
                'attribute_id1'    => '93',
                'value1'           => 'Light Red!',
                'attribute_set_id' => '9',
                'headline'         => 'Red'
            ]
        );

        $helper = $this->createMock(Data::class);
        $helper->expects($this->exactly(2))->method('addValue')->willReturnCallback(
            function (string $code, mixed $value): void {
                $this->assertContains([$code, $value], [['color', 'Light Red!'], ['attribute_set_id', 9]]);
            }
        );

        $layerResolver = $this->createMock(Resolver::class);
        $layerResolver->expects($this->once())->method('create')->with('landing_page');

        $handles = [];

        $title = $this->createMock(Title::class);
        $title->expects($this->once())->method('set')->with('Red');

        $pageConfig = $this->createStub(Config::class);
        $pageConfig->method('getTitle')->willReturn($title);

        $resultPage = $this->createStub(ResultPage::class);
        $resultPage->method('getConfig')->willReturn($pageConfig);
        $resultPage->method('addHandle')->willReturnCallback(
            function (string $handle) use (&$handles, $resultPage): ResultPage {
                $handles[] = $handle;

                return $resultPage;
            }
        );

        $result = $this->createView($page, $this->createAttribute(true), $helper, $layerResolver, $resultPage)
            ->execute();

        $this->assertSame($resultPage, $result);
        $this->assertSame(
            [
                'infrangible_catalogattributelandingpage_page_view',
                'infrangible_catalogattributelandingpage_page_view_color',
                // the value is reduced to the characters of layout handles
                'infrangible_catalogattributelandingpage_page_view_color_light_red',
                'infrangible_catalogattributelandingpage_page_view_5'
            ],
            $handles
        );
    }

    /**
     * Asserts that the view forwards to "not found".
     *
     * @param View $view
     *
     * @return void
     */
    private function assertNotFound(View $view): void
    {
        $this->assertInstanceOf(Forward::class, $view->execute());
    }

    /**
     * Returns the attribute "color".
     *
     * @param bool $filterable
     *
     * @return EavAttribute
     */
    private function createAttribute(bool $filterable): EavAttribute
    {
        $attribute = $this->createStub(EavAttribute::class);
        $attribute->method('getAttributeCode')->willReturn('color');
        $attribute->method('getData')->willReturnMap([['is_filterable', null, $filterable ? '1' : '0']]);

        return $attribute;
    }

    /**
     * Returns the view of the page in the store view 1.
     *
     * @param Page $page
     * @param EavAttribute|null $attribute
     * @param Data|null $helper
     * @param Resolver|null $layerResolver
     * @param ResultPage|null $resultPage
     *
     * @return View
     */
    private function createView(
        Page $page,
        ?EavAttribute $attribute = null,
        ?Data $helper = null,
        ?Resolver $layerResolver = null,
        ?ResultPage $resultPage = null
    ): View {
        $request = $this->createStub(RequestInterface::class);
        $request->method('getParam')->willReturn($page->getId());

        $resultPageFactory = $this->createStub(PageFactory::class);
        $resultPageFactory->method('create')->willReturn($resultPage ?? $this->createStub(ResultPage::class));

        $forward = $this->createStub(Forward::class);
        $forward->method('forward')->willReturn($forward);

        $resultForwardFactory = $this->createStub(ForwardFactory::class);
        $resultForwardFactory->method('create')->willReturn($forward);

        $eavAttributeHelper = $this->createStub(Attribute::class);
        $eavAttributeHelper->method('getAttribute')->willReturn($attribute ?? $this->createAttribute(true));

        $store = $this->createStub(Store::class);
        $store->method('getId')->willReturn('1');

        $storeHelper = $this->createStub(Stores::class);
        $storeHelper->method('getStore')->willReturn($store);

        $pageFactory = $this->createStub(LandingPageFactory::class);
        $pageFactory->method('create')->willReturn($page);

        // the page has its data already
        $pageResourceFactory = $this->createStub(LandingPageResourceFactory::class);
        $pageResourceFactory->method('create')->willReturn($this->createStub(PageResource::class));

        return new View(
            $request,
            $resultPageFactory,
            $resultForwardFactory,
            $this->createStub(Registry::class),
            $eavAttributeHelper,
            $storeHelper,
            new Variables(),
            $this->createStub(LoggerInterface::class),
            $helper ?? $this->createStub(Data::class),
            $pageFactory,
            $pageResourceFactory,
            $layerResolver ?? $this->createStub(Resolver::class)
        );
    }
}
