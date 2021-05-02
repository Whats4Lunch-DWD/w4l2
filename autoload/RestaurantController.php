<?php
// Class that provides methods for working with the form data.
// There should be NOTHING in this file except this class definition.

class RestaurantController {
	private $mapper;
	private $menus_mapper;

	public function __construct() {
		global $f3;
		global $basket;

		$this->mapper = new DB\SQL\Mapper($f3->get('DB'),"restaurants");	// create DB query mapper object
																			// for the "restaurants" table
		$this->menus_mapper = new DB\SQL\Mapper($f3->get('DB'),"menus");
    }
    
    public function addRestaurant($data) {
		$this->reset();
		$this->mapper->save();									// save new record with these fields
	}

	public function showcaseRestaurants() {
		$restaurants = $this->mapper->find(array(),array('limit'=>6));	
		$total_restaurants = count($restaurants);
		$r = array("results"=>$restaurants,"total_results"=>$total_restaurants);
		
		return $r;
	}

	public function listRestaurants() {
		$restaurants = $this->mapper->find();	
		$total_restaurants = count($restaurants);
		$r = array("results"=>$restaurants,"total_results"=>$total_restaurants);
		
		return $r;
	}

	public function getRestaurant($id) {
		$restaurant = $this->mapper->load(['id=?', $id]);
		$menu = $this->menus_mapper->find(['restaurant_id=?', $id]);
		$total_menu_items = count($menu);

		$restaurant_menu = array("restaurant"=>$restaurant, "menu"=>$menu, "total_menu_items"=>$total_menu_items);
		return $restaurant_menu;
	}

	public function updateRestaurant($data) {
		$this->mapper->save();									// save new record with these fields
	}

	public function deleteRestaurant($id) {
		$this->mapper->load(['id=?', $id]);				// load DB record matching the given ID
		$this->mapper->erase();									// delete the DB record
	}

	public function showMenu($id) {
		$menu = $this->menus_mapper->load(['id=?', $id]);

		return $menu;
	}

	public function editMenu($data) {
		//print_r($data); die();
		$this->menus_mapper->load(array("id=?",$data["id"]));
		foreach($data as $key => $value) {
			if ($key == "image" or $key == "description" or $key == "diet" or $key == "allergen") {
				// do nothing
			} else {
				if ($value=="") {
					return array("err"=>"Everything is mandatory except image and description","menu_id"=>$data["id"]);
				}
			}
        	$this->menus_mapper[$key]=$value;
        }

		$this->menus_mapper["image"] = $_FILES['image']['name'];

		if ($this->menus_mapper["image"]!="") {
			if (move_uploaded_file($_FILES['image']['tmp_name'], "uploads/".$this->menus_mapper["image"])) {
				//print "Uploaded successfully!";
			 } else {
				//print "Upload failed!";
			 }
		}

		$this->menus_mapper->save();
		return $this->menus_mapper["id"];
	}

	public function addMenu($data) {
		$this->menus_mapper->dry();

		foreach($data as $key => $value) {
			if ($key == "image" or $key == "description" or $key == "diet" or $key == "allergen") {
				// do nothing
			} else {
				if ($value=="") {
					return array("err"=>"Everything is mandatory except image and description","restaurant_id"=>$data["restaurant_id"]);
				}
			}
        	$this->menus_mapper[$key]=$value;
        }

		$this->menus_mapper["image"] = $_FILES['image']['name'];

		if ($this->menus_mapper["image"]!="") {
			if (move_uploaded_file($_FILES['image']['tmp_name'], "uploads/".$this->menus_mapper["image"])) {
				//print "Uploaded successfully!";
			 } else {
				//print "Upload failed!";
			 }
		}

		$this->menus_mapper->save();
		return $this->menus_mapper["id"];
	}

}