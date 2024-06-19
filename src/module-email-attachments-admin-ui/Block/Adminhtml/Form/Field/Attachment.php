<?php
declare(strict_types=1);

namespace Team23\EmailAttachmentsAdminUi\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Attachment extends AbstractFieldArray
{
    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'file_path',
            ['label' => __('File Path'),]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add File');
    }
}
