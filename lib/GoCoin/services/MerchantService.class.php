<?php

/**
 * Merchant Class
 *
 */

class MerchantService
{
  private $api;

  public function __construct($api)
  {
    $this -> api = $api;
  }

  public function createMerchant($merchant)
  {
    $route = '/merchants';
    $options = array(
      'method' => 'POST',
      'body' =>  json_encode($merchant)
    );
    return $this -> api -> request($route, $options);
  }

  public function deleteMerchant($id)
  {
    $route = "/merchants/" . $id;
    $options = array(
      'method' => 'DELETE'
    );
    return $this -> api -> request($route, $options);
  }

  public function get($id)
  {
    $route = "/merchants/" . $id;
    return $this -> api -> request($route);
  }

  public function listMerchants()
  {
    $route = '/merchants';
/*
    $options = array(
      'method' => 'GET',
      'body' => NULL,
      'response_headers' => FALSE,
    );
    return $this -> api -> request($route, $options);
*/
    return $this -> api -> request($route);
  }

  public function updateMerchant($merchant)
  {
    if (!is_array($merchant)) { throw new Exception("Invalid merchant object!"); }
    if (!array_key_exists('id',$merchant)) { throw new Exception("Invalid merchant object: missing id!"); }
    $id = $merchant['id'];
    $route = "/merchants/" . $id;
    $options = array(
      'method' => 'PATCH',
      'body' => json_encode($merchant),
    );
    return $this -> api -> request($route, $options);
  }

  public function requestPayout($merchant_id,$amount,$currency='BTC')
  {
    $route = '/merchants/' . $merchant_id . '/payouts';
    $data = array(
      'currency_code' => $currency,
      'amount' => $amount,
    );
    $options = array(
      'method' => 'POST',
      'body' =>  json_encode($data),
      'response_headers' => FALSE,
    );
    return $this -> api -> request($route, $options);
  }

  public function getMerchantPayouts($merchant_id,$payout_id=NULL)
  {
    $route = '/merchants/' . $merchant_id . '/payouts';
    if (!empty($payout_id)) { $route .= "/$payout_id"; }
    return $this -> api -> request($route);
  }

  public function requestCurrencyConversion($merchant_id,$amount,$currency='BTC',$target='USD')
  {
    $route = '/merchants/' . $merchant_id . '/currency_conversions';
    $data = array(
      'base_currency' => $currency,
      'base_currency_amount' => $amount,
      'final_currency' => $target,
    );
    $options = array(
      'method' => 'POST',
      'body' =>  json_encode($data),
      'response_headers' => FALSE,
    );
    return $this -> api -> request($route, $options);
  }

  public function getCurrencyConversions($merchant_id,$conversion_id=NULL)
  {
    $route = '/merchants/' . $merchant_id . '/currency_conversions';
    if (!empty($conversion_id)) { $route .= "/$conversion_id"; }
    return $this -> api -> request($route);
  }
}
?>
