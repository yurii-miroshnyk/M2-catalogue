<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scandiweb\Test\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Samples extends AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('Simple'), 'value' => 'simple'],
                ['label' => __('Complex'), 'value' => 'complex'],
                ['label' => __('Partial'), 'value' => 'partial'],
            ];
        }
        return $this->_options;
    }
}
