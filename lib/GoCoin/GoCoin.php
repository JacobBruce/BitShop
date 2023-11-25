<?php
if ( !defined('__DIR__') ) define('__DIR__', dirname(__FILE__));
/**
 * GoCoin Api
 * A PHP-based GoCoin client library with a focus on simplicity and ease of integration
 */

class GoCoin
{
  const VERSION = '0.7';

  //production hostnames
  const PRODUCTION_HOST = "api.gocoin.com";
  const PRODUCTION_DASHBOARD_HOST = "dashboard.gocoin.com";

  const TEST_HOST = "";
  const TEST_DASHBOARD_HOST = "";

  static private $API_MODE = 'production';

  /**
   * @return the version of this client library
   */
  static public function getVersion()
  {
    return GoCoin::VERSION;
  }

  /**
   * @return the api mode
   */
  static public function getApiMode()
  {
    return GoCoin::$API_MODE;
  }

  /**
   * @return set the api mode
   */
  static public function setApiMode($mode='production')
  {
    GoCoin::$API_MODE = $mode;
    return GoCoin::getApiMode();
  }

  /**
   * @return a Client object
   */
  static public function getClient($token)
  {
    $client = Client::getInstance($token);
    if (GoCoin::getApiMode() == 'test')
    {
      $client -> options['host'] = GoCoin::TEST_HOST;
      $client -> options['dashboard_host'] = GoCoin::TEST_DASHBOARD_HOST;
    }
    return $client;
  }

  /**
   * @return a url to redirect to request an authorization code, used later for requesting a token
   */
  static public function requestAuthorizationCode(
    $client_id, $client_secret, $scope, $redirect_uri=NULL
  )
  {
    //create a new client without having a token
    $client = new Client(
      array(
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'scope' => $scope,
        'redirect_uri' => $redirect_uri,  //NOTE: leave this out to use the current URL
      )
    );
    return $client -> get_auth_url();
  }

  /**
   * @return an access token given a previously requested authorization code
   */
  static public function requestAccessToken(
    $client_id, $client_secret, $code, $redirect_uri=NULL
  )
  {
    //create a new client without having a token
    $client = new Client(
      array(
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,  //NOTE: leave this out to use the current URL
      )
    );
    //reset/init any token, just in case
    $client -> initToken();
    //authorize the api, ie: get a token
    if (!$client -> authorize_api($code))
    {
      throw new Exception($client -> getError());
    }
    return $client -> getToken();
  }

  /**
   * @return the exchange rates
   */
  static public function getExchangeRates()
  {
    return Client::getInstance() -> get_xrate();
  }

  #     #   #####   #######  ######   
  #     #  #     #  #        #     #  
  #     #  #        #        #     #  
  #     #   #####   #####    ######   
  #     #        #  #        #   #    
  #     #  #     #  #        #    #   
   #####    #####   #######  #     #  

  /**
   * @return a user given an id, or, the current user if id is not provided
   */
  static public function getUser($token,$id=NULL)
  {
    $client = GoCoin::getClient($token);
    $user = NULL;
    if (empty($id))
    {
      $user = $client -> api -> user -> self();
    }
    else
    {
      $user = $client -> api -> user -> get($id);
    }
    if (!$user) { throw new Exception($client -> getError()); }
    else        { return $user; }
  }

  /**
   * @return an updated user, after applying updates
   */
  static public function updateUser($token,$user)
  {
    $client = GoCoin::getClient($token);
    $user = $client -> api -> user -> updateUser($user);
    if (!$user) { throw new Exception($client -> getError()); }
    else        { return $user; }
  }

  /**
   * @return the user appplications
   */
  static public function getUserApplications($token,$id)
  {
    $client = GoCoin::getClient($token);
    $apps = $client -> api -> user -> getUserApplications($id);
    if (!$apps) { throw new Exception($client -> getError()); }
    else        { return $apps; }
  }

  /**
   * @return a boolean if the password was successfully updated
   */
  static public function updatePassword($token,$id,$password_array)
  {
    $client = GoCoin::getClient($token);
    $result = $client -> api -> user -> updatePassword($id,$password_array);
    if (!$result) { throw new Exception($client -> getError()); }
    else          { return $result; }
  }

  /**
   * @return a boolean if the reset password request went through
   */
  static public function resetPassword($token,$email)
  {
    $client = GoCoin::getClient($token);
    $result = $client -> api -> user -> resetPassword($email);
    if (!$result) { throw new Exception($client -> getError()); }
    else          { return $result; }
  }

  /**
   * @return a boolean if the reset password with token request went through
   */
  static public function resetPasswordWithToken($token,$id,$reset_token,$password_array)
  {
    $client = GoCoin::getClient($token);
    $result = $client -> api -> user -> resetPasswordWithToken($id,$reset_token,$password_array);
    if (!$result) { throw new Exception($client -> getError()); }
    else          { return $result; }
  }

  /**
   * @return a boolean if the request confirmation email request went through
   */
  static public function requestConfirmationEmail($token,$email)
  {
    $client = GoCoin::getClient($token);
    $result = $client -> api -> user -> requestConfirmationEmail($email);
    if (!$result) { throw new Exception($client -> getError()); }
    else          { return $result; }
  }

  /**
   * @return a boolean if the user confirm was successful
   */
  static public function confirmUser($token,$id,$confirm_token)
  {
    $client = GoCoin::getClient($token);
    $result = $client -> api -> user -> confirmUser($id,$confirm_token);
    if (!$result) { throw new Exception($client -> getError()); }
    else          { return $result; }
  }

  #     #  #######  ######    #####   #     #     #     #     #  #######  
  ##   ##  #        #     #  #     #  #     #    # #    ##    #     #     
  # # # #  #        #     #  #        #     #   #   #   # #   #     #     
  #  #  #  #####    ######   #        #######  #     #  #  #  #     #     
  #     #  #        #   #    #        #     #  #######  #   # #     #     
  #     #  #        #    #   #     #  #     #  #     #  #    ##     #     
  #     #  #######  #     #   #####   #     #  #     #  #     #     #     

  /**
   * @return a merchant given an id
   */
  static public function getMerchant($token,$id)
  {
    $client = GoCoin::getClient($token);
    $merchant = $client -> api -> merchant -> get($id);
    if (!$merchant) { throw new Exception($client -> getError()); }
    else            { return $merchant; }
  }

  /**
   * @return an updated merchant, after applying updates
   */
  static public function updateMerchant($token,$merchant)
  {
    $client = GoCoin::getClient($token);
    $merchant = $client -> api -> merchant -> updateMerchant($merchant);
    if (!$merchant) { throw new Exception($client -> getError()); }
    else            { return $merchant; }
  }

  /**
   * @return the result of requesting a payout
   */
  static public function requestPayout($token,$merchant_id,$amount,$currency='BTC')
  {
    $client = GoCoin::getClient($token);
    $payout = $client -> api -> merchant -> requestPayout($merchant_id,$amount,$currency);
    if ($payout === FALSE) { throw new Exception($client -> getError()); }
    else                   { return $payout; }
  }

  /**
   * @return a list of merchant payouts given a payout id, or, all payouts if id is not provided
   */
  static public function getMerchantPayouts($token,$merchant_id,$payout_id=NULL)
  {
    $client = GoCoin::getClient($token);
    $payouts = $client -> api -> merchant -> getMerchantPayouts($merchant_id,$payout_id);
    if ($payouts === FALSE) { throw new Exception($client -> getError()); }
    else                    { return $payouts; }
  }

  /**
   * @return the result of requesting a currency conversion
   */
  static public function requestCurrencyConversion(
    $token,$merchant_id,$amount,$currency='BTC',$target='USD'
  )
  {
    $client = GoCoin::getClient($token);
    $conversion = $client -> api -> merchant -> requestCurrencyConversion(
      $merchant_id,$amount,$currency,$target
    );
    if ($conversion === FALSE)  { throw new Exception($client -> getError()); }
    else                        { return $conversion; }
  }

  /**
   * @return a list of currency conversions given a conversion id, or, all conversions if id is not provided
   */
  static public function getCurrencyConversions($token,$merchant_id,$conversion_id=NULL)
  {
    $client = GoCoin::getClient($token);
    $conversions = $client -> api -> merchant -> getCurrencyConversions($merchant_id,$conversion_id);
    if ($conversions === FALSE) { throw new Exception($client -> getError()); }
    else                        { return $conversions; }
  }

  #     #  #######  ######    #####   #     #     #     #     #  #######  
  ##   ##  #        #     #  #     #  #     #    # #    ##    #     #     
  # # # #  #        #     #  #        #     #   #   #   # #   #     #     
  #  #  #  #####    ######   #        #######  #     #  #  #  #     #     
  #     #  #        #   #    #        #     #  #######  #   # #     #     
  #     #  #        #    #   #     #  #     #  #     #  #    ##     #     
  #     #  #######  #     #   #####   #     #  #     #  #     #     #     


  #     #   #####   #######  ######    #####   
  #     #  #     #  #        #     #  #     #  
  #     #  #        #        #     #  #        
  #     #   #####   #####    ######    #####   
  #     #        #  #        #   #          #  
  #     #  #     #  #        #    #   #     #  
   #####    #####   #######  #     #   #####   

  /**
   * @return a list of merchant users given a merchant id
   */
  static public function getMerchantUsers($token,$merchant_id)
  {
    $client = GoCoin::getClient($token);
    $users = $client -> api -> merchant_users -> getMerchantUsers($merchant_id);
    if ($users === FALSE) { throw new Exception($client -> getError()); }
    else                  { return $users; }
  }

  ###  #     #  #     #  #######  ###   #####   #######   #####   
   #   ##    #  #     #  #     #   #   #     #  #        #     #  
   #   # #   #  #     #  #     #   #   #        #        #        
   #   #  #  #  #     #  #     #   #   #        #####     #####   
   #   #   # #   #   #   #     #   #   #        #              #  
   #   #    ##    # #    #     #   #   #     #  #        #     #  
  ###  #     #     #     #######  ###   #####   #######   #####   

  /**
   * @return the result of creating an invoice
   */
  static public function createInvoice($token,$merchant_id,$invoice)
  {
    $client = GoCoin::getClient($token);
    $invoice = $client -> api -> invoices -> createInvoice($merchant_id,$invoice);
    if ($invoice === FALSE) { throw new Exception($client -> getError()); }
    else                    { return $invoice; }
  }

  /**
   * @return an invoice by id
   */
  static public function getInvoice($token,$id)
  {
    $client = GoCoin::getClient($token);
    $invoice = $client -> api -> invoices -> getInvoice($id);
    if ($invoice === FALSE) { throw new Exception($client -> getError()); }
    else                    { return $invoice; }
  }

  /**
   * @return the invoices that match the given criteria
   */
  static public function searchInvoices($token,$criteria=NULL)
  {
    $client = GoCoin::getClient($token);
    $invoices = $client -> api -> invoices -> searchInvoices($criteria);
    if ($invoices === FALSE)  { throw new Exception($client -> getError()); }
    else                      { return $invoices; }
  }

     #      #####    #####   #######  #     #  #     #  #######   #####   
    # #    #     #  #     #  #     #  #     #  ##    #     #     #     #  
   #   #   #        #        #     #  #     #  # #   #     #     #        
  #     #  #        #        #     #  #     #  #  #  #     #      #####   
  #######  #        #        #     #  #     #  #   # #     #           #  
  #     #  #     #  #     #  #     #  #     #  #    ##     #     #     #  
  #     #   #####    #####   #######   #####   #     #     #      #####   

  /**
   * @return the accounts for a given merchant
   */
  static public function getAccounts($token,$merchant_id)
  {
    $client = GoCoin::getClient($token);
    $accounts = $client -> api -> accounts -> getAccounts($merchant_id);
    if ($accounts === FALSE)  { throw new Exception($client -> getError()); }
    else                      { return $accounts; }
  }

  /**
   * @return the transactions that match the given criteria
   */
  static public function getAccountTransactions($token,$account_id,$criteria=NULL)
  {
    $client = GoCoin::getClient($token);
    $xactions = $client -> api -> accounts -> getAccountTransactions($account_id,$criteria);
    if ($xactions === FALSE)  { throw new Exception($client -> getError()); }
    else                      { return $xactions; }
  }

}

   #     #     #  #######  #######  #        #######     #     ######   
  # #    #     #     #     #     #  #        #     #    # #    #     #  
 #   #   #     #     #     #     #  #        #     #   #   #   #     #  
#     #  #     #     #     #     #  #        #     #  #     #  #     #  
#######  #     #     #     #     #  #        #     #  #######  #     #  
#     #  #     #     #     #     #  #        #     #  #     #  #     #  
#     #   #####      #     #######  #######  #######  #     #  ######   

/**
 * SPL autoloader.
 * @param string $class_name the name of the class to load
 */
function GoCoinAutoload($class_name)
{
  $dirs = array(
    __DIR__ . '/',
    __DIR__ . '/services/',
    __DIR__ . '/impl/',
  );
  //support case insensitivity
  $class_names = array($class_name,strtolower($class_name));
  foreach($class_names as $class_name)
  {
    foreach($dirs as $dir)
    {
      $file = $dir . $class_name . '.php';
      if (file_exists($file)) {
        include_once($file);
        return;
      }
      if (strpos($class_name, "_")) {
        $file = $dir . str_replace("_", "/", $class_name) . ".php";
        if (file_exists($file)) {
          include_once($file);
          return;
        }
      }
      $file = $dir . $class_name . '.class.php';
      if (file_exists($file)) {
        include_once($file);
        return;
      }
    }
  }
  //die("[ERROR]: class [$class_name] cannot be auto loaded!");
}

if (version_compare(PHP_VERSION, '5.3.0', '>='))
{
  spl_autoload_register('GoCoinAutoload', TRUE, TRUE);
}
else
{
  spl_autoload_register('GoCoinAutoload');
}


?>
