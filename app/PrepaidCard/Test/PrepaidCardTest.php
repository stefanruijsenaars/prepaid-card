<?php

namespace PrepaidCard;

require_once dirname(__FILE__) . '/../../index.php';

class PrepaidCardTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var DataStore
   */
  protected $dataStore;

  /**
   * @var PrepaidCard
   */
  protected $card;

  /**
   * @var AuthorizationRequestHandler
   */
  protected $authorizationRequestHandler;

  public function setUp() {
    $this->dataStore = new DataStore();
    $this->card = new PrepaidCard($ownerId = $this->dataStore->getNextOwnerId(), $cardId = $this->dataStore->getNextCardId());
  }

  public function testLoadMoney() {
    $this->assertEquals(0.00, $this->card->amountLoaded());
    $this->card->loadMoney(0.01);
    $this->assertEquals(0.01, $this->card->amountLoaded());
    $this->card->loadMoney(0.01);
    $this->assertEquals(0.02, $this->card->amountLoaded());
  }

  public function testCreateNewCard() {
    $card = new PrepaidCard($ownerId = $this->dataStore->getNextOwnerId(), $cardId = $this->dataStore->getNextCardId());
    // We assume that the card has 0 on it by default
    $this->assertEquals(0.00, $card->availableBalance());
  }

  public function testGetAvailableBalance() {
    $this->card->loadMoney(0.01);
    $this->card->loadMoney(0.01);
    $this->assertEquals(0.02, $this->card->availableBalance());
  }

  public function testGetBlockedBalance() {
    $authorizationRequest = new AuthorizationRequest(3.01, $this->dataStore->getNextAuthorizationRequestId(), $this->card, new Merchant(1));
    $this->card->earmark($authorizationRequest);
    $this->assertEquals(3.01, $this->card->amountBlocked());
    $authorizationRequest = new AuthorizationRequest(3.02, $this->dataStore->getNextAuthorizationRequestId(), $this->card, new Merchant(1));
    $this->card->earmark($authorizationRequest);
    $this->assertEquals(6.03, $this->card->amountBlocked());
  }

}
