<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Traits;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
trait Page
{
    protected function getModuleKey(): string
    {
        return 'Infrangible_CatalogAttributeLandingPage';
    }

    protected function getResourceKey(): string
    {
        return 'infrangible_catalogattributelandingpage';
    }

    protected function getMenuKey(): string
    {
        return 'infrangible_catalogattributelandingpage_page';
    }

    protected function getObjectName(): string
    {
        return 'Page';
    }

    protected function getObjectField(): string
    {
        return 'page_id';
    }

    protected function getTitle(): string
    {
        return __('Attribute Landing Pages > Pages')->render();
    }

    protected function allowAdd(): bool
    {
        return true;
    }

    protected function allowEdit(): bool
    {
        return true;
    }

    protected function allowView(): bool
    {
        return false;
    }

    protected function allowDelete(): bool
    {
        return true;
    }

    protected function getObjectNotFoundMessage(): string
    {
        return __('Unable to find the page with id: %d!')->render();
    }
}
