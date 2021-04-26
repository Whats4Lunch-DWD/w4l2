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
        
        foreach($form as $form_key => $form_value) {
            $this->mapper[$form_key]=$form_value;
        }
        $this->mapper["role"]="normal";
        $this->mapper["active"]="1";

        //echo "<pre>";
        //print_r($form);
        //print_r($this->mapper);
        //echo "</pre>";
        //die();

        if ($form["name"]=="") {
            $err .= "Name cannot be null<br />";
        }

        if ($form["username"]=="") {
            $err .= "Username cannot be null<br />";
        }

        if (strlen($form["username"])<6) {
            $err .= "Username size cannot be less than 6 characters<br />";
        }

        if ($this->checkUsername($form["username"])) {
            $err .= "User already exists<br />";
        }

        if ($form["password"]=="") {
            $err .= "Password cannot be null<br />";
        }

        if (strlen($form["password"])<6) {
            $err .= "Password size cannot be less than 6 characters<br />";
        }

        if ($form["email"]=="") {
            $err .= "Email cannot be null&lt;br /&gt;";
        }

        if ($form["phone"]=="") {
            $err .= "Phone cannot be null<br />";
        }

        //echo "<pre>";
        //print_r($form);
        //print_r($err);
        //echo "</pre>";
        //die();

        if (!isset($err)) {
            $this->mapper->save();
            $this->f->reroute("/signin");
        } else {
            $this->f->reroute("/signup?err=".$err);
        }
    }

    public function checkUsername($username) {
        $this->mapper = new DB\SQL\Mapper($this->f->get('DB'),"users");	// create DB query mapper object		
        $user = $this->mapper->load(array("username = ?",$username));

        if ($user == "") {
            return 0;
        } else {
            return 1;
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