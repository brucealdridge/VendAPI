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

abstract class VendObject
{
    protected $vend;
    protected $vendObjectProperties = array();
    protected $initialObjectProperties = array();

    public function __construct($data = null, &$v = null)
    {
        $this->vend = $v;
        if ($data) {
            foreach ($data as $key => $value) {
                $this->vendObjectProperties[$key] = $value;
            }
            $this->initialObjectProperties = $this->vendObjectProperties;
        }
    }

    public function __set($key, $value)
    {
        $this->vendObjectProperties[$key] = $value;
    }
    public function __get($key)
    {
        if (array_key_exists($key, $this->vendObjectProperties)) {
            return $this->vendObjectProperties[$key];
        }

        return null;
    }

    public function __isset($key)
    {
        return isset($this->vendObjectProperties[$key]);
    }

    public function __unset($key)
    {
        unset($this->vendObjectProperties[$key]);
    }
    public function clear()
    {
        $this->vendObjectProperties = array();
    }
    public function toArray()
    {
        return $this->vendObjectProperties;
    }
    /**
     * will return an array of all changed properties and the id
     * return array
     */
    public function saveArray()
    {
        // only output the changed properties
        $output = $this->vendObjectProperties;
        foreach($output as $key => $value) {
            if ($key != 'id' && isset($this->initialObjectProperties[$key]) && $value == $this->initialObjectProperties[$key]) {
                unset($output[$key]);
            }
        }
        return $output;
    }
}
