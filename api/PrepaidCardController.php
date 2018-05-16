<?php

require_once("../app/index.php");

class PrepaidCardController
{
    /**
     * Creates a new card
     *
     * @url POST /card
     */
    public function createNewCard($data)
    {
        // validate input and log the user in
	return $data->ownerId;
    }

    /**
     * Loads money onto a card.
     *
     * @url POST /card/$cardId/load-money
     */
    public function loadMoney($cardId, $data)
    {
	 $card = \PrepaidCard\PrepaidCard::load($cardId);
	 if (!$card) {
	     throw new \Jacwright\RestServer\RestException(404, "Card does not exist");
	 }
	 $card->loadMoney($data->amount);
    }

    /**
     * Gets the balance of a card.
     *
     * @url GET /card/$cardId/balance
     */
    public function getBalance($cardId)
    {
	 $card = \PrepaidCard\PrepaidCard::load($cardId);
	 if (!$card) {
	     throw new \Jacwright\RestServer\RestException(404, "Card does not exist");
	 }
	 $out = new StdClass();
	 $out->balance = $card->getBalance();
	 return $out;
    }

    /**
     * Gets the blocked balance of a card.
     *
     * @url GET /card/$cardId/blocked-balance
     */
    public function getBlockedBalance($query)
    {
	 $card = \PrepaidCard\PrepaidCard::load($cardId);
	 if (!$card) {
	     throw new \Jacwright\RestServer\RestException(404, "Card does not exist");
	 }
	 $out = new StdClass();
	 $out->blockedBalance = $card->getBlockedBalance();
    }
}
