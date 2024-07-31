<?php

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\Search;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Framework\Config\ReaderInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ReaderPlugin
{
    /** @var RequestGenerator */
    protected $requestGenerator;

    public function __construct(RequestGenerator $requestGenerator)
    {
        $this->requestGenerator = $requestGenerator;
    }

    /**
     * Merge reader's value with generated
     *
     * @param ReaderInterface $subject
     * @param array           $result
     * @param string|null     $scope
     *
     * @return array
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterRead(
        ReaderInterface $subject,
        array $result,
        string $scope = null): array
    {
        $container = [];

        $container[ 'landing_page_container' ] =
            $this->requestGenerator->generateRequest(EavAttributeInterface::IS_FILTERABLE, 'landing_page_container',
                false);

        return array_merge_recursive($result, $container);
    }
}
