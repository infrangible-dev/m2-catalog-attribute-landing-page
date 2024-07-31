<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogAttributeLandingPage\Model\Search;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class RequestGenerator
    extends \Magento\CatalogSearch\Model\Search\RequestGenerator
{
    /** @var CollectionFactory */
    private $productAttributeCollectionFactory;

    /** @var GeneratorResolver */
    private $generatorResolver;

    /**
     * @param CollectionFactory $productAttributeCollectionFactory
     * @param GeneratorResolver|null $generatorResolver
     */
    public function __construct(
        CollectionFactory $productAttributeCollectionFactory,
        GeneratorResolver $generatorResolver = null)
    {
        parent::__construct($productAttributeCollectionFactory, $generatorResolver);

        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->generatorResolver = $generatorResolver ?: ObjectManager::getInstance()->get(GeneratorResolver::class);
    }

    public function generateRequest(string $attributeType, string $container, bool $useFulltext): array
    {
        $request = [];

        foreach ($this->getSearchableAttributes() as $attribute) {
            if ($attribute->getData($attributeType)) {
                if (!in_array($attribute->getAttributeCode(), ['price', 'category_ids'], true)) {
                    $queryName = $attribute->getAttributeCode() . '_query';
                    $request['queries'][$container]['queryReference'][] = [
                        'clause' => 'must',
                        'ref' => $queryName,];

                    $filterName = $attribute->getAttributeCode() . self::FILTER_SUFFIX;
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => QueryInterface::TYPE_FILTER,
                        'filterReference' => [
                            [
                                'clause' => 'must',
                                'ref' => $filterName,]],];

                    $bucketName = $attribute->getAttributeCode() . self::BUCKET_SUFFIX;

                    $generator = $this->generatorResolver->getGeneratorForType($attribute->getBackendType());

                    $request['filters'][$filterName] = $generator->getFilterData($attribute, $filterName);
                    $request['aggregations'][$bucketName] = $generator->getAggregationData($attribute, $bucketName);
                }
            }

            /** @var Attribute $attribute */
            if (!$attribute->getIsSearchable() || in_array($attribute->getAttributeCode(), ['price', 'sku'], true)) {
                // Some fields have their own specific handlers
                continue;
            }

            $request = $this->processPriceAttribute($useFulltext, $attribute, $request);
        }

        return $this->addAttributeSet($request, $container);
    }

    /**
     * Modify request for price attribute.
     *
     * @param bool $useFulltext
     * @param Attribute $attribute
     * @param array $request
     *
     * @return array
     */
    private function processPriceAttribute(
        bool $useFulltext,
        Attribute $attribute,
        array $request): array
    {
        // Match search by custom price attribute isn't supported
        if ($useFulltext && $attribute->getFrontendInput() !== 'price') {
            $request['queries']['search']['match'][] = [
                'field' => $attribute->getAttributeCode(),
                'boost' => $attribute->getSearchWeight() ?: 1,];
        }

        return $request;
    }

    private function addAttributeSet(array $request, string $container): array
    {
        $queryName = 'attribute_set_query';

        $request['queries'][$container]['queryReference'][] = [
            'clause' => 'must',
            'ref' => $queryName];

        $filterName = 'attribute_set' . self::FILTER_SUFFIX;

        $request['queries'][$queryName] = [
            'name' => $queryName,
            'type' => QueryInterface::TYPE_FILTER,
            'filterReference' => [
                [
                    'clause' => 'must',
                    'ref' => $filterName,]]];

        $request['filters'][$filterName] = [
            'type' => FilterInterface::TYPE_TERM,
            'name' => $filterName,
            'field' => 'attribute_set_id',
            'value' => '$attribute_set_id$'];

        return $request;
    }

    /**
     * Retrieve searchable attributes
     *
     * @return Collection
     */
    protected function getSearchableAttributes(): Collection
    {
        $productAttributes = $this->productAttributeCollectionFactory->create();

        $productAttributes->addFieldToFilter([
            'is_searchable',
            'is_visible_in_advanced_search',
            'is_filterable',
            'is_filterable_in_search'], [1, 1, [1, 2, 3], 1]);

        return $productAttributes;
    }
}
