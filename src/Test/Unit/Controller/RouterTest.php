<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Test\Unit\Controller;

use FeWeDev\Base\Variables;
use Infrangible\CatalogAttributeLandingPage\Controller\Router;
use Infrangible\CatalogAttributeLandingPage\Helper\Data;
use Infrangible\CatalogAttributeLandingPage\Model\Group;
use Infrangible\CatalogAttributeLandingPage\Model\Page;
use Infrangible\Core\Helper\Stores;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests the forwarding of the URLs of the landing pages and groups.
 */
#[AllowMockObjectsWithoutExpectations]
class RouterTest extends TestCase
{
    /**
     * Returns paths, URL suffixes and the URL key that is searched, or null if none is searched.
     *
     * @return array
     * @phpstan-return array<string, array{string, string|null, string|null}>
     */
    public static function pathProvider(): array
    {
        return [
            'with suffix'          => ['/red.html', '.html', 'red'],
            'without suffix'       => ['/red', null, 'red'],
            // before, the length of the suffix was cut off: "foo-bar" became "fo"
            'missing suffix'       => ['/foo-bar', '.html', null],
            'other suffix'         => ['/red.htm', '.html', null],
            'encoded'              => ['/gr%C3%BCn.html', '.html', 'grün'],
            'only the suffix'      => ['/.html', '.html', null],
            'empty path'           => ['/', null, null]
        ];
    }

    /**
     * Tests which URL key is searched for the path.
     *
     * @dataProvider pathProvider
     *
     * @param string $path
     * @param string|null $suffix
     * @param string|null $urlKey
     *
     * @return void
     */
    #[DataProvider('pathProvider')]
    public function testMatchUrlKey(string $path, ?string $suffix, ?string $urlKey): void
    {
        $helper = $this->createMock(Data::class);

        if ($urlKey === null) {
            $helper->expects($this->never())->method('loadPage');
        } else {
            $helper->expects($this->once())->method('loadPage')->with($urlKey, 1)->willReturn(null);
            $helper->expects($this->once())->method('loadGroup')->with($urlKey, 1)->willReturn(null);
        }

        $this->assertNull($this->createRouter($helper, $suffix)->match($this->createRequest($path)));
    }

    /**
     * Tests that the URL of a landing page is forwarded to the view of the page, without searching groups.
     *
     * @return void
     */
    public function testMatchPage(): void
    {
        $page = $this->createStub(Page::class);
        $page->method('getId')->willReturn('7');

        $helper = $this->createMock(Data::class);
        $helper->method('loadPage')->willReturn($page);
        $helper->expects($this->never())->method('loadGroup');

        $request = $this->createMock(Http::class);
        $request->method('getPathInfo')->willReturn('/red.html');
        $request->expects($this->once())->method('setModuleName')->with(Router::FRONT_NAME);
        $request->expects($this->once())->method('setControllerName')->with('page');
        $request->expects($this->once())->method('setActionName')->with('view');
        $request->expects($this->once())->method('setParam')->with('page_id', '7');

        $this->assertInstanceOf(Forward::class, $this->createRouter($helper, '.html')->match($request));
    }

    /**
     * Tests that the URL of a landing group is forwarded to the view of the group.
     *
     * @return void
     */
    public function testMatchGroup(): void
    {
        $group = $this->createStub(Group::class);
        $group->method('getId')->willReturn('3');

        $helper = $this->createStub(Data::class);
        $helper->method('loadPage')->willReturn(null);
        $helper->method('loadGroup')->willReturn($group);

        $request = $this->createMock(Http::class);
        $request->method('getPathInfo')->willReturn('/colors.html');
        $request->expects($this->once())->method('setControllerName')->with('group');
        $request->expects($this->once())->method('setParam')->with('group_id', '3');

        $this->assertInstanceOf(Forward::class, $this->createRouter($helper, '.html')->match($request));
    }

    /**
     * Tests that the forwarded request to "not found" of the view is not matched again.
     *
     * @return void
     */
    public function testMatchNoRoute(): void
    {
        $helper = $this->createMock(Data::class);
        $helper->expects($this->never())->method('loadPage');

        $request = $this->createStub(Http::class);
        $request->method('getModuleName')->willReturn(Router::FRONT_NAME);
        $request->method('getActionName')->willReturn('noRoute');
        $request->method('getPathInfo')->willReturn('/red.html');

        $this->assertNull($this->createRouter($helper, '.html')->match($request));
    }

    /**
     * Returns a request with the path.
     *
     * @param string $path
     *
     * @return Http
     */
    private function createRequest(string $path): Http
    {
        $request = $this->createStub(Http::class);
        $request->method('getPathInfo')->willReturn($path);

        return $request;
    }

    /**
     * Returns the router with the helper and the URL suffix of categories, in the store view 1.
     *
     * @param Data $helper
     * @param string|null $suffix
     *
     * @return Router
     */
    private function createRouter(Data $helper, ?string $suffix): Router
    {
        $store = $this->createStub(Store::class);
        $store->method('getId')->willReturn('1');

        $storeHelper = $this->createStub(Stores::class);
        $storeHelper->method('getStoreConfig')->willReturn($suffix);
        $storeHelper->method('getStore')->willReturn($store);

        $actionFactory = $this->createStub(ActionFactory::class);
        $actionFactory->method('create')->willReturn($this->createStub(Forward::class));

        return new Router(
            $storeHelper,
            $helper,
            $actionFactory,
            new Variables(),
            $this->createStub(LoggerInterface::class)
        );
    }
}
