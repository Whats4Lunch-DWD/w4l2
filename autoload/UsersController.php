<?php

class UsersController {
	private $mapper;

	public function __construct() {
		global $f3;
		$this->mapper = new DB\SQL\Mapper($f3->get('DB'),"users");	// create DB query mapper object		
    }

    public function login($username, $password) {        
        $crypt = \Bcrypt::instance();
        $hashed_password = $crypt->hash($_POST["password"], \Base::instance()->get('salt'), 10);
        echo($hashed_password);

        //$crypt->verify($hashed_password, $hashed_password_in_db);

    }

}

?>