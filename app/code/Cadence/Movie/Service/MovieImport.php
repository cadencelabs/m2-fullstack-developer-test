<?php

declare(strict_types=1);

namespace Cadence\Movie\Service;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Catalog\Model\ProductFactory;
use Cadence\Movie\Model\Config;
use Magento\Catalog\Model\Product\Type;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Psr\Http\Message\ResponseInterface;
use Cadence\Movie\Service\ImageImport;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeCollectionFactory;


class MovieImport
{
    /**
     * API request endpoint
     */
    const API_REQUEST_ENDPOINT = 'movie/popular/';

    const API_REQUEST_ENDPOINT_MOVIE_INFO = 'movie/';

    const MOVIE_IMAGE_MAX_ITEMS = '5';

    /**
     * @var ResponseFactory
     */
    public $responseFactory;

    /**
     * @var ClientFactory
     */
    public $clientFactory;

    /**
     * @var ProductFactory
     */
    public $productFactory;

    /**
     * @var StoreManagerInterface
     */
    public $storeManagerInterface;

    /**
     * @var ProductRepositoryInterface
     */
    public $productRepositoryInterface;

    /**
     * @var ImageImport
     */
    public $imageImport;

    /**
     * @var Config
     */
    public $cadenceConfig;

    /**
     * @var CategoryCollectionFactory
     */
    public $categoryCollectionFactory;

    /**
     * GitApiService constructor
     *
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        ProductFactory $productFactory,
        Config $cadenceConfig,
        StoreManagerInterface $storeManagerInterface,
        ProductRepositoryInterface $productRepositoryInterface,
        ImageImport $imageImport,
        CategoryCollectionFactory $categoryCollectionFactory,
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->productFactory = $productFactory;
        $this->cadenceConfig = $cadenceConfig;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->imageImport = $imageImport;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    public function execute(): void
    {
        $response = $this->doRequest(
            static::API_REQUEST_ENDPOINT,
            [
                'query' => [
                    'api_key' => $this->cadenceConfig->getTmdbKey(), 'language' => 'en-US', 'page' => 1
                ]
            ]

        );

        $this->createMovieProducts($this->processResponse($response));
    }

    public function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ): Response {
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => $this->cadenceConfig->getTmdbRequestUri()
        ]]);

        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }

    public function processResponse(ResponseInterface $response): array
    {
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();
        return json_decode($responseContent, true);
    }

    public function createMovieProducts(array $data)
    {
        $productsData = $data['results'];

        $websiteId = $this->storeManagerInterface->getStore()->getWebsiteId();

        foreach ($productsData as $productData) {
            $product = $this->productFactory->create();

            $productData = [
                'type_id' => Type::TYPE_VIRTUAL,
                'attribute_set_id' => $this->getAttributeSetId(),
                'sku' => (string) $productData['id'],
                'website_ids' => [$websiteId],
                'name' => $productData['title'],
                'description' => $productData['overview'],
                'price' => Config::MOVIE_PRODUCT_PRICE,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => Config::MOVIE_PRODUCT_QTY,
                    'is_qty_decimal' => 0,
                    'is_in_stock' => 1
                ],
                'category_ids' => [$this->getCategoryId()],
                'visibility' => Visibility::VISIBILITY_BOTH,
                'status' => Status::STATUS_ENABLED,
            ];

            $product->setData($productData);

            $this->addMovieDetails($product);
            $this->addMovieCredits($product);
            $this->assignImages($product);

            $this->productRepositoryInterface->save($product);
        }
    }

    public function addMovieDetails(\Magento\Catalog\Model\Product $product)
    {
        $response = $this->doRequest(
            static::API_REQUEST_ENDPOINT_MOVIE_INFO . $product->getSku(),
            [
                'query' => [
                    'api_key' => $this->cadenceConfig->getTmdbKey(), 'language' => 'en-US'
                ]
            ]

        );

        $data = $this->processResponse($response);

        if (empty($data)) {
            return;
        }

        $genres = array_map(function($genre) { return $genre['name']; }, $data['genres']);
        $dateData = explode("-", $data['release_date']);
        $year = trim($dateData[0]);
        $product->setCustomAttribute('genre', implode(', ', $genres));
        $product->setCustomAttribute('year', $year);
        $product->setCustomAttribute('vote_average', $data['vote_average']);

    }

    public function addMovieCredits(\Magento\Catalog\Model\Product $product)
    {
        $response = $this->doRequest(
            static::API_REQUEST_ENDPOINT_MOVIE_INFO . $product->getSku() . '/credits',
            [
                'query' => [
                    'api_key' => $this->cadenceConfig->getTmdbKey(), 'language' => 'en-US'
                ]
            ]

        );

        $data = $this->processResponse($response);

        $cast = [];
        foreach ($data['cast'] as $castItem) {
            array_push($cast, $castItem['name']);
        }
        $crewProducers = [];
        $crewDirectors = [];
        foreach ($data['crew'] as $crewItem) {
            if (strpos('Producer', $crewItem['job']) !== false) {
                array_push($crewProducers, $crewItem['name']);
            }
            if (strpos('Director', $crewItem['job']) !== false) {
                array_push($crewDirectors, $crewItem['name']);
            }
        }

        $product->setCustomAttribute('actors', implode(', ', $cast));
        $product->setCustomAttribute('producer', implode(', ', $crewProducers));
        $product->setCustomAttribute('director', implode(', ', $crewDirectors));
    }

    public function assignImages(\Magento\Catalog\Model\Product $product)
    {
        $response = $this->doRequest(
            static::API_REQUEST_ENDPOINT_MOVIE_INFO . $product->getSku() . '/images',
            [
                'query' => [
                    'api_key' => $this->cadenceConfig->getTmdbKey()
                ]
            ]

        );

        $data = $this->processResponse($response);

        if (!empty($data['posters'])) {
            $i = 0;
            foreach ($data['posters'] as $poster) {
                if ($i++ >= self::MOVIE_IMAGE_MAX_ITEMS) {
                    break;
                }
                if ($poster['file_path'] !== '') {
                    $this->imageImport->execute($product, $poster['file_path']);
                }
            }
        }
    }

    public function getCategoryId()
    {
        $collection = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToFilter('name', Config::MOVIE_CATEGORY_NAME)
            ->setPageSize(1);

        if ($collection->getSize()) {
            return $collection->getFirstItem()->getId();
        }

        return null;
    }

    public function getAttributeSetId()
    {
        $collection = $this->attributeCollectionFactory
            ->create()
            ->addFieldToFilter('attribute_set_name', Config::MOVIE_ATTRIBUTE_SET_NAME)
            ->setPageSize(1);

        if ($collection->getSize()) {
            return $collection->getFirstItem()->getId();
        }

        return null;
    }
}
