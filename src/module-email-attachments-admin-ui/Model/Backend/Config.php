<?php

namespace Team23\EmailAttachmentsAdminUi\Model\Backend;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 *
 * @package Team23\EmailAttachmentsAdminUi\Model\Backend
 */
class Config
{
    /**
     * Order mail attachments XML path
     *
     * @const string
     */
    const ORDER_EMAIL_ATTACHMENTS_PATH = 'sales_pdf/email_attachments/order';

    /**
     * Invoice mail attachments XML path
     *
     * @const string
     */
    const INVOICE_EMAIL_ATTACHMENTS_PATH = 'sales_pdf/email_attachments/invoice';

    /**
     * Shipment mail attachments XML path
     *
     * @const string
     */
    const SHIPMENT_EMAIL_ATTACHMENTS_PATH = 'sales_pdf/email_attachments/shipment';

    /**
     * Credit memo mail attachments XML path
     *
     * @const string
     */
    const CREDIT_MEMO_EMAIL_ATTACHMENTS_PATH = 'sales_pdf/email_attachments/creditmemo';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var string
     */
    private $scopeType = ScopeInterface::SCOPE_STORE;

    /**
     * Config constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    /**
     * Retrieve order email attachments
     *
     * @param int|null $storeId
     * @return array
     */
    public function getOrderEmailAttachments(int $storeId = null): array
    {
        return $this->getConfigValue(
            self::ORDER_EMAIL_ATTACHMENTS_PATH,
            $this->scopeType,
            $storeId
        );
    }

    /**
     * Retrieve invoice email attachments
     *
     * @param int|null $storeId
     * @return array
     */
    public function getInvoiceEmailAttachments(int $storeId = null): array
    {
        return $this->getConfigValue(
            self::INVOICE_EMAIL_ATTACHMENTS_PATH,
            $this->scopeType,
            $storeId
        );
    }

    /**
     * Retrieve shipment email attachments
     *
     * @param int|null $storeId
     * @return array
     */
    public function getShipmentEmailAttachments(int $storeId = null): array
    {
        return $this->getConfigValue(
            self::SHIPMENT_EMAIL_ATTACHMENTS_PATH,
            $this->scopeType,
            $storeId
        );
    }

    /**
     * Retrieve credit memo email attachments
     *
     * @param int|null $storeId
     * @return array
     */
    public function getCreditMemoEmailAttachments(int $storeId = null): array
    {
        return $this->getConfigValue(
            self::CREDIT_MEMO_EMAIL_ATTACHMENTS_PATH,
            $this->scopeType,
            $storeId
        );
    }

    /**
     * Retrieve configuration value
     *
     * @param string $path
     * @param string $scopeType
     * @param int|null $storeId
     * @return array
     */
    private function getConfigValue(string $path, string $scopeType, int $storeId = null): array
    {
        $result = [];
        $value  = $this->scopeConfig->getValue(
            $path,
            $scopeType,
            $storeId
        );

        if (!is_string($value)) {
            return $result;
        }

        $configValue = $this->serializer->unserialize($value);

        if (!is_array($configValue)) {
            return $result;
        }

        foreach ($configValue as $item) {
            if (isset($item['file_path'])) {
                $result[] = $item['file_path'];
            }
        }

        return $result;
    }
}
