<?php

namespace Team23\EmailAttachmentsApi\Api;

/**
 * Interface GetAttachmentsInterface
 *
 * @api
 * @version 1.0.0
 * @package Team23\EmailAttachmentsApi\Api
 */
interface GetAttachmentsInterface
{
    /**
     * Retrieve files by type
     *
     * @param string $type
     * @param int $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get(string $type, int $storeId);
}
