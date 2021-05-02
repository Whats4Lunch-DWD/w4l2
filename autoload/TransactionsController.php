<?php
// Class that provides methods for working with the form data.
// There should be NOTHING in this file except this class definition.

class TransactionsController {
	private $mapper;
    private $cart_items_mapper;
    private $user_mapper;
    
	public function __construct() {
		global $f3;

        $this->mapper = new DB\SQL\Mapper($f3->get('DB'),"transactions");	// create DB query mapper object
        $this->cart_items_mapper = new DB\SQL\Mapper($f3->get('DB'),"cart_items");
        $this->user_mapper = new DB\SQL\Mapper($f3->get('DB'),"users");
    }
    
    public function add($cart) {
        //print_r($cart);
        //die();
        $this->mapper->dry();

        foreach($cart as $cart_key => $cart_value) {
            if ($cart_key=="saveaddress") {
                // Do nothing
            } else {
                if ($cart_key!="address2") {
                    if ($cart_value=="") {
                        return 0;
                    }
                }
                $this->mapper[$cart_key]=$cart_value;
            }
        }
        $this->mapper["status"]="in_progress";

        if ($_SESSION["username"]!="") {
            $this->user_mapper->load(array("username=?",$_SESSION["username"]));
            $this->mapper["user_id"]=$this->user_mapper["id"];
        }


        //echo "<pre>";
        //print_r($this->mapper);
        //echo "</pre>";
        //die();


        $_SESSION["CART_SESSION"]=null;
        $this->mapper->save();


        // todo. currently not working
        if ($cart["saveaddress"]=="on" and $cart["address1"]!="") {
            $this->saveAddressToProfile($cart["address1"]);
        }

        return $this->mapper["id"];
    }

    public function listTransactions() {
        $transactions["results"] = $this->mapper->find(array(),array("order"=>"status asc"));

        return $transactions;
    }

    public function getTransaction($id) {
        $transaction = $this->mapper->load(["id=?",$id]);
        $cart_items = $this->cart_items_mapper->find(["cart_id=?",$transaction["cart_id"]]);
        $transaction_cart_items = array("transaction"=>$transaction,"cart_items"=>$cart_items);
        return $transaction_cart_items;
    }

    public function updateTransactionStatus($data) {
        //print_r($data);
        //die();
        $this->mapper->load(array("id=?",$data["id"]));
        $this->mapper["status"] = $data["status"];
        $this->mapper->save();
        
        return $this->getTransaction($data["id"]);
    }

    public function saveAddressToProfile($address) {
        
        $arr_location = explode(" ", $address);
        $pcode = array_pop($arr_location);
        $this->user_mapper->load(array("username=?",$_SESSION["username"]));

        $this->user_mapper["address1"]=$address;
        $this->user_mapper["postalcode"]=$pcode;

        //print_r($this->user_mapper);
        //die();

        $this->user_mapper->save();
    }


}