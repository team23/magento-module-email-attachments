<?php
declare(strict_types=1);

namespace Team23\EmailAttachmentsApi\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;
use Team23\EmailAttachmentsAdminUi\Model\Backend\Config;
use Team23\EmailAttachmentsApi\Api\GetAttachmentsInterface;

class GetAttachments implements GetAttachmentsInterface
{
    /**
     * GetAttachments constructor
     *
     * @param Filesystem $fileSystem
     * @param File $fileDriver
     * @param LoggerInterface $logger
     * @param Config $configReader
     */
    public function __construct(
        private readonly Filesystem $fileSystem,
        private readonly File $fileDriver,
        private readonly LoggerInterface $logger,
        private readonly Config $configReader
    ) {
    }

    /**
     * @inheritDoc
     */
    public function get(string $type, int $storeId): array
    {
        $files = match ($type) {
            'order' => $this->configReader->getOrderEmailAttachments($storeId),
            'invoice' => $this->configReader->getInvoiceEmailAttachments($storeId),
            'shipment' => $this->configReader->getShipmentEmailAttachments($storeId),
            'creditmemo' => $this->configReader->getCreditMemoEmailAttachments($storeId),
            default => throw new LocalizedException(__('Wrong type for transactional email.')),
        };
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
        $path = rtrim(
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
