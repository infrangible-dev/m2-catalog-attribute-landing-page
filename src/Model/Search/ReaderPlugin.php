<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogAttributeLandingPage\Model\Search;

use Magento\Framework\Config\ReaderInterface;

/**
 * Adds the generated filters of the filterable attributes to the search request of the landing pages.
 */
class ReaderPlugin
{
    /** Name of the search request of the landing pages. */
    public const CONTAINER = 'landing_page_container';

    /**
     * @param RequestGenerator $requestGenerator
     */
    public function __construct(protected RequestGenerator $requestGenerator)
    {
    }

    /**
     * Merges the generated request of the landing pages into the search requests of search_request.xml.
     *
     * @param ReaderInterface $subject
     * @param array $result
     * @param string|null $scope
     *
     * @phpstan-param array<string, mixed> $result
     *
     * @return array<string, mixed>
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterRead(ReaderInterface $subject, array $result, ?string $scope = null): array
    {
        return array_merge_recursive(
            $result,
            [self::CONTAINER => $this->requestGenerator->generateRequest(self::CONTAINER)]
        );
    }
}
