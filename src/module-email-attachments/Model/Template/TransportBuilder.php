<?php

namespace Team23\EmailAttachments\Model\Template;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;

/**
 * Class TransportBuilder
 *
 * @package Team23\EmailAttachments\Model\Template
 */
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @var EmailMessageInterfaceFactory
     */
    private $emailMessageInterfaceFactory;

    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageInterfaceFactory;

    /**
     * @var MimePartInterfaceFactory
     */
    private $mimePartInterfaceFactory;

    /**
     * @var AddressConverter
     */
    private $addressConverter;

    /**
     * @var array
     */
    private $messageData = [];

    /**
     * @var array
     */
    private $attachments = [];

    /**
     * TransportBuilder constructor
     *
     * @param FactoryInterface $templateFactory
     * @param MessageInterface $message
     * @param SenderResolverInterface $senderResolver
     * @param ObjectManagerInterface $objectManager
     * @param TransportInterfaceFactory $mailTransportFactory
     * @param MessageInterfaceFactory $messageFactory
     * @param EmailMessageInterfaceFactory $emailMessageInterfaceFactory
     * @param MimeMessageInterfaceFactory $mimeMessageInterfaceFactory
     * @param MimePartInterfaceFactory $mimePartInterfaceFactory
     * @param AddressConverter $addressConverter
     */
    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MessageInterfaceFactory $messageFactory,
        EmailMessageInterfaceFactory $emailMessageInterfaceFactory,
        MimeMessageInterfaceFactory $mimeMessageInterfaceFactory,
        MimePartInterfaceFactory $mimePartInterfaceFactory,
        AddressConverter $addressConverter
    ) {
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory,
            $messageFactory,
            $emailMessageInterfaceFactory,
            $mimeMessageInterfaceFactory,
            $mimePartInterfaceFactory,
            $addressConverter
        );

        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory;
        $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory;
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory;
        $this->addressConverter = $addressConverter;
    }

    /**
     * Add attachment
     *
     * @param string $content
     * @param string $fileName
     * @param string $mimeType
     * @param string $disposition
     * @param string $encoding
     * @return Part
     */
    public function addAttachment(
        string $content,
        string $fileName,
        string $mimeType = Mime::TYPE_OCTETSTREAM,
        string $disposition = Mime::DISPOSITION_ATTACHMENT,
        string $encoding = Mime::ENCODING_BASE64
    ): Part {
        $attachment = new Part($content);
        $attachment
            ->setType($mimeType)
            ->setEncoding($encoding)
            ->setDisposition($disposition)
            ->setFileName($fileName);
        $this->attachments[] = $attachment;
        return $attachment;
    }

    /**
     * @inheritDoc
     */
    public function addTo($address, $name = '')
    {
        $this->addAddressByType('to', $address, $name);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addCc($address, $name = '')
    {
        $this->addAddressByType('cc', $address, $name);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addBcc($address)
    {
        $this->addAddressByType('bcc', $address);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setReplyTo($email, $name = null)
    {
        $this->addAddressByType('replyTo', $email, $name);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setFromByScope($from, $scopeId = null)
    {
        $result = $this->_senderResolver->resolve($from, $scopeId);
        $this->addAddressByType('from', $result['email'], $result['name']);
        return $this;
    }

    /**
     * Reset mail attachments
     *
     * @return $this
     */
    public function resetAttachments()
    {
        return $this->reset();
    }

    /**
     * @inheritDoc
     */
    protected function reset()
    {
        $this->messageData = [];
        $this->templateIdentifier = null;
        $this->templateVars = null;
        $this->templateOptions = null;
        $this->attachments = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function prepareMessage()
    {
        $template = $this->getTemplate();
        $content = $template->processTemplate();

        switch ($template->getType()) {
            case TemplateTypesInterface::TYPE_TEXT:
                $part['type'] = MimeInterface::TYPE_TEXT;
                break;
            case TemplateTypesInterface::TYPE_HTML:
                $part['type'] = MimeInterface::TYPE_HTML;
                break;
            default:
                throw new LocalizedException(
                    new Phrase('Unknown template type')
                );
        }
        $mimePart = $this->mimePartInterfaceFactory->create(['content' => $content]);
        $parts = [$mimePart];

        foreach ($this->attachments as $attachment) {
            $parts[] = $attachment;
        }
        $this->messageData['encoding'] = $mimePart->getCharset();
        $this->messageData['body'] = $this->mimeMessageInterfaceFactory->create(['parts' => $parts]);
        $this->messageData['subject'] = html_entity_decode(
            (string) $template->getSubject(),
            ENT_QUOTES
        );
        $this->message = $this->emailMessageInterfaceFactory->create($this->messageData);
        return $this;
    }

    /**
     * Handles possible incoming types of email (string or array)
     *
     * @param string $addressType
     * @param string|array $email
     * @param string|null $name
     * @return void
     * @throws InvalidArgumentException
     */
    private function addAddressByType(string $addressType, $email, ?string $name = null): void
    {
        if (is_string($email)) {
            $this->messageData[$addressType][] = $this->addressConverter->convert($email, $name);
            return;
        }
        $convertedAddressArray = $this->addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $convertedAddressArray
            );
        } else {
            $this->messageData[$addressType] = $convertedAddressArray;
        }
    }
}
