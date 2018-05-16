<?php

namespace PrepaidCard;

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
   *
   * @throws Exception
   */
  public function approveAndEarmark() {
    // Check if user has enough money available on card to pay for amount on authorization request.
    // Assumption: it's enough to check if the available balance is no greater than the amount that has been authorized.
    if ($this->card->availableBalance() > $this->authorizationRequest->getAuthorizedAmount()) {
      // Approve request.
      $this->authorizationRequest->approve();
      // Earmark the amount in the authorization request on the card.
      $this->card->earmark($this->authorizationRequest);
    } else {
      throw new Exception("User does not have enough money available on their card.");
    }
  }

  /**
   * Assumption: the request has already been approved when we reverse it.
   *
   * @param float $amount
   *   (optional) amount to reverse. (again we assume valid inputs, i.e. amount <= transaction amount)
   */
  public function reverse(?float $amount = NULL) {
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
    // TODO: Find out what "the merchant can capture the amount multiple times" and "the merchant can’t capture more than we initially authorized him to." mean.
    // I read this as the merchant being able to capture bits and pieces of the amount in the authorization request, but no more.
    $this->authorizationRequest->markAsCaptured($amount);
    $this->card->loseMoney($amount);
    $this->authorizationRequest->sendToMerchant($amount);
    define(EPSILON, 0.00001);
    // Cleans up the relevant authorization request if it's zero.
    if ($this->authorizationRequest->getAuthorizedAmount() < EPSILON) {
      $this->card->removeEarmarked($this->authorizationRequest);
    }

  }
}
