<?php

/**
 * Merchant User Service Class
 *
 */

class MerchantUserService
{
  private $api;

  public function  __construct($api)
  {
    $this -> api = $api;
  }

  public function getMerchantUsers($merchant_id)
  {
    $route = '/merchants/' . $merchant_id . '/users';
    return $this -> api -> request($route);
  }

  public function addMerchantUser($merchant_id,$user_id)
  {
    $route = '/merchants/' . $merchant_id . '/users/' . $user_id;
    $options = array(
      'method' => 'PUT',
      'response_headers' => TRUE,
    );
    return $this -> api -> request($route, $options);
  }

  public function deleteMerchantUser($merchant_id,$user_id)
  {
    $route = '/merchants/' . $merchant_id . '/users/' . $user_id;
    $options = array(
      'method' => 'DELETE',
      'response_headers' => TRUE,
    );
    return $this -> api -> request($route, $options);
  }
}

?>