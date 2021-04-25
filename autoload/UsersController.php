<?php

class UsersController {
	private $mapper;
    var $f;

	public function __construct() {
		global $f3;
        $this->f = $f3;
		$this->mapper = new DB\SQL\Mapper($f3->get('DB'),"users");	// create DB query mapper object		
    }

    public function login($username, $password) {        
        //$hashed_password = password_hash($password, PASSWORD_DEFAULT);
        //echo $hashed_password;

        $user = $this->mapper->load(array("username = ?",$username)); // get the user data. we want to get the hashed_password.

        if (password_verify($password,$user["password"])) {
            echo " valid";
        } else {
            $this->f->reroute("/signin?err=wrongpassword");
        }
        
    }

}

?>