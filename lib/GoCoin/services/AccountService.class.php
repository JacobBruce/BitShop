<?php

/**
 * Account Class
 *
 */

class AccountService
{
  private $api;

  public function  __construct($api)
  {
    $this -> api = $api;
  }

  public function getAccounts($merchant_id)
  {
    $route = '/merchants/' . $merchant_id . '/accounts';
    return $this -> api -> request($route);
  }

  public function getAccountTransactions($account_id,$criteria=NULL)
  {
    $route = "/accounts/$account_id/transactions";
    if (!empty($criteria))
    {
      $route .= '?' . http_build_query($criteria);
    }
    return $this -> api -> request($route);
  }
}

?>