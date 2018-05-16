<?php

require_once dirname(__FILE__) . '/../index.php';

class IntegrationTest extends \PHPUnit_Framework_TestCase {

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

  public function testCapture() {
    $this->card->loadMoney(6.00);
    $this->assertEquals(6.00, $this->card->amountLoaded());
    $this->assertEquals(6.00, $this->card->availableBalance());

    $authorizationRequest = new AuthorizationRequest(5.00, $this->dataStore->getNextAuthorizationRequestId(), $this->card, $this->dataStore->getNextMerchantId());
    $this->authorizationRequestHandler = new AuthorizationRequestHandler($authorizationRequest, $this->card);
    $this->authorizationRequestHandler->handle();
    $this->assertEquals(6.00, $this->card->amountLoaded());
    $this->assertEquals(1.00, $this->card->availableBalance());

    $this->authorizationRequestHandler->capture(3.00);
    $this->assertEquals(3.00, $this->card->amountLoaded());
    $this->assertEquals(1.00, $this->card->availableBalance());

    $this->authorizationRequestHandler->capture(2.00);
    $this->assertEquals(1.00, $this->card->amountLoaded());
    $this->assertEquals(1.00, $this->card->availableBalance());
  }

  // TODO: finalize

}