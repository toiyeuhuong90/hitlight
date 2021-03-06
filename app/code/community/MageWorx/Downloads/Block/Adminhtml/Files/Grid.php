<?php
/**
 * MageWorx
 * File Downloads & Product Attachments Extension
 *
 * @category   MageWorx
 * @package    MageWorx_Downloads
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_Downloads_Block_Adminhtml_Files_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('downloadsGrid');
        $this->setDefaultDir(Varien_Data_Collection::SORT_ORDER_ASC);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('mageworx_downloads/files')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function getStoreId()
    {
        return Mage::registry('store_id');
    }

    protected function _prepareColumns()
    {
        $helper = $this->_getHelper();
        $this->addColumn('file_id', array(
            'header' => $helper->__('ID'),
            'index' => 'file_id',
            'filter_index' => 'main_table.file_id',
            'type' => 'number',
        ));

        $this->addColumn('category', array(
            'header' => $helper->__('Category'),
            'width' => 200,
            'index' => 'title',
            'type' => 'options',
            'options' => Mage::getModel('mageworx_downloads/categories')->getCategoriesList(true)
        ));

        $this->addColumn('name', array(
            'header' => $helper->__('Name'),
            'index' => 'name',
        ));

        $this->addColumn('filename', array(
            'header' => $helper->__('File Name'),
            'index' => 'filename',
            'type' => 'text'
        ));

        $this->addColumn('type', array(
            'header' => $helper->__('Type'),
            'index' => 'type',
            'width' => 80,
        ));

        $this->addColumn('size', array(
            'header' => $helper->__('Size'),
            'index' => 'size',
            'type' => 'number',
        ));

        $this->addColumn('downloads', array(
            'header' => $helper->__('Downloads'),
            'index' => 'downloads',
            'type' => 'number',
        ));

        $this->addColumn('products_count', array(
            'header' => $helper->__('Products'),
            'index' => 'products_count',
            'type' => 'number',
            'filter' => false,
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_ids', array(
                'header'    => Mage::helper('sitemap')->__('Store View'),
                'index'     => 'store_ids',
                'type'      => 'store',
                'filter_condition_callback' => array($this, 'storeFilter')
            ));
        }

        $this->addColumn('is_active', array(
            'header' => $helper->__('Status'),
            'width' => 80,
            'index' => 'is_active',
            'type' => 'options',
            'options' => $helper->getStatusArray(),
            'index' => 'is_active',
            'filter_index' => 'main_table.is_active',
        ));

        $this->addColumn('actions', array(
            'header' => $helper->__('Action'),
            'width' => 170,
            'sortable' => false,
            'filter' => false,
            'align' => 'center',
            'renderer' => 'mageworx_downloads/adminhtml_files_grid_renderer_action',
        ));

        return parent::_prepareColumns();
    }

    public function storeFilter($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();
        $storeId = $cond['eq'];
        $colName = 'main_table.' . $column->getId();
        $collection->getSelect()
            ->where("$colName like '$storeId,%' or $colName like '%,$storeId' or $colName like '%,$storeId,%' or $colName = $storeId or $colName = 0");
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    protected function _prepareMassaction()
    {
        $helper = $this->_getHelper();
        $this->setMassactionIdField('file_id');
        $this->getMassactionBlock()->setFormFieldName('files');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $helper->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete', array('store' => $this->getStoreId())),
            'confirm' => $helper->__('Are you sure you want to do this?')
        ));

        $statuses = $helper->getStatusArray();
        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => $helper->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true, 'store' => $this->getStoreId())),
            'additional' => array(
                'visibility' => array(
                    'name' => 'is_active',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $helper->__('Status'),
                    'values' => $statuses
                )
            )
        ));

        $this->getMassactionBlock()->addItem('category', array(
            'label' => $helper->__('Change category'),
            'url' => $this->getUrl('*/*/massCategory', array('_current' => true, 'store' => $this->getStoreId())),
            'additional' => array(
                'visibility' => array(
                    'name' => 'category_id',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $helper->__('Category'),
                    'values' => Mage::getSingleton('mageworx_downloads/categories')->getCategoriesList()
                )
            )
        ));

        $this->getMassactionBlock()->addItem('customer_groups', array(
            'label' => $helper->__('Update customer groups'),
            'url' => $this->getUrl('*/*/massCustomerGroups', array('_current' => true, 'store' => $this->getStoreId())),
            'additional' => array(
                'visibility' => array(
                    'name' => 'customer_groups',
                    'type' => 'multiselect',
                    'class' => 'required-entry',
                    'label' => $helper->__('Customer groups'),
                    'style' => 'height: 75px',
                    'values' => Mage::getResourceSingleton('customer/group_collection')->toOptionArray()
                )
            )
        ));

        $this->getMassactionBlock()->addItem('reset_downloads', array(
            'label' => $helper->__('Reset downloads'),
            'url' => $this->getUrl('*/*/resetDownloads', array('store' => $this->getStoreId())),
            'confirm' => $helper->__('Are you sure you want to do this?')
        ));

        $this->getMassactionBlock()->addItem('assign_products', array(
            'label' => $helper->__('Assign products'),
            'url' => $this->getUrl('*/*/assignProducts', array('store' => $this->getStoreId())),
        ));

        return $this;
    }

    protected function _getHelper()
    {
        return Mage::helper('mageworx_downloads');
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getFileId(), 'store' => $this->getStoreId()));
    }

}
