<?php

  /////////////////////////////////////
 // index.php for Whats4Lunch app //
/////////////////////////////////////

// Create f3 object then set various global properties of it
// These are available to the routing code below, but also to any
// classes defined in autoloaded definitions

$f3 = require('../../AboveWebRoot/fatfree-master-3.7/lib/base.php');

// autoload Controller class(es) and anything hidden above web root, e.g. DB stuff
$f3->set('AUTOLOAD','autoload/;../../AboveWebRoot/autoload/');

$db = DatabaseConnection::connect();		// defined as autoloaded class in AboveWebRoot/autoload/
$f3->set('DB', $db);
$f3->get('DB')->exec("set sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';");

$f3->set('DEBUG',3);		// set maximum debug level
$f3->set('UI','ui/');		// folder for View templates

session_start();
if (!isset($_SESSION["CART_SESSION"])) {
  $session = session_create_id();
  $_SESSION["CART_SESSION"] = $session;
}

  /////////////////////////////////////////////
 // Simple Example URL application routings //
/////////////////////////////////////////////

//home page (index.html) -- actually just shows form entry page with a different title
$f3->route('GET /',
  function ($f3) {
    $f3->set('html_title','Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','home.html');
    echo Template::instance()->render('layout.html');
  }
);

// search resto
$f3->route('POST /',
  function ($f3) {
    $query = $f3->get('POST');
    $f3->set('query',$query);

    //print_r($query);

    if ($query["choicestyle"]=="3choices") {
      
      $criteria = "dish_name like '%".$query["query"]."%' and diet like '%".$query["diet"]."%'";
      if ($query["allergy"]!="") {
        $criteria .= "and allergen not like '%".$query["allergy"]."%'";
      }
      $sql = "select distinct restaurant_id, restaurant_name, restaurants.image from hazrulaz_whats4lunch.menus inner join hazrulaz_whats4lunch.restaurants on menus.restaurant_id=restaurants.id where ".$criteria;
      //echo $sql;
      $f3->set('results',$f3->get('DB')->exec($sql));

    } else {
      
      $criteria = "(dish_name like '%".$query["query"]."%' and diet like '%".$query["diet"]."%'";
      if ($query["allergy"]!="") {
        $criteria .= "and allergen not like '%".$query["allergy"]."%')";
      } else {
        $criteria .= ")";
      }
      $count_sql = "select count(distinct restaurant_id) as max_records from hazrulaz_whats4lunch.menus inner join hazrulaz_whats4lunch.restaurants on menus.restaurant_id=restaurants.id where ".$criteria;
      //echo $sql;
      $f3->set('sum_of_records',$f3->get('DB')->exec($count_sql));
      $max_records = $f3->get('sum_of_records');
      if ($max_records["0"]["max_records"]<1) {
        $max = 1;
      } else {
        $max = $max_records["0"]["max_records"];
      }
      //print_r($max_records["0"]["max_records"]);
      
      $criteria .= " and restaurants.id=".random_int(1,$max);
      $sql = "select distinct restaurant_id, restaurant_name, restaurants.image from hazrulaz_whats4lunch.menus inner join hazrulaz_whats4lunch.restaurants on menus.restaurant_id=restaurants.id where ".$criteria;
      $f3->set('results',$f3->get('DB')->exec($sql));
    }
    $f3->set('html_title','Restaurant - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','restaurants/search_response.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /about',
  function ($f3) {
    $f3->set('html_title','About - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','about.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /restaurants',
  function ($f3) {
    $controller = new RestaurantController;
    $data = $controller->listRestaurants();
    $f3->set("records", $data);
    $f3->set('html_title','Restaurant - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','restaurants/list.html');
    echo Template::instance()->render('layout.html');
  }
);

// Todo: modify the comment to add the id value in getRestaurant
// Show the Show restaurants Page
$f3->route('GET /restaurants/show/@id',
  function ($f3,$args) {
    $controller = new RestaurantController;
    //print_r($args);
    $data = $controller->getRestaurant($args['id']);
    $f3->set('result',$data);
    //print_r($data);
    $f3->set('html_title','Restaurant - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','restaurants/show.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /cart',
  function ($f3) {
    $controller = new CartsController;
    $data = $controller->getCart($_SESSION["CART_SESSION"]);
    $f3->set('cart',$data);
    $f3->set('html_title','Cart - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','cart.html');
    echo Template::instance()->render('layout.html');
  }
);

// Show the Add Cart page.
$f3->route('GET /cart/add/@id',
  function ($f3, $args) {
    $controller = new CartsController;
    $data = $controller->add($args['id']);
    $f3->reroute('/cart');
  }
);

// Show the Delete Cart page.
$f3->route('GET /cart/delete/@id',
  function ($f3, $args) {
    $controller = new CartsController;
    $data = $controller->delete($args['id']);
    $f3->reroute('/cart');
  }
);

// Confirm the order
$f3->route('POST /transactions/add',
  function ($f3) {
    $cart = $f3->get('POST');
    $controller = new TransactionsController;
    $transaction_id = $controller->add($cart);
    $f3->reroute('/transactions/'.$transaction_id);
  }
);

$f3->route('GET /transactions/@id',
  function ($f3, $args) {
    $controller = new TransactionsController;
    $data = $controller->getTransaction($args['id']);
    $f3->set('transaction_cart_items',$data);
    $f3->set('html_title','Transaction - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','transactions/show.html');
    echo Template::instance()->render('layout.html');
  }
);


$f3->route('GET /sign-in',
  function ($f3) {
    $f3->set('html_title','Sign In - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','sign-in.html');
    echo Template::instance()->render('layout.html');
  }
);

  ////////////////////////
 // Run the F3 engine //
////////////////////////

$f3->run();

?>
