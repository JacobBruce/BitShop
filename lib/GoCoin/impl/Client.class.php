<?php 

/**
 * GoCoin Api
 * Client class
 * Main interface to use GoCoin Api
 * 
 */

class Client
{
  /**
   * The default api options array
   * 
   * @var array
   */
  private $default_options = array(
    'client_id' => NULL,
    'client_secret' =>NULL,
    'host' => GoCoin::PRODUCTION_HOST,
    'dashboard_host' => GoCoin::PRODUCTION_DASHBOARD_HOST,
    'port' => NULL,
    'path' => '/api',
    'api_version' => 'v1',
    'secure' => TRUE,
    'method' => 'GET',
    'headers' => NULL,
    'request_id' => NULL,
    'redirect_uri' => NULL,
  );

  /**
  * The default header array
  * 
  * @var array
  */
  public $default_headers = array(
    'Content-Type' => 'application/json',
    'Cache-Control' => 'no-cache',
  );

  /**
   * options array object
   * 
   * @var array
   */
  public $options = array();

  /**
   * $header array object
   * 
   * @var array
   */
  public $headers = array();

  /**
   * token string
   * 
   * @var string
   */
  private $token = NULL;

  /**
   * error string;
   * 
   * @var mixed
   */
  private $error = "";

  /**
   * @return a client object
   */
  public static function getInstance($token=NULL)
  {
    $instance = new self();
    $instance -> setToken($token);
    return $instance;
  }

  /**
   * Constructor
   * 
   * @param array $options: initial options to use api
   */
  public function __construct($options=NULL)
  {
    if ($options == NULL)
    {
      $options = array();
    }

    $this -> options = $this -> set_default_value($options, $this -> default_options);

    if(!isset($options['headers'])) { $options['headers'] = NULL; }

    $this -> headers = $this -> default_headers;
    //merge an incoming headers
    if ($options['headers'] != NULL)
    {
      $this->headers = array_merge($options['headers'], $this -> default_headers);
    }
    //add request id if present
    if ($this -> options['request_id'] != NULL)
    {
      $this -> headers['X-Request-Id'] = $this -> options['request_id'];
    }
    $this -> auth     = new Auth($this);
    $this -> api      = new Api($this);
    $this -> user     = $this -> api -> user;
    $this -> merchant = $this -> api -> merchant;
    $this -> invoices = $this -> api -> invoices;
    $this -> accounts = $this -> api -> accounts;
  }

  /**
   * Authorization process
   * @return boolean
   */
  public function authorize_api($code)
  {
    if ($this -> getToken() !== NULL)
    {
      return TRUE;
    }
    return $this -> get_token_from_request($code);
  }

  /**
   * Get authorization code and setToken
   * if process is done successfully, return true else return false
   * @return boolean
   */
  public function get_token_from_request($code)
  {
    if (!empty($code))
    {
      $auth_code = $code;
      $options['grant_type'] = 'authorization_code';
      $options['code'] = $auth_code;
      $options['client_id'] = $this -> options['client_id'];
      $options['client_secret'] = $this -> options['client_secret'];
      if (array_key_exists('redirect_uri',$this -> options) && !empty($this -> options['redirect_uri']))
      {
        $options ['redirect_uri'] = $this -> options['redirect_uri'];
      }
      else
      {
        $options['redirect_uri'] = $this -> get_current_url();
      }
      $options = $this -> set_default_value($options, $this->options);
      $auth_result = $this -> auth -> authenticate($options);
      if (!$auth_result)
      {
        return FALSE;
      }
      $this -> setToken($auth_result -> access_token);
      return TRUE;
    }
    else
    {
      $this -> setError("Can not get authorization token, empty code");
      return FALSE;
    }
  }

  /**
   * Initialize access token and session data
   * 
   */
  public function initToken()
  {
    $this -> token = NULL;
  }

  /**
   * Return client id
   *  @return String $client_id
   */
  public function getClientId()
  {
    return $this -> options['client_id'];
  }

  /**
   * Set client_id in options array
   * 
   * @param mixed $client_id
   * @return Client
   */
  public function setClientId($client_id)
  {
    $this -> options['client_id'] = $client_id;
    return $this;
  }

  /**
   * Return client secret 
   *  @return String $client_secret
   */
  public function getClientSecret()
  {
    return $this -> options['client_secret'];
  }

  /**
   * Set client secret in options array
   * 
   * @param mixed $secret
   * @return Client
   */
  public function setClientSecret($secret)
  {
    $this -> options['client_secret'] = $secret;
    return $this;
  }

  /**
   * Set access token
   * 
   * @param string $token
   */
  public function setToken($token)
  {
    $this -> token = $token;
  }

  /**
   * Return access token
   *  @return String $token
   */
  public function getToken()
  {
    return $this -> token;
  }

  /**
   * Return operation error
   *  @return  String $error
   */
  public function getError($as_is=FALSE)
  {
    $error = $this -> error;
    //support returning the error as-is
    if ($as_is) { return $error; }

    //otherwise, potentially translate it
    if (is_object($error) && get_class($error) == 'stdClass')
    {
      if (isset($error -> error)) {
       return $error -> error;
      }
      $err = $error->message . ' ';
      if (isset($error -> errors)) {
        foreach ($error -> errors as $key => $value) {
          $err .= $key . ' ' . $value[0] . ' ';
        }
      }
      return trim($err);
    }
    return $error;
  }

  /**
   *  Set error string for operation
   * 
   * @param mixed $error
   * @return Client
   */
  public function setError($error)
  {
    $this -> error = $error;
    return $this;
  }

  /**
   * Return Api's url
   * 
   * @param mixed $options  The Array value including api options
   * @return string
   */
  public function get_api_url($options)
  {
    $options = $this -> set_default_value($options, $this -> options);
    $url = $this -> request_client($options['secure']);
    $url = $url . "://" . $options['host'] . $options['path'] . "/" . $options['api_version'];
    return $url;
  }

  /**
   * Return dashboard api's url
   * @return string
   */
    public function get_dashboard_url()
    {
      $url = $this -> request_client($this -> options['secure']);
      $url = $url . "://" . $this -> options['dashboard_host'];
      return $url;
    }

  /**
   * Return Authorization url
   *  @return string
   */
  public function get_auth_url()
  {
    /*$url = $this->get_dashboard_url($this->options)."/auth";
    $options = array(
        'response_type' => 'code',
        'client_id' => $this->options['client_id'],
        'redirect_uri' => $this->get_current_url(),
        'scope' => 'user_read',
    );
    $url = $this->create_get_url($url, $options);*/
    return $this -> auth -> get_auth_url();
  }

  /**
   * Return protocol string for http
   * 
   * @param mixed $secure
   * @return mixed
   */
  public function request_client($secure=NULL)
  {
    if ($secure === NULL) { $secure = TRUE; }
    if ($secure)  { return 'https'; }
    else          { return 'http'; }
  }

  /**
   * Return api port
   * 
   * @param mixed $secure
   * @return int
   */
  public function port($secure=NULL)
  {
    if ($secure === NULL) { $secure = TRUE; }
    if ($this -> options['port'] != NULL) { return $this -> options['port']; }
    else if ($secure)                     { return 443; }
    else                                  { return 80; }
  }

  /**
   * Get result from curl and process it
   * 
   * @param mixed $config configuration parameter
   *
   * @return Object
   */
  public function raw_request($config)
  {
    $DEBUG = FALSE;
    //$DEBUG = strpos($config['path'], '/users') !== FALSE;
    $url = $this -> request_client($this -> options['secure']);
    $url = $url . "://" . $config['host'] . $config['path'];
    $headers = $this -> default_headers;
    $get_response_headers = FALSE;
    if (array_key_exists('response_headers', $config) && $config['response_headers'])
    {
      $get_response_headers = TRUE;
    }
    if ($DEBUG)
    {
      var_dump('===================== RAW REQUEST =====================');
      var_dump($url);
      var_dump($config['method']);
      var_dump($config['headers']);
      var_dump($config['body']);
      var_dump($get_response_headers);
    }
    //make the request
    $result = $this -> do_request(
      $url, $config['body'], $config['headers'], $config['method'], $get_response_headers
    );
    if ($DEBUG)
    {
      var_dump($result);
      var_dump('==========================================');
    }
    //make our own json of the HTTP status code and message
    if ($get_response_headers)
    {
      $body = '';
      $success = FALSE;
      $append = FALSE;
      $index = 0;
      //parse the headers, essentially just grabbing the first one
      //this loop is here in case we want any others
      foreach( explode("\r\n", $result) as $header )
      {
        //increment our index
        $index++;
        //get the status code and message
        if ($index == 1)
        {
          //split on whitespace
          $fields = preg_split('/\s+/', $header);
          //remove the first element (ie: HTTP/1.1)
          array_shift($fields);
          //shift again to get the HTTP status code
          $code = array_shift($fields);
          //implode the message with spaces
          $msg = implode(' ', $fields);
          //create a dummy array
          $data = array('code' => $code, 'message' => $msg);
          //if its a 'good' status code, just break
          if ($code == '200' || $code == '204')
          {
            //make this as successful
            $success = TRUE;
            break;
          }
        }
        //get the location header, in case anyone wants it
        if (($data['code'] == '301') && (strpos($header,'Location:') !== FALSE))
        {
          //make this as successful
          $success = TRUE;
          $data['location'] = trim(substr($header, 9));
        }
        //once we see an empty header, the body is after, per the HTTP protocol
        $htest = trim($header);
        if (empty($htest)) { $append = TRUE; }
        //build up the body
        if ($append)
        {
          $body .= $header;
        }
      }
      if (!empty($body))
      {
        //try and decode the json body
        $json = json_decode($body);
        //if we got json back add the key/value pairs
        if (!empty($json) && is_object($json) && get_class($json) == 'stdClass')
        {
          //add it to the data array we're building
          foreach ($json as $key => $value)
          {
            $data[$key] = $value;
          }
        }
        //otherise, just add the raw body in case anyone wants it
        else
        {
          $data['body'] = $body;
        }
      }
      //set the result and break so we can just decode it again later
      $result = json_encode($data);
    }

    //decode the result
    $result = json_decode($result);

    //make sure there isn't an error
    if (isset($result->status) && (intval($result->status) >= 400))
    {
      $this -> setError($result);
      return FALSE;
    }

    //return it
    return $result;
  }

  #     #  #######  #        ######   #######  ######   
  #     #  #        #        #     #  #        #     #  
  #     #  #        #        #     #  #        #     #  
  #######  #####    #        ######   #####    ######   
  #     #  #        #        #        #        #   #    
  #     #  #        #        #        #        #    #   
  #     #  #######  #######  #        #######  #     #  


  #######  #     #  #     #   #####   #######  ###  #######  #     #   #####   
  #        #     #  ##    #  #     #     #      #   #     #  ##    #  #     #  
  #        #     #  # #   #  #           #      #   #     #  # #   #  #        
  #####    #     #  #  #  #  #           #      #   #     #  #  #  #   #####   
  #        #     #  #   # #  #           #      #   #     #  #   # #        #  
  #        #     #  #    ##  #     #     #      #   #     #  #    ##  #     #  
  #         #####   #     #   #####      #     ###  #######  #     #   #####   

  /**
   * merge two array's value 
   * if $arr have no element in $default_arr then insert the element from $default_arr
   * 
   * @param mixed $arr
   * @param mixed $default_arr
   * @return array
   */
  public function set_default_value($arr, $default_arr)
  {
    $result = array();
    $result = $default_arr;
    foreach ($arr as $key => $value)
    {
      $result[$key] = $value;
    }
    return $result;
  }

  /**
   * create_get_url
   * Create complete url for GET method with auth parameters
   * @param String $url The base URL for api
   * @param Array $params The parameters to pass to the URL
   * @return string
   */
  public function create_get_url($url,$params)
  {
    if(!empty($params) && $params)
    {
      foreach($params as $param_name => $param_value)
      {
        $arr_params[] = "$param_name=" . urlencode($param_value);
      }
      $str_params = implode('&',$arr_params);
      //$str_params = http_build_query($params);
      $url = trim($url) . '?' . $str_params;
    }
    return $url;
  }

  /**
   * get xrate
   *
   * Gets the xrate - aka current btc exchange rate in US Dollars
   *
   * @throws Exception error
   * @return Array
   */
  public function get_xrate()
  {
    $xrate_config['url'] = 'https://x.g0cn.com/prices';
    $xrate_config['method'] = 'GET';
    //perform the request
    $result = $this -> do_request(
      $xrate_config['url'], '', '', $xrate_config['method']
    );
    //decode the result
    $result = json_decode($result);
    if (isset($result -> error))
    {
      throw new Exception($result -> error_description);
    }
    return $result;
  }

  /**
   * do_request
   *
   * Performs a cUrl request with a url . The useragent of the request is hardcoded
   * as the Google Chrome Browser agent
   *
   * @param String $url The base url to query
   * @param Boolean $params The parameters to pass to the request
   * @param Array $headers curl header
   * @param String $method curl type
   *
   *
   * @return Array
   */
  public function do_request($url, $params=FALSE, $headers, $method="POST", $response_headers=FALSE)
  {
    //NOTE: umm ... this is ALWAYS going to be unsset -- why is this here?
    if (!isset($ch)) { $ch = curl_init(); }

    $opts = array(
      CURLOPT_CONNECTTIMEOUT => 30,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_TIMEOUT        => 60,
    );

    if ($method == "POST")
    {
      $opts[CURLOPT_POST] = 1;
      $opts[CURLOPT_POSTFIELDS] = $params;
    }

    if ($method == "PATCH")
    {
      $opts[CURLOPT_CUSTOMREQUEST] = "PATCH";
      $opts[CURLOPT_POSTFIELDS] = $params;
    }

/*
    if ( isset($_SERVER['HTTP_USER_AGENT']) )
    {
      $opts[CURLOPT_USERAGENT] = $_SERVER['HTTP_USER_AGENT'];
    }
    else
    {
      // Handle the useragent like we are Google Chrome
      $opts[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.X.Y.Z Safari/525.13.';
    }
*/

    $opts[CURLOPT_URL] = $url;
    if ($response_headers)  { $opts[CURLOPT_HEADER] = TRUE; }
    else                    { $opts[CURLOPT_HEADER] = FALSE; }
    $opts[CURLOPT_SSL_VERIFYPEER] = TRUE;

    $curl_header = array();
    if ($headers && count($headers))
    {
      foreach ($headers as $key => $value)
      {
        $curl_header[] = $key.': '.$value;
      }
    }

    if (isset($opts[CURLOPT_HTTPHEADER]))
    {
      $opts[CURLOPT_HTTPHEADER] = array_merge($curl_header, $opts[CURLOPT_HTTPHEADER]);
    }
    else
    {
      $opts[CURLOPT_HTTPHEADER] = $curl_header;
    }

    curl_setopt_array($ch, $opts);

    $result = curl_exec($ch);

    if ($result === FALSE)
    {
      $this -> setError(curl_error($ch));
      curl_close($ch);
      return FALSE;
    }
    curl_close($ch);
    return $result;
  }

  /**
   * get_current_url
   * Returns the Current URL, drop params what is included in default params
   *  @return string
   */
  public function get_current_url()
  {
    if (isset($_SERVER['HTTPS']) &&
          (
            $_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == TRUE
          )
            ||
          (
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
          )
        )
    {
      $protocol = 'https://';
    }
    else
    {
      $protocol = 'http://';
    }
    $currentUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $parts = parse_url($currentUrl);

    $query = '';
    if (!empty($parts['query']))
    {
      //drop known params
      $params = explode('&', $parts['query']);
      $retained_params = array();
      foreach ($params as $param)
      {
        if ($this->should_drop_param($param))
        {
          $retained_params[] = $param;
        }
      }
      if (!empty($retained_params))
      {
        $query = '?'.implode($retained_params, '&');
      }
    }

    //use port if non default
    $port = isset($parts['port']) &&
      (
        ($protocol === 'http://' && $parts['port'] !== 80) ||
        ($protocol === 'https://' && $parts['port'] !== 443)
      ) ? ':' . $parts['port'] : '';

    // rebuild
    return $protocol . $parts['host'] . $port . $parts['path'] . $query;
  }

  /**
   * Return the Array including the params should be removed in options
   * 
   * @param mixed $param
   *
   * @return boolean
   */
  public function should_drop_param($param)
  {
    $drop_params = array('code');
    foreach ( $drop_params as $drop_param )
    {
      if (strpos($param, $drop_param) === 0)
      {
        return FALSE;
      }
    }
    return TRUE;
  }
}

?>