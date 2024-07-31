<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Traits;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
trait Group
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
        return 'infrangible_catalogattributelandingpage_group';
    }

    protected function getObjectName(): string
    {
        return 'Group';
    }

    protected function getObjectField(): string
    {
        return 'group_id';
    }

    protected function getTitle(): string
    {
        return __('Attribute Landing Pages > Groups')->render();
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
        return __('Unable to find the group with id: %d!')->render();
    }
}
