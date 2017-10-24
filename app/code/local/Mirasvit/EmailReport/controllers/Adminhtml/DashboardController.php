<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Trigger Email Suite
 * @version   1.0.1
 * @revision  156
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_EmailReport_Adminhtml_DashboardController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('email')
            ->_title(Mage::helper('email')->__('Trigger Email Suite'), Mage::helper('email')->__('Trigger Email Suite'))
            ->_title(Mage::helper('email')->__('Statistic'), Mage::helper('email')->__('Statistic'));

        return $this;
    }

    public function IndexAction()
    {
        $this->_initAction();
        $this->_title($this->__('Dashboard'));

        $this->_addContent($this->getLayout()->createBlock('emailreport/adminhtml_dashboard'));
        $this->renderLayout();
    }
}