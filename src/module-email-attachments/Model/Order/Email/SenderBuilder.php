<?php

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
 * Class SenderBuilder
 *
 * @package Team23\EmailAttachments\Model\Order\Email
 */
class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var FileInfo
     */
    private $fileInfo;

    /**
     * @var GetAttachmentsInterface
     */
    private $getAttachments;

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
        File $fileDriver,
        FileInfo $fileInfo,
        GetAttachmentsInterface $getAttachments,
        TransportBuilderByStore $transportBuilderByStore = null
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $transportBuilder,
            $transportBuilderByStore
        );

        $this->fileDriver      = $fileDriver;
        $this->fileInfo        = $fileInfo;
        $this->getAttachments = $getAttachments;

        $this->transportBuilder->resetAttachments();
    }

    /**
     * @inheritDoc
     */
    public function send()
    {
        $attachments = $this->addAttachments();

        if (empty($attachments)) {
            parent::send();
        } else {
            $this->configureEmailTemplate();

            $this->transportBuilder->addTo(
                $this->identityContainer->getCustomerEmail(),
                $this->identityContainer->getCustomerName()
            );

            $copyTo = $this->identityContainer->getEmailCopyTo();

            if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'bcc') {
                foreach ($copyTo as $email) {
                    $this->transportBuilder->addBcc($email);
                }
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        }
    }

    /**
     * @inheritDoc
     */
    public function sendCopyTo()
    {
        $attachments = $this->addAttachments();

        if (empty($attachments)) {
            parent::sendCopyTo();
        } else {
            $copyTo = $this->identityContainer->getEmailCopyTo();

            if (!empty($copyTo)) {
                foreach ($copyTo as $email) {
                    $this->configureEmailTemplate();
                    $this->transportBuilder->addTo($email);
                    $transport = $this->transportBuilder->getTransport();
                    $transport->sendMessage();
                }
            }
        }
    }

    /**
     * Add attachments to transport builder
     *
     * @return array
     */
    private function addAttachments(): array
    {
        $templateVars = $this->templateContainer->getTemplateVars();
        $storeId      = $templateVars['store']->getId();
        $type         = false;
        $result       = [];

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
        } catch (LocalizedException $e) {
            return $result;
        }

        foreach ($attachments as $attachment) {
            try {
                $content = $this->fileDriver->fileGetContents($attachment);
            } catch (FileSystemException $e) {
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
