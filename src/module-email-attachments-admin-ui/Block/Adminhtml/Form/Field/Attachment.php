<?php

namespace Team23\EmailAttachmentsAdminUi\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Attachment
 *
 * @package Team23\EmailAttachments\Block\Adminhtml\Form\Field
 */
class Attachment extends AbstractFieldArray
{
    /**
     * @inheritDoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'file_path',
            [
                'label' => __('File Path')
            ]
        );

        $this->_addAfter       = false;
        $this->_addButtonLabel = __('Add File');
    }
}
