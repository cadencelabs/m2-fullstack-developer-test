<?php

declare(strict_types=1);

namespace Cadence\Movie\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const MOVIE_CATEGORY_NAME = 'Movie';
    const MOVIE_ATTRIBUTE_SET_NAME = 'Movie';
    const MOVIE_PRODUCT_PRICE = 5.99;
    const MOVIE_PRODUCT_QTY = 100;

    const XML_PATH_TMDB_API_KEY = 'tmdb/api/key';
    const XML_PATH_TMDB_API_REQUEST_URI = 'tmdb/api/request_uri';
    const XML_PATH_TMDB_API_IMAGE_BASE_URI = 'tmdb/api/image_base_uri';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getTmdbKey(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TMDB_API_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTmdbRequestUri(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TMDB_API_REQUEST_URI,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTmdbImageBaseUri(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TMDB_API_IMAGE_BASE_URI,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
