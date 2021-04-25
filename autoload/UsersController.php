<?php

class UsersController {
	private $mapper;

	public function __construct() {
		global $f3;
		$this->mapper = new DB\SQL\Mapper($f3->get('DB'),"users");	// create DB query mapper object		
    }

    public function login($username, $password) {        
        //$hashed_password = password_hash($password, PASSWORD_DEFAULT);
        //echo $hashed_password;

        $user = $this->mapper->load("username = ?",$username); // get the user data. we want to get the hashed_password.

        print_r($user);

        /*
        if (password_verify("password",$hashed_password)) {
            echo " valid";
        } else {
            echo " invalid";
        }
        */
    }

}

?>