<?php

class UsersController {
	private $mapper;
    private $txn_mapper;
    private $cart_items_mapper;
    var $f;

	public function __construct() {
		global $f3;
        $this->f = $f3;
		$this->mapper = new DB\SQL\Mapper($f3->get('DB'),"users");	// create DB query mapper object	
        $this->checker = new DB\SQL\Mapper($f3->get('DB'),"users");	// create DB query mapper object
        $this->txn_mapper = new DB\SQL\Mapper($f3->get('DB'),"transactions");	// create DB query mapper object	
        $this->cart_items_mapper = new DB\SQL\Mapper($f3->get('DB'),"cart_items");	// create DB query mapper object	
    }

    public function signup($form) {
        $this->mapper->dry();
        
        foreach($form as $form_key => $form_value) {
            if ($form_key=="password") {
                $this->mapper["password"]=password_hash($form_value, PASSWORD_DEFAULT);
            } else if ($form_key=="signup") {
                // do nothing.
            } else {
                $this->mapper[$form_key]=$form_value;
            }
        }
        $this->mapper["address1"]=null;
        $this->mapper["address2"]=null;
        $this->mapper["postalcode"]=null;
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
            $err .= "Email cannot be null".html_entity_decode("&lt;br /&gt;");
        }

        if ($form["phone"]=="") {
            $err .= "Phone cannot be null".html_entity_decode("&lt;br /&gt;");
        }

        //echo "<pre>";
        //print_r($form);
        //print_r($err);
        //print_r($this->mapper);
        //echo "</pre>";
        //die();

        // the form contains singup field. remove it.

        if (!isset($err)) {
            //echo $this->f->get("DB")->log();
            $this->mapper->save();
            //die();
            $this->f->reroute("/signin?signup=success");
        } else {
            //$this->f->reroute("/signup?err=".$err."&".implode("&",$form));
            $this->f->reroute("/signup?err=".$err);
        }
    }

    public function checkUsername($username) {
        $this->checker = new DB\SQL\Mapper($this->f->get('DB'),"users");	// create DB query mapper object		
        $user = $this->checker->load(array("username = ?",$username));

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

    public function admin_login($username, $password) {        
        $user = $this->mapper->load(array("username = ?",$username)); // get the user data. we want to get the hashed_password.

        if (password_verify($password,$user["password"])) {
            if ($user["role"]=="admin") {
                $_SESSION["username"]=$user["username"];
                $_SESSION["name"]=$user["name"];
                $_SESSION["phone"]=$user["phone"];
                $_SESSION["role"]=$user["role"];
                $this->f->reroute("/admin");
            } else {
                $this->f->reroute("/admin/signin?err=notadmin");
            }
        } else {
            $this->f->reroute("/admin/signin?err=wrongpassword");
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

    public function userTransactions($username) {
        $user = $this->showProfile($username);
        $transactions["results"] = $this->txn_mapper->find(array("user_id=?",$user["id"]));

        return $transactions;
    }

    public function cartTxn($cart_id) {
        $cart_items = $this->cart_items_mapper->find(array("cart_id=?",$cart_id));
        return $cart_items;
    }

    public function updateProfile($form) {
        $this->mapper->load(array("id = ?",$form["id"])); // get the user data.

        foreach($form as $key => $value) {
            
            $this->mapper[$key] = $value;
        }

        $this->mapper->save();

        return $this->showProfile($_SESSION["username"]);
    }

}

?>