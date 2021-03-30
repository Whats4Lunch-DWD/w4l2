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

}