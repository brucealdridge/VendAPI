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

class VendCustomer extends VendObject
{
    /**
     * will create/update the user using the vend api and this object will be updated
     * @return null
     */
    public function save ()
    {
        // wipe current user and replace with new objects properties
        $this->vendObjectProperties = $this->vend->saveCustomer($this)->toArray();
    }
}
