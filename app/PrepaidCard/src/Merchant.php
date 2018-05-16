<?php

/**
 * Models a merchant.
 */
class Merchant
{

  /**
   * @var int
   *   ID of this merchant.
   */
  protected $id;

  /**
   * @var float
   *   Balance owned this merchant.
   */
  protected $balance;

  public function __construct(int $id, float $balance) {
    $this->id = $id;
    $this->balance = $balance;
  }

  /**
   * @return int
   *   The ID of this merchant.
   */
  public function id() {
    return $this->id;
  }

  /**
   * @param $amount
   *   The amount to receive.
   */
  public function receive($amount) {
    $this->balance += amount;
  }
}