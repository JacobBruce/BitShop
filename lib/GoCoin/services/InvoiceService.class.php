<?php

/**
 * Invoice Class
 *
 */

class InvoiceService
{
  private $api;

  public function __construct($api)
  {
    $this -> api = $api;
  }

  public function createInvoice($merchant_id,$invoice)
  {
    $route = "/merchants/" . $merchant_id . "/invoices";
    $options = array(
      'method' => 'POST',
      'body' => json_encode($invoice)
    );
    return $this -> api -> request($route, $options);
  }

  public function getInvoice($id)
  {
    $route = "/invoices/" . $id;
    return $this -> api -> request($route);
  }

  public function searchInvoices($criteria=NULL)
  {
    $route = "/invoices/search";
    if (!empty($criteria))
    {
      $route .= '?' . http_build_query($criteria);
    }
    return $this -> api -> request($route);
  }
}
?>