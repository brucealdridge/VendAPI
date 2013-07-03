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

class VendProduct extends VendObject
{
    /**
     * will create/update the product using the vend api and this object will be updated
     * @return null
     */
    public function save ()
    {
        // wipe current product and replace with new objects properties
        $this->vendObjectProperties = $this->vend->saveProduct($this)->toArray();
    }
    /**
     * get the inventory for the given outlet (default: all outlets)
     * @param  string $outlet
     * @return int
     */
    public function getInventory($outlet = null)
    {
        $total = 0;
        if (!isset($this->vendObjectProperties['inventory']) || !is_array($this->vendObjectProperties['inventory'])) {
            return $total;
        }
        foreach ($this->vendObjectProperties['inventory'] as $o) {
            if ($o->outlet_name == $outlet) {
                return $o->count;
            }
            $total += $o->count;
        }

        return $total;
    }
    /**
     * set the inventory at $outlet to $count .. default outlet is the first found
     * @param int    $count
     * @param string $outlet
     */
    public function setInventory($count, $outlet = null)
    {
        foreach ($this->vendObjectProperties['inventory'] as $k => $o) {
            if ($o->outlet_name == $outlet || $outlet === null) {
                $this->vendObjectProperties['inventory'][$k]->count = $count;

                return;
            }
        }
        $this->vendObjectProperties['inventory'] = array(
                array(
                    "outlet_name" => (
                                      $outlet ? $outlet : ($this->vend ? $this->vend->default_outlet : 'Main Outlet')),
                    "count" =>    $count,
                )
        );
    }
}
