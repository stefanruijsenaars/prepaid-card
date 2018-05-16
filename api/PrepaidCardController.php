<?php
class PrepaidCardController
{
    /**
     * Returns a JSON string object to the browser when hitting the root of the domain
     *
     * @url GET /
     */
    public function test()
    {
        return "Hello World";
    }

    /**
     * Creates a new card
     *
     * @url POST /card
     */
    public function createNewCard()
    {
        $ownerId = $_POST['ownerId'];
        $cardId = $_POST['cardId'];
        // validate input and log the user in
        return "Hi!";
    }

    /**
     * Gets the user by id or current user
     *
     * @url GET /users/$id
     * @url GET /users/current
     */
    public function getUser($id = null)
    {
        if ($id) {
          return 'hi';
        } else {
            $user = $_SESSION['user'];
        }

        return $user; // serializes object into JSON
    }

    /**
     * Saves a user to the database
     *
     * @url POST /users
     * @url PUT /users/$id
     */
    public function saveUser($id = null, $data)
    {
        // ... validate $data properties such as $data->username, $data->firstName, etc.
        $data->id = $id;
        $user = User::saveUser($data); // saving the user to the database
        return $user; // returning the updated or newly created user object
    }

    /**
     * Gets user list
     *
     * @url GET /users
     */
    public function listUsers($query)
    {
        $users = array('Andra Combes', 'Valerie Shirkey', 'Manda Douse', 'Nobuko Fisch', 'Roger Hevey');
        if (isset($query['search'])) {
          $users = preg_grep("/$query[search]/i", $users);
        }
        return $users; // serializes object into JSON
    }
}
