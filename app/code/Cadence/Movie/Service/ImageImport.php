<?php

declare(strict_types=1);

namespace Cadence\Movie\Service;

use Cadence\Movie\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

class ImageImport
{
    const MOVIE_IMAGE_FILE_SIZE = 'w1280';
    const MOVIE_IMAGE_FILE_SIZE_THUMB = 'w500';

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var File
     */
    private $file;

    /**
     * @var Config
     */
    private $cadenceConfig;

    public function __construct(
        DirectoryList $directoryList,
        File $file,
        Config $cadenceConfig
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->cadenceConfig = $cadenceConfig;
    }

    public function execute(\Magento\Catalog\Model\Product $product, string $filePath)
    {
        $tmpDir = $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
        $this->file->checkAndCreateFolder($tmpDir);
        $imageUrl = $this->cadenceConfig->getTmdbImageBaseUri() . self::MOVIE_IMAGE_FILE_SIZE . $filePath;
        $newFileName = $tmpDir . baseName($imageUrl);
        $result = $this->file->read($imageUrl, $newFileName);
        if ($result) {
            $product->addImageToMediaGallery($newFileName, ['image', 'small_image', 'thumbnail'], false, false);
        }
        return $result;
    }
}
