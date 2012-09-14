<?php

class vendapi {
  private $url;
  private $curl;

  private $last_result_raw;
  private $last_result;
  private $curl_debug;

  public $debug = false;

  function __construct($url, $username, $password) {
    // trim trailing slash for niceness
    $this->url = rtrim($url,'/');

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
  function __destruct() {
    // close curl nicely
    curl_close($this->curl);
  }

  public function getUsers() {
    $result = $this->request('/api/users');

    $users = array();
    foreach($result->users as $user) {
      $users[] = new venduser($user, $this);
    }

    return $users;
  }
  public function getProducts() { return $this->_getProducts(); }
  public function getProduct($id) { $result = $this->_getProducts('/id/'.$id); return $result[0]; }

  private function _getProducts($path) {
    $result = $this->request('/api/products'.$path);

    $products = array();
    foreach($result->products as $product) {
      $products[] = new vendproduct($product, $this);
    }

    return $products;
  }
  public function saveProduct($product) {
    $this->debug = true;
    $result = $this->request('/api/products',$product->toArray());
    var_dump($this);exit;
    return new vendproduct($result->product, $this);
  }

  private function request($path, $data = null) {
    // TODO handle pager
    if ($data !== null) {
      // setup for a post'
      curl_setopt_array($this->curl,array(
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => array('data' => json_encode($data)),
        CURLOPT_CUSTOMREQUEST => 'POST'
        ));
    }else{
      // reset to a get
      curl_setopt_array($this->curl,array(
        CURLOPT_HTTPGET => 1,
        CURLOPT_POSTFIELDS => null,
        CURLOPT_CUSTOMREQUEST => 'GET'
        ));

    }

    curl_setopt($this->curl,CURLOPT_URL, $this->url.$path);
    //'api/register_sales/since/'.'2012-09-12 09:05:00');
    //curl_setopt($ch,CURLOPT_URL, $url.'api/stock_takes');
    curl_setopt($this->curl,CURLOPT_HTTPHEADER,array('Accept: application/json','Content-Type: application/json'));

    $rawresult = curl_exec($this->curl);
    $result = json_decode($rawresult);

    if ($this->debug) {
      $this->last_result_raw = $rawresult;
      $this->curl_debug = curl_getinfo($this->curl);
      $this->last_result = $result;
    }
    return $result;
  }
}

class vendproduct extends vendobject {
  public function save () {
    // wipe current product and replace with new objects properties
    $this->_properties = $this->vend->saveProduct($this)->toArray();
  }
}
class venduser extends vendobject {

}
abstract class vendobject {
  protected $vend;
  protected $_properties;

  function __construct($data = null, $v = null) {
    $this->vend = $v;
    if ($data) {
      foreach($data as $key => $value) {
        $this->_properties[$key] = $value;
      }
    }
  }

  public function __set($key, $value) {
    $this->_properties[$key] = $value;
  }
  public function __get($key) {
    if (array_key_exists($key, $this->_properties)) {
      return $this->_properties[$key];
    }
    return null;
  }

  public function __isset($key) {
    return isset($this->_properties[$key]);
  }

  public function __unset($key) {
    unset($this->_properties[$key]);
  }
  public function clear() {
    $this->_properties = array();
  }
  public function toArray() {
    return $this->_properties;
  }
}
