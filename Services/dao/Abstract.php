<?php
/**
 * Magento Itipart Order Export Module
 *
 * NOTICE OF LICENSE
 *
 *
 * DISCLAIMER
 *
 *
 * @category   Bluejalappeno
 * @package    Bluejalappeno_OrderExport
 * @copyright  Copyright (c) 2015 ITIpart (http://www.itipart.com)
 * @license    1.0
 * @author     Yassine BELHAJ SALAH <yassine.belhajsalah@itipart.tn>
 * */

$parent_path = substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), 'synchro'));
$mageFilename = $parent_path . 'app/Mage.php';
    
require_once $mageFilename;
    /**
     * Returns the name of the website, store and store view the order was placed in.
     *
     * @param Mage_Sales_Model_Order $order The order to return info from
     * @return String The name of the website, store and store view the order was placed in
     */
     function getStoreName($order)
    {
        $store = Mage::app()->getStore($order->getStoreId);
        $name = array(
        $store->getWebsite()->getName(),
        $store->getGroup()->getName(),
        $store->getName()
        );
        return implode(', ', $name);
    }

    /**
     * Returns the payment method of the given order.
     *
     * @param Mage_Sales_Model_Order $order The order to return info from
     * @return String The name of the payment method
     */
     function getPaymentMethod($order)
    {
        return $order->getPayment()->getMethod();
    }

	/**
     * Returns the credit card type of the given order.
     *
     * @param Mage_Sales_Model_Order $order The order to return info from
     * @return String The cc type
     */
     function getCcType($order)
    {
        return $order->getPayment()->getCcType();
    }

    /**
     * Returns the shipping method of the given order.
     *
     * @param Mage_Sales_Model_Order $order The order to return info from
     * @return String The name of the shipping method
     */
     function getShippingMethod($order)
    {
        if (!$order->getIsVirtual() && $order->getShippingDescription()) {
            return $order->getShippingDescription();
        }
        else if (!$order->getIsVirtual() && $order->getShippingMethod()) {
        	return $order->getShippingMethod();
        }
        return '';
    }

    /**
     * Returns the total quantity of ordered items of the given order.
     *
     * @param Mage_Sales_Model_Order $order The order to return info from
     * @return int The total quantity of ordered items
     */
    function getTotalQtyItemsOrdered($order) {
        $qty = 0;
        $orderedItems = $order->getItemsCollection();
        foreach ($orderedItems as $item)
        {
            if (!$item->isDummy()) {
                $qty += (int)$item->getQtyOrdered();
            }
        }
        return $qty;
    }

    /**
     * Returns the sku of the given item dependant on the product type.
     *
     * @param Mage_Sales_Model_Order_Item $item The item to return info from
     * @return String The sku
     */
    function getItemSku($item)
    {
        if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return $item->getProductOptionByCode('simple_sku');
        }
        return $item->getSku();
    }

    /**
     * Returns the options of the given item separated by comma(s) like this:
     * option1: value1, option2: value2
     *
     * @param Mage_Sales_Model_Order_Item $item The item to return info from
     * @return String The item options
     */
    function getItemOptions($item)
    {
        $options = '';
        if ($orderOptions = getItemOrderOptions($item)) {
            foreach ($orderOptions as $_option) {
                if (strlen($options) > 0) {
                    $options .= ', ';
                }
                $options .= $_option['label'].': '.$_option['value'];
            }
        }
        return $options;
    }

    /**
     * Returns all the product options of the given item including additional_options and
     * attributes_info.
     *
     * @param Mage_Sales_Model_Order_Item $item The item to return info from
     * @return Array The item options
     */
    function getItemOrderOptions($item)
    {
        $result = array();
        if ($options = $item->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        return $result;
    }

    /**
     * Calculates and returns the grand total of an item including tax and excluding
     * discount.
     *
     * @param Mage_Sales_Model_Order_Item $item The item to return info from
     * @return Float The grand total
     */
    function getItemTotal($item)
    {
        return $item->getRowTotal() - $item->getDiscountAmount() + $item->getTaxAmount() + $item->getWeeeTaxAppliedRowAmount();
    }

    /**
     * Formats a price by adding the currency symbol and formatting the number
     * depending on the current locale.
     *
     * @param Float $price The price to format
     * @param Mage_Sales_Model_Order $formatter The order to format the price by implementing the method formatPriceTxt($price)
     * @return String The formatted price
     */
    function formatPrice($price, $formatter)
    {
        $price = $formatter->formatPriceTxt($price);
        $price = str_replace('�', '', $price);
		$price = str_replace('€', '�', $price);
    	return $price;
    }

 	function getStreet($address) {
    	if ($address->getStreet2() != '') {
    		return $address->getStreet1() .' ' .$address->getStreet2();
    	}
    	else {
    		return $address->getStreet1();
    	}
    }

?>