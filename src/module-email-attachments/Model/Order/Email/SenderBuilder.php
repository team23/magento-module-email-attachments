<?php
declare(strict_types=1);

namespace Team23\EmailAttachments\Model\Order\Email;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\File as FileInfo;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use Team23\EmailAttachments\Model\Template\TransportBuilder;
use Team23\EmailAttachmentsApi\Api\GetAttachmentsInterface;
use Laminas\Mime\Mime;

/**
 * @SuppressWarnings(PHPMD.LongVariableName)
 */
class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    /**
     * SenderBuilder constructor
     *
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param File $fileDriver
     * @param FileInfo $fileInfo
     * @param GetAttachmentsInterface $getAttachments
     * @param TransportBuilderByStore|null $transportBuilderByStore
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        private readonly File $fileDriver,
        private readonly FileInfo $fileInfo,
        private readonly GetAttachmentsInterface $getAttachments,
        TransportBuilderByStore $transportBuilderByStore = null
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $transportBuilder,
            $transportBuilderByStore
        );

        $this->transportBuilder->resetAttachments();
    }

    /**
     * @inheritDoc
     */
    public function send()
    {
        $this->addAttachments();
        parent::send();
    }

    /**
     * @inheritDoc
     */
    public function sendCopyTo()
    {
        $this->addAttachments();
        parent::sendCopyTo();
    }

    /**
     * Add attachments to transport builder
     *
     * @return array
     */
    private function addAttachments(): array
    {
        $templateVars = $this->templateContainer->getTemplateVars();
        $storeId = $templateVars['store']->getId();
        $type = false;
        $result = [];

        if (isset($templateVars['invoice'])) {
            $type = 'invoice';
        } elseif (isset($templateVars['shipment'])) {
            $type = 'shipment';
        } elseif (isset($templateVars['creditmemo'])) {
            $type = 'creditmemo';
        } elseif (isset($templateVars['order'])) {
            $type = 'order';
        }

        if ($type === false) {
            return $result;
        }

        try {
            $attachments = $this->getAttachments->get($type, $storeId);
        } catch (LocalizedException) {
            return $result;
        }

        foreach ($attachments as $attachment) {
            try {
                $content = $this->fileDriver->fileGetContents($attachment);
            } catch (FileSystemException) {
                continue;
            }
            $attachmentInfo = $this->fileInfo->getPathInfo($attachment);

            /** @var TransportBuilder $transportBuilder */
            $result[] = $this->transportBuilder->addAttachment(
                $content,
                $attachmentInfo['basename'],
                'application/pdf',
                Mime::DISPOSITION_ATTACHMENT,
                Mime::ENCODING_BASE64
            );
        }
        return $result;
    }
}
