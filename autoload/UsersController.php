<?php

class UsersController {
	private $mapper;
    var $f;

	public function __construct() {
		global $f3;
        $this->f = $f3;
		$this->mapper = new DB\SQL\Mapper($f3->get('DB'),"users");	// create DB query mapper object		
    }

    public function signup($form) {
        $this->mapper->dry();
        $err = 0;
        
        foreach($form as $form_key => $form_value) {
            $this->mapper[$form_key]=$form_value;
        }
        $this->mapper["role"]="normal";
        $this->mapper["active"]="1";

        echo "<pre>";
        print_r($this->mapper);
        echo "</pre>";
        die();

        if (!isset($this->mapper["name"]) or is_null($this->mapper["name"])) {
            $err .= "Name cannot be null<br />";
        }

        if (!isset($this->mapper["username"]) or is_null($this->mapper["username"])) {
            $err .= "Username cannot be null<br />";
        }

        if (!isset($this->mapper["password"]) or is_null($this->mapper["password"])) {
            $err .= "Password cannot be null<br />";
        }

        if (!isset($this->mapper["email"]) or is_null($this->mapper["email"])) {
            $err .= "Email cannot be null<br />";
        }

        if (!isset($this->mapper["phone"]) or is_null($this->mapper["phone"])) {
            $err .= "Phone cannot be null<br />";
        }

        if ($err==0) {
            $this->mapper->save();
            $this->f->reroute("/signin");
        } else {
            $this->f->reroute("/signup?err=".$err);
        }
    }

    public function login($username, $password) {        
        $user = $this->mapper->load(array("username = ?",$username)); // get the user data. we want to get the hashed_password.

        if (password_verify($password,$user["password"])) {
            $_SESSION["username"]=$user["username"];
            $_SESSION["name"]=$user["name"];
            $_SESSION["phone"]=$user["phone"];
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