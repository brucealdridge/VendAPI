<?php

namespace VendAPI;

class VendAPI
{
    private $url;
    private $curl;

    private $last_result_raw;
    private $last_result;
    private $curl_debug;

    public $debug = false;

    public $default_outlet = 'Main Outlet';

    public function __construct($url, $username, $password)
    {
        // trim trailing slash for niceness
        $this->url = rtrim($url, '/');

        $this->curl = curl_init();

        // setup default curl options
        $options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FAILONERROR => 1,
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_USERPWD => $username.':'.$password,
            CURLOPT_HTTPHEADER,array('Accept: application/json','Content-Type: application/json'),
        );
        if ($this->debug) {
            $options[CURLINFO_HEADER_OUT] = 1;
        }

        curl_setopt_array($this->curl, $options);
    }
    public function __destruct()
    {
        // close curl nicely
        curl_close($this->curl);
    }

    public function getUsers()
    {
        $result = $this->request('/api/users');

        $users = array();
        foreach ($result->users as $user) {
            $users[] = new VendUser($user, $this);
        }

        return $users;
    }
    /**
     * Get all products
     * @param  array $options .. optional
     * @return array
     */
    public function getProducts($options = array())
    {
        $path = '';
        if (count($options)) {
            foreach ($options as $k => $v) {
                $path .= '/'.$k.'/'.$v;
            }
        }

        return $this->apiGetProducts($path);
    }
    /**
     * Get all sales
     * @param  array $options .. optional
     * @return array
     */
    public function getSales($options = array())
    {
        $path = '';
        if (count($options)) {
            foreach ($options as $k => $v) {
                $path .= '/'.$k.'/'.$v;
            }
        }

        return $this->apiGetSales($path);
    }
    /**
     * Get a single product by id
     * @return object
     */
    public function getProduct($id)
    {
        $result = $this->getProducts(array('id' => $id));
        return is_array($result) && isset($result[0]) ? $result[0] : new VendProduct(null, $this);
    }
    public function getProductsSince($date)
    {
        $result = $this->getProducts(array('since' => $date));
        return $result;
    }
    public function getSalesSince($date)
    {
        $result = $this->getSales(array('since' => $date));
        return $result;
    }

    private function apiGetProducts($path)
    {
        $result = $this->request('/api/products'.$path);

        $products = array();
        foreach ($result->products as $product) {
            $products[] = new VendProduct($product, $this);
        }

        return $products;
    }
    private function apiGetSales($path)
    {
        $result = $this->request('/api/register_sales'.$path);

        $sales = array();
        foreach ($result->register_sales as $s) {
            $sales[] = new VendSale($s, $this);
        }

        return $sales;
    }
    /**
     * Save vendproduct object to vend
     * @param  object $productl
     * @return object
     */
    public function saveProduct($product)
    {
        $result = $this->request('/api/products', $product->toArray());

        return new VendProduct($result->product, $this);
    }

    private function request($path, $data = null)
    {
        // TODO handle pager
        if ($data !== null) {
            // setup for a post'
            curl_setopt_array(
                $this->curl,
                array(
                    CURLOPT_POST => 1,
                    CURLOPT_POSTFIELDS => array('data' => json_encode($data)),
                    CURLOPT_CUSTOMREQUEST => 'POST'
                )
            );
        } else {
            // reset to a get
            curl_setopt_array(
                $this->curl,
                array(
                    CURLOPT_HTTPGET => 1,
                    CURLOPT_POSTFIELDS => null,
                    CURLOPT_CUSTOMREQUEST => 'GET'
                )
            );

        }
        if ($this->debug) {
            curl_setopt($this->curl, CURLOPT_VERBOSE, true);
        }

        curl_setopt($this->curl, CURLOPT_URL, $this->url.$path);
        //'api/register_sales/since/'.'2012-09-12 09:05:00');
        //curl_setopt($ch,CURLOPT_URL, $url.'api/stock_takes');

        $rawresult = curl_exec($this->curl);
        $result = json_decode($rawresult);

        if ($result && isset($result->error)) {
            throw new Exception($result->error .' : '. $result->details);
        }

        if ($this->debug) {
            $this->last_result_raw = $rawresult;
            $this->curl_debug = curl_getinfo($this->curl);
            $this->last_result = $result;
        }

        return $result;
    }
}

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
class VendSale extends VendObject
{
}
class VendUser extends VendObject
{
}
abstract class VendObject
{
    protected $vend;
    protected $vendObjectProperties = array();

    public function __construct($data = null, &$v = null)
    {
        $this->vend = $v;
        if ($data) {
            foreach ($data as $key => $value) {
                $this->vendObjectProperties[$key] = $value;
            }
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
}
