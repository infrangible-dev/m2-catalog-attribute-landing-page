<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Traits;

/**
 * Settings of the admin pages of the landing groups.
 */
trait Group
{
    /**
     * Returns the key of the module.
     *
     * @return string
     */
    protected function getModuleKey(): string
    {
        return 'Infrangible_CatalogAttributeLandingPage';
    }

    /**
     * Returns the key of the ACL resource.
     *
     * @return string
     */
    protected function getResourceKey(): string
    {
        return 'infrangible_catalogattributelandingpage';
    }

    /**
     * Returns the key of the menu item.
     *
     * @return string
     */
    protected function getMenuKey(): string
    {
        return 'infrangible_catalogattributelandingpage_group';
    }

    /**
     * Returns the name of the object.
     *
     * @return string
     */
    protected function getObjectName(): string
    {
        return 'Group';
    }

    /**
     * Returns the field of the object ID.
     *
     * @return string
     */
    protected function getObjectField(): string
    {
        return 'group_id';
    }

    /**
     * Returns the title of the pages.
     *
     * @return string
     */
    protected function getTitle(): string
    {
        return __('Attribute Landing Pages > Groups')->render();
    }

    /**
     * Objects can be added.
     *
     * @return bool
     */
    protected function allowAdd(): bool
    {
        return true;
    }

    /**
     * Objects can be edited.
     *
     * @return bool
     */
    protected function allowEdit(): bool
    {
        return true;
    }

    /**
     * Objects have no view page.
     *
     * @return bool
     */
    protected function allowView(): bool
    {
        return false;
    }

    /**
     * Objects can be deleted.
     *
     * @return bool
     */
    protected function allowDelete(): bool
    {
        return true;
    }

    /**
     * Returns the message if the object was not found.
     *
     * @return string
     */
    protected function getObjectNotFoundMessage(): string
    {
        return __('Unable to find the group with id: %d!')->render();
    }
}
