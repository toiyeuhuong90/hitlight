<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */


class MageWorx_SeoBase_Model_Canonical_Default extends MageWorx_SeoBase_Model_Canonical_Abstract
{
    /**
     *
     * @var string
     */
    protected $_entityType = 'default';

    protected function _getCanonicalUrl($item = null)
    {
        return $this->renderUrl($this->_cropGetParameters(Mage::helper('core/url')->getCurrentUrl()));
    }
}