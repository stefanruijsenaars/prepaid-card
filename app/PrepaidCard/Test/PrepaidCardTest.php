<?php

require_once dirname(__FILE__) . '/../index.php';

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
    $this->assertEquals(FALSE, $this->card->isActive());
    $this->card->loadMoney(0.01);
    $this->assertEquals(TRUE, $this->card->isActive());
    $this->assertEquals(0.01, $this->card->availableBalance());
    $this->assertEquals(0.01, $this->card->amountLoaded());
  }

}
