<?php
// Class that provides methods for working with the form data.
// There should be NOTHING in this file except this class definition.

class CartsController {
	private $mapper;
    private $cart_items_mapper;
	private $menus_mapper;
    
	public function __construct() {
		global $f3;

        $this->mapper = new DB\SQL\Mapper($f3->get('DB'),"carts");	// create DB query mapper object
        $this->cart_items_mapper = new DB\SQL\Mapper($f3->get('DB'),"cart_items");
        $this->menus_mapper = new DB\SQL\Mapper($f3->get('DB'),"menus");	// create DB query mapper object
    }
    
    public function add($id){
        
        $cart_session = $this->mapper->find(["cart_session=?",$_SESSION["CART_SESSION"]]);
        $cart_items = null;

        /*
        echo "php session cart session: ".$_SESSION["CART_SESSION"]."<br />";
        print_r($cart_session); echo "<br />";
        echo "cart id: ".$cart_session[0]["id"]."<br />";
        echo "cart session: ".$cart_session[0]["cart_session"]."<br />";
        */
        //die();
        

        if (count($cart_session)<1) {
            $this->addCart($_SESSION["CART_SESSION"]);
            $cart_session = $this->mapper->find(["cart_session=?",$_SESSION["CART_SESSION"]]);
        }
        $cart_items = $this->cart_items_mapper->find(["cart_id=? and menu_id=?", $cart_session[0]["id"], $id]);
        
        $menu_item = $this->menus_mapper->load(['id=?', $id]);
        //echo "cart session: ".$_SESSION["CART_SESSION"]."<br />";
        //echo "<pre>"; print_r($cart_items); echo "</pre>";
        //echo "cart item id: ".$cart_items[0]["id"]."<br />";
        //echo "menu_id from cart_items: ".$cart_items[0]["menu_id"]."<br />";
        //echo "id from add function: ".$id."<br />";
        //die();

        if ($cart_items[0]["menu_id"] != $id) {
            // Add new item
            foreach ($menu_item as $item_key => $item_value) {
                //echo $item_key."=>".$item_value."<br />";
                if ($item_key != "created_at" ) {
                    if ($item_key == "id") {
                        $this->cart_items_mapper["menu_id"]=$item_value;
                    } else {
                        $this->cart_items_mapper[$item_key]=$item_value;
                    }
                }
                $this->cart_items_mapper["quantity"]=1;
            }   
        } else {
            // Update existing item
            // hydrate the cart
            $hydrated_cart_item = $this->cart_items_mapper->load(["cart_id=? and menu_id=?", $cart_session[0]["id"], $id]);
            if ($hydrated_cart_item["id"]>0) {
                $this->cart_items_mapper["id"]=$hydrated_cart_item["id"];
            }
            $this->cart_items_mapper["quantity"]=$hydrated_cart_item["quantity"]+1;
        }

        $this->cart_items_mapper["cart_id"] = $cart_session[0]["id"];

        /*
        echo "<pre>";
        print_r($this->cart_items_mapper["cart_id"]);
        echo "<br />";
        print_r($this->cart_items_mapper["id"]);
        echo "<br />";
        print_r($this->cart_items_mapper["menu_id"]);
        echo "<br />";
        echo "<br /> cart items object: <br />";
        print_r($this->cart_items_mapper);
        echo "</pre>";
        die();
        */
        
        $this->cart_items_mapper->save();
    }

    public function addCart($data) {
		$this->mapper->cart_session = $data;
        $this->mapper->save();									// save new record with these fields
	}

    public function delete($cart_item_id) { 
        // this id is the cart_item id. not the menu id.
        $this->cart_items_mapper->load(['id=?', $cart_item_id]);				// load DB record matching the given ID
		$this->cart_items_mapper->erase();
    }

	public function getCart($cart_session) {
		$cart = $this->mapper->load(['cart_session=?', $cart_session]);
		$cart_items = $this->cart_items_mapper->find(['cart_id=?', $cart->id]);
		$total_cart_items = count($cart_items);
        $total_cart_value = 0;
        foreach ($cart_items as $c) {
            $total_cart_value += $c["price"]*$c["quantity"];
        }

		$mycart = array("cart"=>$cart, "cart_items"=>$cart_items, "total_cart_items"=>$total_cart_items, "total_cart_value"=>$total_cart_value);
         //print_r($mycart);
		return $mycart;
	}

	public function deleteCart($id) {
		$this->mapper->load(['id=?', $id]);				// load DB record matching the given ID
		$this->mapper->erase();									// delete the DB record
	}

}