<?php

namespace PrepaidCard;

/**
 * Models an authorization request (we assume this is the same as a "transaction" for purposes of the coding test)
 */
class AuthorizationRequest {

  /**
   * @var int
   */
  protected $id;

  /**
   * @var bool
   *   Whether the authorization request has been approved by the card issuer.
   */
  protected $approved;

  /**
   * @var Merchant
   */
  protected $merchant;

  /**
   * @var PrepaidCard
   */
  protected $card;

  /**
   * @var float
   */
  protected $originalAmount;

  /**
   * @var float
   *   Amount that has been captured.
   */
  protected $amountCaptured;

  /**
   * @var float
   */
  protected $amountReversed;

  /**
   * AuthorizationRequest constructor.
   *
   * @param float $amount
   * @param int $id
   * @param PrepaidCard $card
   * @param Merchant $merchant
   */
  public function __construct(float $amount, int $id, PrepaidCard $card, Merchant $merchant) {
    $this->id = $id;
    $this->amountReversed = 0.00;
    $this->amountCaptured = 0.00;
    $this->originalAmount = $amount;
    $this->card = $card;
    $this->merchant = $merchant;
    // Assumption: all authorization requests are unapproved on object creation.
    $this->approved = FALSE;
  }

  /**
   * @param $amount
   */
  public function reverse($amount) {
    $this->amountReversed += $amount;
    // TODO: perform the actual reversal
  }

  /**
   * Approves the authorization request.
   */
  public function approve() {
    $this->approved = TRUE;
  }

  /**
   * @return bool
   *   Whether the authorization request has been approved.
   */
  public function isApproved() {
    return $this->approved;
  }

  /**
   * @return float
   *   The amount that has been authorized for capture.
   */
  public function getAuthorizedAmount(): float {
    return $this->originalAmount - $this->amountReversed - $this->amountCaptured;
  }

  /**
   * @return int
   *   The ID.
   */
  public function getId(): int {
    return $this->id;
  }

  /**
   * Marks the given amount as captured.
   *
   * @param float $amount
   *   Amount to capture.
   */
  public function markAsCaptured(float $amount) {
    $this->amountCaptured += $amount;
  }

  /**
   * @return int
   */
  public function getMerchantId(): int {
    return $this->merchant->id();
  }

  /**
   * Sends the given amount to a merchant.
   *
   * @param float $amount
   */
  public function sendToMerchant(float $amount) {
    $this->merchant->receive($amount);
  }
}
