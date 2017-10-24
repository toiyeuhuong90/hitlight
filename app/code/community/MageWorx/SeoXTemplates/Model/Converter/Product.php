<?php

/**
 * MageWorx
 * MageWorx SeoXTemplates Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoXTemplates
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
abstract class MageWorx_SeoXTemplates_Model_Converter_Product extends MageWorx_SeoXTemplates_Model_Converter
{
    /**
     * Retrive converted string by template code
     * @param array $vars
     * @param string $templateCode
     * @return string
     */
    protected function __convert($vars, $templateCode)
    {
        $convertValue = $templateCode;
        $includingTax = (Mage::helper('tax')->displayPriceIncludingTax()) ? true : null;

        foreach ( $vars as $key => $params ) {
            foreach ( $params['attributes'] as $attributeCode ) {
                switch ($attributeCode) {
                    case 'name':
                        $value = $this->_convertName($attributeCode);
                        break;
                    case 'category':
                        $value = $this->_convertCategory();
                        break;
                    case 'categories':
                        $value = $this->_convertCategories();
                        break;
                    case 'store_view_name':
                        $value = $this->_convertStoreViewName();
                        break;
                    case 'store_name':
                        $value = $this->_convertStoreName();
                        break;
                    case 'website_name':
                        $value = $this->_convertWebsiteName();
                        break;
                    case 'price':
                        $value = $this->_convertPrice($includingTax);
                        break;
                    case 'special_price':
                        $value = $this->_convertSpecialPrice($includingTax);
                        break;
                    default:
                        $value = $this->_convertAttribute($attributeCode);
                        break;
                }

                if ($value) {
                    $value = $params['prefix'] . $value . $params['suffix'];
                    break;
                }
            }

            $convertValue = str_replace($key, $value, $convertValue);
        }

        return $this->_render($convertValue);
    }

    /**
     * Retrive converted string
     * @param string $attribute
     * @return string
     */
    protected function _convertName($attribute)
    {
        return $this->_convertAttribute($attribute);
    }

    /**
     * Retrive converted string
     * @return string
     */
    protected function _convertStoreViewName()
    {
        return Mage::app()->getStore($this->_item->getStoreId())->getName();
    }

    /**
     * Retrive converted string
     * @return string
     */
    protected function _convertStoreName()
    {
        return Mage::app()->getStore($this->_item->getStoreId())->getGroup()->getName();
    }

    /**
     * Retrive converted string
     * @return string
     */
    protected function _convertWebsiteName()
    {
        return Mage::app()->getStore($this->_item->getStoreId())->getWebsite()->getName();
    }

    protected function _convertCategory()
    {
        return '[category]';
    }

    protected function _convertCategories()
    {
        return '[categories]';
    }

    /**
     * Retrive converted string
     * @param int $includingTax
     * @return string
     */
    protected function _convertPrice($includingTax)
    {
        if ($this->_item->getTypeId() == 'bundle') {
            $value = $this->_convertPriceForBundle($includingTax);
        }
        elseif ($this->_item->getTypeId() == 'grouped') {
            $value = $this->_convertPriceForGrouped($includingTax);
        }
        else {
            $value = $this->_convertPriceByDefault($includingTax);
        }
        return $value;
    }

    /**
     * Retrive converted string
     * @param int $includingTax
     * @return string
     */
    protected function _convertPriceByDefault($includingTax)
    {
        $value = '';
        if ($this->_item->getPrice() > 0) {
            $price = Mage::helper('tax')->getPrice(
                $this->_item, $this->_item->getPrice(), $includingTax, null, null, null, $this->_item->getStoreId(),
                Mage::helper('tax')->priceIncludesTax($this->_item->getStoreId())
            );
            $value = $this->_currencyByStore($price, $this->_item->getStore(), true, FALSE);
            if ($this->_item->getMinimalPrice() && $this->_item->getMinimalPrice() <> $this->_item->getPrice()) {
                $price = Mage::helper('tax')->getPrice($this->_item, $this->_item->getMinimalPrice(), $includingTax);
                $value = Mage::helper('core')->__('Starting at') . ' ' . $this->_currencyByStore($price,
                        $this->_item->getStore(), true, FALSE);
            }
        }
        return $value;
    }

    /**
     * Retrive converted string
     * @return string
     */
    protected function _convertPriceForBundle()
    {
        $value = '';
        if ($this->_item->getStoreId() !== "") {
            if (is_callable(array($this->_item->getPriceModel(), 'getPricesDependingOnTax'))) {
                list($_minimalPriceTax, $_maximalPriceTax) = $this->_item->getPriceModel()->
                    getPricesDependingOnTax($this->_item, null, null, false);
            }
            else {
                //For old version ~ 1.4
                $prices           = $this->_getBundlePrices($this->_item);
                $_minimalPriceTax = array_shift($prices);
            }

            if (!empty($_minimalPriceTax) && !empty($_maximalPriceTax)) {
                $value = Mage::helper('core')->__('From') . ' ' . Mage::helper('core')->currency($_minimalPriceTax,
                        true, FALSE) . " " . Mage::helper('core')->__('To') . " " . Mage::helper('core')->currency($_maximalPriceTax,
                        true, FALSE);
            }
            elseif (!empty($_minimalPriceTax)) {
                $value = Mage::helper('core')->__('From') . ' ' . Mage::helper('core')->currency($_minimalPriceTax,
                        true, FALSE);
            }
        }
        return $value;
    }

    /**
     * Retrive converted string
     * @param int $includingTax
     * @return string
     */
    protected function _convertPriceForGrouped($includingTax)
    {
        $value = '';
        if ($this->_item->getMinimalPrice()) {
            $price = Mage::helper('tax')->getPrice($this->_item, $this->_item->getMinimalPrice(), $includingTax);
            $value = Mage::helper('core')->__('Starting at') . ' ' . Mage::helper('core')->currencyByStore($price,
                    $this->_item->getStore(), true, FALSE);
        }
        return $value;
    }

    /**
     * Retrive converted string
     * @param int $includingTax
     * @return string
     */
     protected function _convertSpecialPrice($includingTax)
    {
        $value = '';
        if ($this->_item->getSpecialPrice() > 0) {
            if (Mage::app()->getLocale()->isStoreDateInInterval($this->_item->getStore(), $this->_item->getSpecialFromDate(), $this->_item->getSpecialToDate())) {
                $price = Mage::helper('tax')->getPrice($this->_item, $this->_item->getSpecialPrice(), $includingTax);
                $value = $this->_currencyByStore($price, $this->_item->getStore(), true, FALSE);
            }
        }
        return $value;
    }

    /**
     * Retrive converted string
     * @param string $attribute
     * @return string
     */
    protected function _convertAttribute($attributeCode)
    {
        $tempValue = '';
        $value     = $this->_item->getData($attributeCode);
        if ($_attr     = $this->_item->getResource()->getAttribute($attributeCode)) {
            $_attr->setStoreId($this->_item->getStoreId());
            if($_attr->usesSource()){
                $tempValue = $_attr->setStoreId($this->_item->getStoreId())->getSource()->getOptionText($this->_item->getData($attributeCode));
            }
        }
        if ($tempValue) {
            $value = $tempValue;
        }
        if (!$value) {
            if ($this->_item->getTypeId() == 'configurable') {
                $productAttributeOptions = $this->_item->getTypeInstance(true)->getConfigurableAttributesAsArray($this->_item);
                $attributeOptions        = array();
                foreach ( $productAttributeOptions as $productAttribute ) {
                    if ($productAttribute['attribute_code'] == $attributeCode) {
                        foreach ( $productAttribute['values'] as $attribute ) {
                            $attributeOptions[] = $attribute['store_label'];
                        }
                    }
                }
                if (count($attributeOptions) == 1) {
                    $value = array_shift($attributeOptions);
                }
            }
            else {
                $value = $this->_item->getData($attributeCode);
            }
        }
        return is_array($value) ? implode(', ', $value) : $value;
    }

    /**
     *
     * @param string $converValue
     * @return string
     */
    protected function _render($converValue)
    {
        return trim($converValue);
    }

    /**
     * Convert and format price value for specified store
     *
     * @param   float $value
     * @param   int|Mage_Core_Model_Store $store
     * @param   bool $format
     * @param   bool $includeContainer
     * @return  mixed
     */
    protected function _currencyByStore($value, $store = null, $format = true, $includeContainer = true)
    {
        if (is_callable(array(Mage::helper('core'), 'currencyByStore'))) {
            return Mage::helper('core')->currencyByStore($value, $store, $format, $includeContainer);
        }
        else {
            //Mageworx copy from helper('core') for old magento version
            try {
                if (!($store instanceof Mage_Core_Model_Store)) {
                    $store = Mage::app()->getStore($store);
                }

                $value = $store->convertPrice($value, $format, $includeContainer);
            }
            catch (Exception $e) {
                $value = $e->getMessage();
            }
            return $value;
        }
    }

    /**
     * Retrive prices for bundle product
     * @param type $_product
     * @return array
     */
    protected function _getBundlePrices($_product = null)
    {
        if (!$_product) {
            $_product = Mage::registry('current_product');
        }

//        Mage_Bundle_Block_Catalog_Product_Price

        $prices      = array();
        $_priceModel = $_product->getPriceModel();

        if (is_callable(array($_priceModel, 'getPricesDependingOnTax'))) {
            list($_minimalPriceTax, $_maximalPriceTax) = $_priceModel->getPricesDependingOnTax($_product, null, null,
                false);
            list($_minimalPriceInclTax, $_maximalPriceInclTax) = $_priceModel->getPricesDependingOnTax($_product, null,
                true, false);
        }
        else {
            list($_minimalPrice, $_maximalPrice) = $_product->getPriceModel()->getPrices($_product);
            $_maximalPriceTax     = $_minimalPriceTax     = Mage::helper('tax')->getPrice($_product, $_minimalPrice);
            $_maximalPriceInclTax = $_minimalPriceInclTax = Mage::helper('tax')->getPrice($_product, $_minimalPrice,
                true);
        }

        $_weeeTaxAmount = 0;

        if ($_product->getPriceType() == 1) {
            $_weeeTaxAmount          = Mage::helper('weee')->getAmount($_product);
            $_weeeTaxAmountInclTaxes = $_weeeTaxAmount;
            if (Mage::helper('weee')->isTaxable()) {
                $_attributes             = Mage::helper('weee')->getProductWeeeAttributesForRenderer($_product, null,
                    null, null, true);
                $_weeeTaxAmountInclTaxes = Mage::helper('weee')->getAmountInclTaxes($_attributes);
            }
            if ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, array(0, 1, 4))) {
                $_minimalPriceTax += $_weeeTaxAmount;
                $_minimalPriceInclTax += $_weeeTaxAmountInclTaxes;
            }
            if ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, 2)) {
                $_minimalPriceInclTax += $_weeeTaxAmountInclTaxes;
            }
        }

        if ($_product->getPriceView()):
            if ($this->_displayBothPrices($_product)):
                $prices[] = $_minimalPriceInclTax;
                $prices[] = $_minimalPriceTax;
            else:
                $prices[] = $_minimalPriceTax;
                if (Mage::helper('weee')->typeOfDisplay($_product, 2) && $_weeeTaxAmount):
                    $prices[] = $_minimalPriceInclTax;
                endif;
            endif;
        else:
            if ($_minimalPriceTax <> $_maximalPriceTax):
                if ($this->_displayBothPrices($_product)):
                    $prices[] = $_minimalPriceInclTax;
                    $prices[] = $_minimalPriceTax;
                else:
                    $prices[] = $_minimalPriceTax;
                    if (Mage::helper('weee')->typeOfDisplay($_product, 2) && $_weeeTaxAmount):
                        $prices[] = $_minimalPriceInclTax;
                    endif;
                endif;

                if ($_product->getPriceType() == 1) {
                    if ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, array(0, 1, 4))) {
                        $_maximalPriceTax += $_weeeTaxAmount;
                        $_maximalPriceInclTax += $_weeeTaxAmountInclTaxes;
                    }
                    if ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($_product, 2)) {
                        $_maximalPriceInclTax += $_weeeTaxAmountInclTaxes;
                    }
                }

                if ($this->_displayBothPrices($_product)):
                    $prices[] = $_maximalPriceInclTax;
                    $prices[] = $_maximalPriceTax;
                else:
                    $prices[] = $_maximalPriceTax;
                    if (Mage::helper('weee')->typeOfDisplay($_product, 2) && $_weeeTaxAmount):
                        $prices[] = $_maximalPriceInclTax;
                    endif;
                endif;
            else:
                if ($this->_displayBothPrices($_product)):
                    $prices[] = $_minimalPriceInclTax;
                    $prices[] = $_minimalPriceTax;
                else:
                    $prices[] = $_minimalPriceTax;
                    if (Mage::helper('weee')->typeOfDisplay($_product, 2) && $_weeeTaxAmount):
                        $prices[] = $_minimalPriceInclTax;
                    endif;
                endif;
            endif;
        endif;

        return $prices;
    }

}
