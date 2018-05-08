<?php

/**
 * Assumptions:
 *
 * - It's OK to use floats for GBP amounts (the consumer of this API can round them all to 2 digits, which we assume to be OK as well)
 * - We assume a user can hold multiple cards.
 * - We assume valid inputs.
 * - For purposes of the coding test it's OK to chock all the classes into one big index.php file.
 * - We don't bother modelling actual Authorizations or Transactions (Authorization Requests will serve that role)
 * - It's OK not to persist any data if the PHP process crashes (no database).
 * - It's OK not to perform any logging.
 * - The only information we're concerned about for the merchants and the card owners is their ID.
 * - All IDs are unique integers.
 * - The card issuer approves authorization requests.
 * - We are the card issuer.
 * - Sending the money to the merchant after a transaction is captured happens outside of this system (we don't know
 *   the amount of money sent to each merchant).
 * - We don't bother modelling the merchant side of a refund (only receiving a refund).
 * - Reversing (part of) an authorization request/transaction can only happen after it has been approved.
 * - We assume "The merchant can capture the amount multiple times" means that the merchant can choose to capture part
 *   of the amount multiple times, but this cannot total more than the initally authorized amount.
 * - Amounts remain earmarked infinitely if they are not captured.
 * - We're not worried about the data store being down and the IDs that are passed in have been checked to be unique.
 *
 * TODO (and assumed to be out of scope for this coding test):
 * - create interfaces
 * - split out into separate files per PSR, add autoloading
 * - add input validation
 * - further unit test coverage
 */

/**
 * Models a prepaid card.
 *
 * Assumption: one card always has one owner.
 */
class PrepaidCard {

  /**
   * @var int
   *   ID for this card
   */
  protected $cardId;

  /**
   * @var AuthorizationRequest[]
   *   All the authorization requests that have been approved (the amount in them is automatically blocked/earmarked on the card.)
   *   Keyed by the authorization request ID.
   */
  protected $earmarkedAuthorizationRequests = [];

  /**
   * @var bool
   *   Whether the card is active.
   */
  protected $active;

  /**
   * @var int
   *   ID of the person who owns the card. (Assumption: this is only one person.)
   */
  protected $ownerId;

  /**
   * @var float
   *   Amount loaded on this card (minus what is captured by merchants). (Assumption: this is a positive number.)
   */
  protected $amountLoaded;

  /**
   * @var float
   *   Amount refunded on this card. (Assumption: this is a positive number.)
   */
  protected $amountRefunded;

  /**
   * PrepaidCard constructor.
   *
   * @param int $ownerId
   * @param int $cardId
   */
  public function __construct(int $ownerId, int $cardId) {
    $this->cardId = $cardId;
    $this->ownerId = $ownerId;
    // Assumption: card is always inactive when a card object is created.
    $this->active = FALSE;
    // Assumption: amount loaded is always 0 when card object is created.
    $this->amountLoaded = 0.00;
    $this->amountRefunded = 0.00;
  }

  /**
   * @return bool
   *   Whether the card is active.
   */
  public function isActive(): boolean {
    return $this->active;
  }

  /**
   * @return float
   *   Amount loaded on this card (Assumption: this is a positive number.)
   */
  public function amountLoaded(): float {
    return $this->amountLoaded;
  }

  /**
   * Assumption: we can calculate this by subtracting the amount earmarked/blocked from the amount loaded.
   *
   * @return float
   *   Balance available on this card.
   */
  public function availableBalance(): float {
    return $this->amountLoaded() - $this->amountBlocked() - $this->amountRefunded;
  }

  /**
   * @return float
   *   Amount blocked/earmarked on this card.
   */
  public function amountBlocked(): float {
    $amount = 0.0;
    // Loop through earmarked (approved) authorization requests, sum amount available for capture on each of them.
    foreach ($this->earmarkedAuthorizationRequests as $request) {
      // Assumption: it's OK for this to be slow / uncached.
      $amount += $request->getAuthorizedAmount();
    }
    return $amount;
  }

  /**
   * Loads money on the card.
   *
   * Assumptions:
   * - this function is safe to access and we've already done the necessary checks
   *   to ensure that money can actually be loaded on this card.
   * - Loading money onto a card activates it.
   *
   * @param float $amount
   *   Amount to load on the card (Assumption: we have already checked that this is a positive number.)
   */
  public function loadMoney(float $amount) {
    // Assumption: loading any positive amount on this card activates it.
    $this->active = TRUE;
    $this->amountLoaded += $amount;
  }

  /**
   * Marks an authorization request as earmarked for this card.
   *
   * TODO: split this out into its own service
   *
   * @param AuthorizationRequest $authorizationRequest
   */
  public function earmark(AuthorizationRequest $authorizationRequest) {
    $this->earmarkedAuthorizationRequests[$authorizationRequest->getId()] = $authorizationRequest;
  }

  /**
   * Remove an authorization request (such as when it has been fully captured.)
   *
   * @param AuthorizationRequest $authorizationRequest
   */
  public function removeEarmarked(AuthorizationRequest $authorizationRequest) {
    unset($this->earmarkedAuthorizationRequests[$authorizationRequest->getId()]);
  }

  /**
   * TODO: split this out into its own service
   *
   * @param float $amount
   */
  public function receiveRefund(float $amount) {
    // Assumption: we don't care about the merchant part of this transaction (we assume it's a different system)
    $this->amountRefunded += $amount;
  }

  /**
   * Assumption: this is called whenever a merchant captures an amount.
   *
   * @param float $amount
   */
  public function capture(float $amount) {
    $this->amountLoaded -= $amount;
  }
}


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
   * @var int
   */
  protected $merchantId;

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
   * @param int $merchantId
   */
  public function __construct(float $amount, int $id, PrepaidCard $card, int $merchantId) {
    $this->id = $id;
    $this->amountReversed = 0.00;
    $this->amountCaptured = 0.00;
    $this->originalAmount = $amount;
    $this->card = $card;
    $this->merchantId = $merchantId;
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
   * Captures the given amount.
   *
   * @param float $amount
   *   Amount to capture.
   */
  public function capture(float $amount) {
    $this->amountCaptured += $amount;
  }

  /**
   * @return int
   */
  public function getMerchantId(): int {
    return $this->merchantId;
  }
}


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


/**
 * Models data storage.
 *
 * TODO: finalize
 *
 * Assumption: we're not worried about integer IDs overflowing,
 *             and there is protection against these unique ID generators being called too often.
 */
class DataStore {

  /**
   * @var array
   *   Card owners
   */
  protected $owners = [];

  /**
   * @var array
   *   Cards
   */
  protected $cards = [];

  /**
   * @var array
   *   Merchants
   */
  protected $merchants = [];

  /**
   * @var array
   *   Multidimensional array initially keyed by cardId, which contains a keyed array or Authorization requests (also keyed by ID)
   */
  protected $authorizationRequests = [];


  /**
   * @var int
   */
  protected $nextOwnerId = 1;

  /**
   * @var int
   */
  protected $nextCardId = 1;

  /**
   * @var int
   */
  protected $nextMerchantId = 1;

  /**
   * @var int
   */
  protected $nextAuthorizationRequestId = 1;

  /**
   * @return int
   *   Unique owner ID.
   */
  public function getNextOwnerId() {
    $this->nextOwnerId += 1;
    return $this->nextOwnerId;
  }

  /**
   * @return int
   *   Unique card ID.
   */
  public function getNextCardId() {
    $this->nextCardId += 1;
    return $this->nextCardId;
  }

  /**
   * @return int
   *   Unique merchant ID.
   */
  public function getNextMerchantId() {
    $this->nextMerchantId += 1;
    return $this->nextMerchantId;
  }

  /**
   * @return int
   *   Unique owner ID.
   */
  public function getNextAuthorizationRequestId() {
    $this->nextAuthorizationRequestId += 1;
    return $this->nextAuthorizationRequestId;
  }

}
