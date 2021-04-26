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
            $_SESSION["username"]=$user["username"];
            $_SESSION["name"]=$user["name"];
            $this->f->reroute("/");
        } else {
            $this->f->reroute("/signin?err=wrongpassword");
        }
        
    }

    public function logout($username) {
        // from https://www.php.net/manual/en/function.session-destroy
        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();
        $this->f->reroute("/");
    }

    public function showProfile($username) {
        $user = $this->mapper->load(array("username = ?",$username)); // get the user data.
        return $user;
    }

}

?>