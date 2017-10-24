<?php

/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magegiant.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @copyright   Copyright (c) 2012 Magegiant (http://www.magegiant.com/)
 * @license     http://www.magegiant.com/license-agreement.html
 */
class Magegiant_Onestepcheckout_Helper_Block extends Mage_Core_Helper_Data
{

    /**
     * Get ARP2 rules collection
     */
    public function getStaticBlockCollection()
    {
        $collection = Mage::getResourceModel('cms/block_collection');

        return $collection;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray     = array(
            array(
                'value' => null,
                'label' => $this->__('--Please Select--'),
            )
        );
        $blockCollection = $this->getStaticBlockCollection();
        if (!is_null($blockCollection)) {
            foreach ($blockCollection as $block) {
                $optionArray[] = array(
                    'value' => $block->getIdentifier(),
                    'label' => $block->getTitle(),
                );
            }
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array           = array();
        $blockCollection = $this->getStaticBlockCollection();
        if (!is_null($blockCollection)) {
            foreach ($blockCollection as $block) {
                $array[$block->getIdentifier()] = $block->getIdentifier();
                $array[$block->getTitle()]      = $block->getTitle();
            }
        }

        return $array;
    }


}