<?php

/**
 * Approves/earmarks valid authorization requests and allows for capturing and reversing authorizations.
 */
class AuthorizationRequestHandler {

  /**
   * @var AuthorizationRequest
   */
  protected $authorizationRequest;

  /**
   * @var PrepaidCard
   */
  protected $card;

  /**
   * AuthorizationRequestHandler constructor.
   *
   * @param AuthorizationRequest $authorizationRequest
   * @param PrepaidCard $card
   */
  public function __construct(AuthorizationRequest $authorizationRequest, PrepaidCard $card) {
    $this->authorizationRequest = $authorizationRequest;
    $this->card = $card;
  }

  /**
   * Handles an authorization request (checks if user has enough money, then approves and earmarks the amount).
   */
  public function handle() {
    // Check if user has enough money available on card to pay for amount on authorization request.
    // Assumption: it's enough to check if the available balance is no greater than the amount that has been authorized.
    if ($this->card->availableBalance() > $this->authorizationRequest->getAuthorizedAmount()) {
      // Approve request.
      $this->authorizationRequest->approve();
      // Earmark the amount in the authorization request on the card.
      $this->card->earmark($this->authorizationRequest);
    } else {
      // TODO
    }
  }

  /**
   * Assumption: the request has already been approved when we reverse it.
   *
   * @param int $amount
   *   (optional) amount to reverse. (again we assume valid inputs, i.e. amount <= transaction amount)
   */
  public function reverse(?int $amount = NULL) {
    if (!isset($amount)) {
      // Reverse full amount.
      $this->authorizationRequest->reverse($this->authorizationRequest->getAuthorizedAmount());
    } else {
      // Reverse in part only.
      $this->authorizationRequest->reverse($amount);
    }
  }

  /**
   * Captures the given amount and sends the money to the merchant.
   *
   * Assumption: we checked that the authorization request has been approved and that the given number has been authorized.
   *
   * @param float $amount
   *   Amount to capture.
   */
  public function capture(float $amount) {
    $merchantId = $this->authorizationRequest->getMerchantId();
    $this->authorizationRequest->capture($amount);
    $this->card->capture($amount);
    // TODO: send money to merchant
    echo("Sending $amount to merchant #$merchantId...");
    // Clean up if necessary.
    // if smaller than 1 cent, consider it to be zero.
    define(EPSILON, 0.01);
    if ($this->authorizationRequest->getAuthorizedAmount() < EPSILON) {
      $this->card->removeEarmarked($this->authorizationRequest);
    }

  }
}
