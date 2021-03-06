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

$db = DatabaseConnection::connectv2();		// defined as autoloaded class in AboveWebRoot/autoload/
$f3->set('DB', $db);
$f3->get('DB')->exec("set sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';");

$f3->set('DEBUG',3);		// set maximum debug level
$f3->set('UI','ui/');		// folder for View templates
$f3->set('UPLOADS','uploads/');

session_start();
if (!isset($_SESSION["CART_SESSION"])) {
  $session = session_create_id();
  $_SESSION["CART_SESSION"] = $session;
}

if (isset($_SESSION["username"]) and !is_null($_SESSION["username"])) {
  $f3->set('username',$_SESSION["username"]); 
}

if (isset($_SESSION["name"]) and !is_null($_SESSION["name"])) {
  $f3->set('name',$_SESSION["name"]); 
}

if (isset($_SESSION["phone"]) and !is_null($_SESSION["phone"])) {
  $f3->set('phone',$_SESSION["phone"]); 
}

if ($_GET["location"]!="") {
  $location = $_GET["location"];
  $arr_location = explode(" ", $location);
  $pcode = array_pop($arr_location);
  $f3->set("location",$location);
  $f3->set("pcode",$pcode);
}

  /////////////////////////////////////////////
 // Simple Example URL application routings //
/////////////////////////////////////////////

//home page (index.html) -- actually just shows form entry page with a different title
$f3->route('GET /',
  function ($f3) {

    $controller = new RestaurantController;
    $data = $controller->showcaseRestaurants();
    $f3->set("records", $data);

    if ($_SESSION["username"]!="") {
      $user_controller = new UsersController;
      $user = $user_controller->showProfile($_SESSION["username"]);
      $f3->set("user",$user);
      $f3->set("saved_address",$user["address1"]);
    }
    
    $f3->set('html_title','Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','home.html');
    $f3->set('page','Home');

    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /signin',
  function ($f3) {

    $f3->set('html_title','Sign In - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','sign-in.html');
    $f3->set('err',$_GET["err"]);

    if($_GET["signup"]=="success") {
      $f3->set('signup',$_GET["signup"]);
    }
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /admin/signin',
  function ($f3) {

    $f3->set('html_title','Admin Sign In - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','admin/sign-in.html');
    $f3->set('err',$_GET["err"]);

    if($_GET["signup"]=="success") {
      $f3->set('signup',$_GET["signup"]);
    }
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /signup',
  function ($f3) {

    $f3->set('html_title','Sign Up - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','sign-up.html');

    $err = explode("<br />",$_GET["err"]);

    if ($err[0]!="") {
      $f3->set('err',$err);
    }
    
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('POST /signup',
  function ($f3) {
    $controller = new UsersController;
    $auth = $controller->signup($_POST);
  }
);

$f3->route('POST /login',
  function ($f3) {
    $controller = new UsersController;
    $auth = $controller->login($_POST["username"],$_POST["password"]);
  }
);

$f3->route('POST /admin/login',
  function ($f3) {
    $controller = new UsersController;
    $auth = $controller->admin_login($_POST["username"],$_POST["password"]);
  }
);

$f3->route('GET /signout',
  function ($f3) {
    $controller = new UsersController;
    $auth = $controller->logout($_SESSION["username"]);
  }
);

$f3->route('GET /profile',
  function ($f3) {
    $controller = new UsersController;
    $data = $controller->showProfile($_SESSION["username"]);
    $transactions = $controller->userTransactions($_SESSION["username"]);
    //$transactions_sql = "select * from hazrulaz_whats4lunch2.transactions inner join hazrulaz_whats4lunch2.cart_items on transactions.cart_id=cart_items.cart_id where transactions.user_id=".$data["id"];
    //echo $transactions_sql;
    //$transactions["results"] = $f3->get('DB')->exec($transactions_sql);

    //print_r($transactions);
    
    $f3->set("user",$data);
    $f3->set("transactions",$transactions);
    $f3->set('html_title','Profile - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','profile.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /profile/edit',
  function ($f3) {
    $controller = new UsersController;
    $data = $controller->showProfile($_SESSION["username"]);
    $f3->set("user",$data);
    $f3->set('html_title','Edit Profile - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','edit_profile.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('POST /profile/edit',
  function ($f3) {
    $controller = new UsersController;
    $data = $controller->updateProfile($_POST);
    
    $f3->reroute('/profile');
  }
);

// search resto
//$f3->route('POST /',
$f3->route('GET /search',
  function ($f3) {
    //$query = $f3->get('POST');
    $query = $f3->get('GET');
    $f3->set('query',$query);

    //print_r($query);

    if ($query["choicestyle"]=="3choices") {
      
      $criteria = "dish_name like '%".$query["query"]."%' and diet like '%".$query["diet"]."%'";
      if ($query["allergy"]!="") {
        $criteria .= "and allergen not like '%".$query["allergy"]."%'";
      }
      $criteria .= " ORDER BY RAND() LIMIT 3";

      $sql = "select distinct restaurant_id, restaurant_name, restaurants.image from hazrulaz_whats4lunch2.menus inner join hazrulaz_whats4lunch2.restaurants on menus.restaurant_id=restaurants.id where ".$criteria;
      //echo $sql;

      $f3->set('results',$f3->get('DB')->exec($sql));

    } else {
      
      $criteria = "(dish_name like '%".$query["query"]."%' and diet like '%".$query["diet"]."%'";
      if ($query["allergy"]!="") {
        $criteria .= "and allergen not like '%".$query["allergy"]."%')";
      } else {
        $criteria .= ")";
      }
      $criteria .= " ORDER BY RAND() LIMIT 1";
      
      $sql = "select distinct restaurant_id, restaurant_name, restaurants.image from hazrulaz_whats4lunch2.menus inner join hazrulaz_whats4lunch2.restaurants on menus.restaurant_id=restaurants.id where ".$criteria;
      $f3->set('results',$f3->get('DB')->exec($sql));
    }
    $f3->set('location', $_GET["location"]);
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
    $f3->set("location", $_GET["location"]);
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
    $f3->set("location",$_GET["location"]);
    $f3->set("err", $_GET["err"]);

    if ($_GET["location"]!="") {
      $location = $_GET["location"];
      $arr_location = explode(" ", $location);
      $pcode = array_pop($arr_location);
      $f3->set("pcode",$pcode);
    }

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
    if ($_GET["location"]!="") {
      $f3->reroute('/cart?location='.$_GET["location"]);
    }
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
    if ($transaction_id==0) {
      $f3->reroute('/cart/?err=1&location='.$_GET["location"]);
    } else {
      $f3->reroute('/transactions/'.$transaction_id);
    }
    
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

$f3->route('GET /admin/sign-in',
  function ($f3) {
    $f3->set('html_title','Admin Sign In - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','admin/sign-in.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /admin',
  function ($f3) {
    // restos
    // menus
    // orders

    $f3->set('html_title','Admin - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','admin/index.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /admin/restaurants',
  function ($f3) {
    $controller = new RestaurantController;
    $data = $controller->listRestaurants();
    $f3->set("err",$_GET["err"]);
    $f3->set("addresto_success",$_GET["success"]);
    $f3->set("records", $data);
    $f3->set('html_title','Manage Restaurants - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','admin/restaurants.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /admin/add_restaurant',
  function ($f3) {
    $f3->set('html_title','Add Restaurants - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','admin/add_restaurant.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('POST /admin/add_restaurant',
  function ($f3) {
    $controller = new RestaurantController;
    $data = $f3->get('POST');
    $response = $controller->addRestaurant($data);
    
    if ($response["err"] != "") {
      $f3->reroute("/admin/restaurants?err=".$response["err"]);  
    }
    $f3->reroute("/admin/restaurants?success=addresto");
  }
);

$f3->route('GET /admin/restaurants/show/@id',
  function ($f3,$args) {
    $controller = new RestaurantController;
    //print_r($args);
    $data = $controller->getRestaurant($args['id']);
    $f3->set('err',$_GET["err"]);
    $f3->set('addmenu_success',$_GET["success"]);
    $f3->set('result',$data);
    $f3->set('resto_id',$args['id']);
    //print_r($data);
    $f3->set('html_title','Restaurant Menu - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','admin/menu.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /admin/orders',
  function ($f3) {
    $controller = new TransactionsController;
    $transactions = $controller->listTransactions();
    $f3->set('transactions',$transactions);
    $f3->set('html_title','Manage Orders - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','admin/orders.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /admin/orders/@id',
  function ($f3, $args) {
    $controller = new TransactionsController;
    $data = $controller->getTransaction($args['id']);
    $f3->set('transaction_cart_items',$data);
    $f3->set('html_title','Order Detail - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','admin/order_detail.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('POST /admin/orders/@id',
  function ($f3) {
    $controller = new TransactionsController;
    $args = $f3->get('POST');
    $data = $controller->updateTransactionStatus($args);
    $f3->set('transaction_cart_items',$data);
    $f3->set('html_title','Order Detail - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','admin/order_detail.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('GET /admin/add_menu',
  function ($f3) {
    $controller = new RestaurantController;
    $restaurant = $controller->getRestaurant($_GET['restaurant']);
    $f3->set('resto_id',$_GET['restaurant']);
    $f3->set('resto_name',$restaurant['restaurant']['restaurant_name']);
    $f3->set('html_title','Add Menu - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','admin/add_menu.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('POST /admin/add_menu',
  function ($f3) {
    $controller = new RestaurantController;
    $menu = $f3->get('POST');
    $response = $controller->addMenu($menu);
    if ($response["err"] != "") {
      $f3->reroute("/admin/restaurants/show/".$menu['restaurant_id']."?err=".$response["err"]);  
    }
    $f3->reroute("/admin/restaurants/show/".$menu['restaurant_id']."?success=addmenu");
  }
);

$f3->route('GET /admin/edit_menu/@id',
  function ($f3,$args) {
    $controller = new RestaurantController;
    $menu = $controller->showMenu($args['id']);
    $restaurant = $controller->getRestaurant($menu['restaurant_id']);
    $f3->set('resto_name',$restaurant["restaurant"]["restaurant_name"]);
    $f3->set('err',$_GET["err"]);
    $f3->set('menu',$menu);
    $f3->set('html_title','Add Menu - Whats4Lunch - The World\'s easiest Food Delivery for people with diets and allergies');
    $f3->set('content','admin/edit_menu.html');
    echo Template::instance()->render('layout.html');
  }
);

$f3->route('POST /admin/edit_menu/@id',
  function ($f3,$args) {
    $controller = new RestaurantController;
    $menu = $f3->get('POST');
    $response = $controller->editMenu($menu); //print_r($response); die();
    if ($response["err"] != "") {
      $f3->reroute("/admin/edit_menu/".$args["id"]."?err=".$response["err"]);  
    }
    $f3->reroute("/admin/restaurants/show/".$menu['restaurant_id']."?success=editmenu");
  }
);

  ////////////////////////
 // Run the F3 engine //
////////////////////////

$f3->run();

?>
