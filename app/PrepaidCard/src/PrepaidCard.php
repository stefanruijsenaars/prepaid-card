<?php

namespace PrepaidCard;

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
    // Assumption: amount loaded is always 0 when card object is created.
    $this->amountLoaded = 0.00;
    $this->amountRefunded = 0.00;
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
  public function loseMoney(float $amount) {
    $this->amountLoaded -= $amount;
  }

  /**
   * Loads a card object from the data store.
   *
   * @param $cardId
   * @throws \Exception
   * @return PrepaidCard|false
   */
  public static function load($cardId) {
    if ($cardId == 1234) {
      throw new \Exception("card not found");
    }
    return new PrepaidCard(0, 0);
  }

}
