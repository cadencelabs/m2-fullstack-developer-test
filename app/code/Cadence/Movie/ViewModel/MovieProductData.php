<?php

declare(strict_types=1);

namespace Cadence\Movie\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Helper\Data as ProductHelper;

class MovieProductData implements ArgumentInterface
{
    private $currentProduct;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var ProductHelper
     */
    private $productHelper;

    public function __construct(
        ProductRepository $productRepository,
        ProductHelper $productHelper
    ) {
        $this->productRepository = $productRepository;
        $this->productHelper = $productHelper;
    }

    public function getMovieYear()
    {
        $currentProduct = $this->getCurrentProduct();
        return $currentProduct->getYear();
    }

    public function getMovieGanre()
    {
        $currentProduct = $this->getCurrentProduct();
        return $currentProduct->getGenre();
    }

    public function getMovieRating()
    {
        $currentProduct = $this->getCurrentProduct();
        return $currentProduct->getVoteAverage();
    }

    public function getMovieFullRating()
    {
        $currentProduct = $this->getCurrentProduct();
        return (int)$currentProduct->getVoteAverage();
    }

    public function getMoviePartRating()
    {
        $ratingFull = (float)$this->getMovieRating();
        $ratingFullPart = $this->getMovieFullRating();
        return ($ratingFull - $ratingFullPart) * 100;
    }

    public function getActors()
    {
        $currentProduct = $this->getCurrentProduct();
        return $currentProduct->getActors();
    }

    public function getDescription()
    {
        $currentProduct = $this->getCurrentProduct();
        return $currentProduct->getDescription();
    }

    public function getDirector()
    {
        $currentProduct = $this->getCurrentProduct();
        return $currentProduct->getDirector();
    }

    public function getProducer()
    {
        $currentProduct = $this->getCurrentProduct();
        return $currentProduct->getProducer();
    }

    public function getMovieCast()
    {
        $currentProduct = $this->getCurrentProduct();
        return $currentProduct->getActors();
    }

    public function getCurrentProduct()
    {
        if (!$this->currentProduct) {
            $this->currentProduct = $this->productHelper->getProduct();
            if ($this->currentProduct === null) {
                throw new NoSuchEntityException();
            }
        }

        return $this->currentProduct;
    }
}
