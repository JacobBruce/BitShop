<?php

/**
 * GoCoin Api  Auth class
 * include functions related with authentication
 *
 */

class Auth
{
  /**
   * constant value for required params of password authentication
   * 
   * @var array
   */
  private $required_password_params = array(
    'grant_type',
    'client_id',
    'client_secret',
    'username',
    'password',
    'scope',
  );

  /**
   * constant value for required params of code authentication
   * 
   * @var array
   */
  private $required_code_params = array(
    'grant_type',
    'client_id',
    'client_secret',
    'code',
    'redirect_uri',
  );

  /**
   * Constructor
   *
   * @param mixed $client
   * @return Auth
   */
  public function __construct($client)
  {
    $this -> client = $client;
  }

  /**
   * Return Authorization url to get auth_code 
   *
   * @return string 
   */
  public function get_auth_url()
  {
    $url = $this->client->get_dashboard_url()."/auth";
    $options = array(
      'response_type' => 'code',
      'client_id'     => $this -> client -> options['client_id'],
      'scope'         => $this -> client -> options['scope'],
    );
    if (array_key_exists('redirect_uri',$this -> client -> options) && !empty($this -> client -> options['redirect_uri']))
    {
      $options ['redirect_uri'] = $this -> client -> options['redirect_uri'];
    }
    else
    {
      $options['redirect_uri'] = $this -> client -> get_current_url();
    }
    $url = $this -> client -> create_get_url($url, $options);
    return $url;
  }

  /**
   * do process authorization
   * 
   * @param array $options  Authorization options
   */
  public function authenticate($options)
  {
    $required = array();
    if ($options['grant_type'] == 'password')
    {
      $required = $this -> required_password_params;
    }
    elseif ($options['grant_type'] == 'authorization_code')
    {
      $required = $this -> required_code_params;
    }
    else
    {
      $this -> client -> setError("Authenticate: grant_type was not defined properly");
      return FALSE;
    }

    $headers = $this -> client -> default_headers;
    if ($options['headers'] != NULL) { $headers = $options['headers']; }

    $body = $this -> build_body($options, $required);

    if ($body == FALSE) { return FALSE; }

    //json encode the body for an auth request
    $body = json_encode($body);

    $config = array(
      'host'    => $options['host'],
      'path'    => $options['path']. "/". $options['api_version'] . "/oauth/token",
      'method'  => "POST",
      'port'    => $this -> client -> port($options['secure']),
      'headers' => $headers,
      'body'    => $body
    );

    return $this -> client -> raw_request($config);
  }

  /**
   * filter the options array according to the required options array
   * 
   * @param array $options
   * @param array $required
   * @return Array result
   */
  public function build_body($options, $required)
  {
    $result = array();
    foreach ($required as $k)
    {
      if (!$options[$k])
      {
        $this -> client -> setError("Authenticate: $k was not defined properly");
        return FALSE;
      }
      $result[$k] = $options[$k];
    }
    return $result;
  }
}
?>