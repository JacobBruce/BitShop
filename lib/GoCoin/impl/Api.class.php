<?php

/**
 * GoCoin Api
 * A PHP-based GoCoin client library with a focus on simplicity and ease of integration
 *
 */

class Api
{
  /**
   * Constructor for the API
   * @param Object $client  instance of client
   */
  public function __construct($client)
  {
    $this -> client         = $client;
    $this -> accounts       = new AccountService($this);
    $this -> invoices       = new InvoiceService($this);
    $this -> merchant       = new MerchantService($this);
    $this -> merchant_users = new MerchantUserService($this);
    $this -> user           = new UserService($this);
  }

  /**
   * Do process request
   *  
   * @param string $route Route string for request
   * @param array $options Array of options
   * 
   */
  public function request($route, $options=NULL)
  {
    //default to an empty array
    if ($options == NULL) { $options = array(); }

    if (!(($route != NULL) && is_string($route)))
    {
      $this -> client -> setError('Api Request: Route was not defined');
      return FALSE;
    }
    if (!$this->client->getToken())
    {
      $this -> client -> setError('Api not ready: Token was not defined');
      return FALSE;
    }

    //temp checks to remove php notices
    if(!isset($options['header']))  { $options['header'] = NULL; }
    if(!isset($options['headers'])) { $options['headers'] = NULL; }
    if(!isset($options['body']))    { $options['body'] = NULL; }
    if(!isset($options['method']))  { $options['method'] = NULL; }

    $headers = $options['header'] ? $options['headers'] : $this -> client -> default_headers;
    $headers['Authorization'] = "Bearer " . $this -> client -> getToken();

    //separated $options & $client_options
    $client_options = $this -> client -> options;

    //tweaked config since method was coming through
    $config = array(
      'host'    => $client_options['host'],
      'path'    => "" . $client_options['path'] . "/" . $client_options['api_version'] . $route,
      'method'  => $options['method'],
      'port'    => $this -> client -> port($client_options['port']),
      'headers' => $headers,
      'body'    => $options['body'],
    );

    //pass through the flag to return the response headers
    if (array_key_exists('response_headers', $options))
    {
      $config['response_headers'] = $options['response_headers'];
    }

    return $this -> client -> raw_request($config);
  }
}

?>