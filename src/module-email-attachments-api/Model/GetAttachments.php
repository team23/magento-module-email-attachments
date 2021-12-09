<?php

namespace Team23\EmailAttachmentsApi\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;
use Team23\EmailAttachmentsAdminUi\Model\Backend\Config;
use Team23\EmailAttachmentsApi\Api\GetAttachmentsInterface;

/**
 * Class GetAttachments
 *
 * @api
 * @version 1.0.0
 * @package Team23\EmailAttachmentsApi\Model
 */
class GetAttachments implements GetAttachmentsInterface
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $configReader;

    /**
     * GetAttachments constructor
     *
     * @param Filesystem $fileSystem
     * @param File $fileDriver
     * @param LoggerInterface $logger
     * @param Config $configReader
     */
    public function __construct(
        Filesystem $fileSystem,
        File $fileDriver,
        LoggerInterface $logger,
        Config $configReader
    ) {
        $this->fileSystem   = $fileSystem;
        $this->fileDriver   = $fileDriver;
        $this->logger      = $logger;
        $this->configReader = $configReader;
    }

    /**
     * @inheritDoc
     */
    public function get(string $type, int $storeId)
    {
        switch ($type) {
            case 'order':
                $files = $this->configReader->getOrderEmailAttachments($storeId);
                break;
            case 'invoice':
                $files = $this->configReader->getInvoiceEmailAttachments($storeId);
                break;
            case 'shipment':
                $files = $this->configReader->getShipmentEmailAttachments($storeId);
                break;
            case 'creditmemo':
                $files = $this->configReader->getCreditMemoEmailAttachments($storeId);
                break;
            default:
                throw new LocalizedException(__('Wrong type for transactional email.'));
        }

        return $this->finalizeAttachments($files);
    }

    /**
     * Check if file exists
     *
     * @param array $attachments
     * @return array
     */
    private function finalizeAttachments(array $attachments): array
    {
        $result = [];
        $path   = rtrim(
            $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(),
            '/'
        );

        foreach ($attachments as $attachment) {
            $file = $path . '/' . ltrim($attachment, '/');

            try {
                if ($this->fileDriver->isExists($file)) {
                    $result[] = $file;
                }
            } catch (FileSystemException $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $result;
    }
}
