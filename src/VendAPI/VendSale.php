<?php

/**
 * VendAPI 
 *
 * An api for communicating with vend pos software - http://www.vendhq.com
 *
 * Requires php 5.3
 *
 * @package    VendAPI
 * @author     Bruce Aldridge <bruce@incode.co.nz>
 * @copyright  2012-2013 Bruce Aldridge
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @link       https://github.com/brucealdridge/vendapi
 */

namespace VendAPI;

class VendSale extends VendObject
{
    /**
     * Gets the customer associated with the sale
     * @return VendCustomer
     */
    public function getCustomer()
    {
        $customers = $this->vend->getCustomers(array('id' => $this->customer_id));
        if(empty($customers))
        {
            throw new Exception( 'Unable to find customer ' . $this->customer_id . ' for sale' );
        }
        return $customers[0];
    }

    /**
     * Gets the products associated with the sale - return array of [VendProduct]s
     * @return array
     */
    public function getProducts()
    {
        $products = array();
        foreach( $this->register_sale_products as $product )
        {
            $products[] = $this->vend->getProduct($product->product_id);
        }
        return $products;
    }

    /**
     * will create/update the user using the vend api and this object will be updated
     * @return null
     */
    public function save ()
    {
        // wipe current user and replace with new objects properties
        $this->vendObjectProperties = $this->vend->saveSale($this)->toArray();
    }
}
