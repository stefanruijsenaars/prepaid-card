
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
