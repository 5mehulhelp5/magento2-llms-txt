<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class StoreDataCollector
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly PageRepositoryInterface $pageRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function collect(int $storeId): array
    {
        $baseUrl = $this->getBaseUrl($storeId);

        return [
            'store_name' => $this->storeManager->getStore($storeId)->getName(),
            'store_url' => $baseUrl,
            'home_page_meta_title' => $this->getHomePageMetaTitle($storeId),
            'categories' => $this->collectCategories($storeId, $baseUrl),
            'products' => $this->collectProducts($storeId, $baseUrl),
            'cms_pages' => $this->collectCmsPages($storeId, $baseUrl),
        ];
    }

    private function collectCategories(int $storeId, string $baseUrl): array
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'url_key', 'description'])
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('level', 2) // Only top-level categories
            ->setStoreId($storeId)
            ->setOrder('position', 'ASC');
//            ->setPageSize(10);

        $categories = [];
        foreach ($collection as $category) {
            $categories[] = [
                'name' => (string) $category->getName(),
                'url' => $baseUrl . $category->getUrlKey() . '.html',
                'description' => strip_tags((string) $category->getDescription()),
                'full_content' => sprintf('[%s](%s): %s', (string) $category->getName(), $baseUrl . $category->getUrlKey() . '.html', strip_tags((string) $category->getDescription())),
            ];
        }

        return $categories;
    }

    private function collectProducts(int $storeId, string $baseUrl): array
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'url_key', 'short_description'])
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', ['in' => [2, 3, 4]])
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->setOrder('created_at', 'DESC')
            ->setPageSize(15); // 3-5 products per category, max 10 categories

        $products = [];
        foreach ($collection as $product) {
            $products[] = [
                'name' => (string) $product->getName(),
                'url' => $baseUrl . $product->getUrlKey() . '.html',
                'description' => strip_tags((string) $product->getShortDescription()),
            ];
        }

        return $products;
    }

    private function collectCmsPages(int $storeId, string $baseUrl): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', 1)
            ->addFilter('store_id', [$storeId, 0], 'in')
            ->create();

        try {
            $pages = $this->pageRepository->getList($searchCriteria)->getItems();
        } catch (NoSuchEntityException $e) {
            return [];
        }

        $cmsPages = [];

        foreach ($pages as $page) {
            $identifier = (string) $page->getIdentifier();

            if ($identifier === 'home') {
                continue;
            }

            $cmsPages[] = [
                'title' => (string) $page->getTitle(),
                'url' => $baseUrl . $identifier,
                'meta_description' => (string) $page->getMetaDescription(),
                'full_content' => sprintf('[%s](%s): %s', $page->getTitle(), $baseUrl . $identifier, $page->getMetaDescription()),
            ];
        }
        // Add Contact Us page
        $cmsPages[] = [
            'title' => 'Contact Us',
            'url' => $baseUrl . 'contact',
            'meta_description' => 'Get in touch with us through our Contact Us page.',
            'full_content' => sprintf('[%s](%s): %s', 'Contact Us', $baseUrl . 'contact', 'Get in touch with us through our Contact Us page.'),
        ];

        return $cmsPages;
    }

    private function getBaseUrl(int $storeId): string
    {
        try {
            return (string) $this->storeManager->getStore($storeId)->getBaseUrl();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    private function getHomePageMetaTitle(int $storeId): string
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('identifier', 'home')
            ->addFilter('is_active', 1)
            ->addFilter('store_id', [$storeId, 0], 'in')
            ->create();
        $pages = $this->pageRepository->getList($searchCriteria)->getItems();
        foreach ($pages as $page) {
            return (string) $page->getMetaTitle();
        }
        return '';
    }

    private function getCmsPageMetaTitle(string $identifier, int $storeId): string
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('identifier', $identifier)
            ->addFilter('is_active', 1)
            ->addFilter('store_id', [$storeId, 0], 'in')
            ->create();
        $pages = $this->pageRepository->getList($searchCriteria)->getItems();
        foreach ($pages as $page) {
            return (string) $page->getTitle();
        }
        return '';
    }
}
