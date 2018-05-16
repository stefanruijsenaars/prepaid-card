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
