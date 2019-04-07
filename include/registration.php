<?php

class OrderDescription {
  // The order parameters
  public $firstname;
  public $lastname;
  public $email;
  public $status;
  public $orderid;
  public $order;
  public $lineitems;
  public $errors;
  public $paypal_orderid;

  public function __construct() {
    $this->errors = array();
    $this->lineitems = array();
  }

  public function add_line_item($desc, $price) {
    $this->lineitems[] = array(
      'desc' => $desc,
      'price' => $price
    );
  }

  public function format_line_items() {
    $rv = "";
    foreach ( $this->lineitems as $item ) {
      $rv .= "{$item['desc']} : $ {$item['price']}\n";
    }
    return $rv;
  }

  public function add_error($msg) {
    $this->errors[] = $msg;
  }

  public function sum_price() {
    $price = 0.0;
    foreach ( $this->lineitems as $item ) {
      $price = $price + $item['price'];
    }
    if ($price < 0) {
      $price = 0.0;
    }
    return $price;
  }

  public function to_client() {
    $rv = array();
    $rv['errors'] = $this->errors;
    if (count($this->errors) > 0) {
      $rv['success'] = false;
      return $rv;
    }

    $rv['success'] = true;
    $rv['price'] = $this->sum_price();
    $rv['firstname'] = $this->firstname;
    $rv['lastname'] = $this->lastname;
    $rv['email'] = $this->email;
    if (!is_null($this->orderid)) {
      $rv['orderid'] = $this->orderid;
    }
    if (!is_null($this->status)) {
      $rv['status'] = $this->status;
    } else {
      $rv['status'] = 'draft';
    }

    $rv['summary'] = $this->format_line_items();
    return $rv;
  }
}

class SweetMoRegistration {
  private static $_instance = null;

  public static function instance () {
    if ( is_null(self::$_instance) ) { self::$_instance = new self(); }
    return self::$_instance;
  }

  public function add_ajax_callbacks() {
    add_action(
      'wp_ajax_nopriv_smbpreview',
      array($this, 'preview_order_post'));
    add_action(
      'wp_ajax_smbpreview',
      array($this, 'preview_order_post'));

    add_action(
      'wp_ajax_smbnudgedb',
      array($this, 'nudgedb_ajax'));

    add_action(
      'wp_ajax_nopriv_smbregister',
      array($this, 'register_initial_post'));
    add_action(
      'wp_ajax_smbregister',
      array($this, 'register_initial_post'));
  }

  public function nudgedb_ajax() {
    $this->configure();
    wp_send_json_success();
  }

  private function safe_fetch($data, ...$keys_or_filters) {
    foreach ($keys_or_filters as $kf) {
      if (is_null($data)) {
        return null;
      }

      if (is_callable($kf)) {
        if (!$kf($data)) {
          return null;
        }
      } else {
        if (!is_array($data)) {
          // asked to index further, but this isn't an array
          return null;
        }

        $data = isset($data[$kf]) ? $data[$kf] : null;
      }
    }
    return $data;
  }

  public function preview_order_post() {
    require_once('mdsc_deps.php');
    $input = MDSC_Hacks::get_real_post();
    //wp_send_json_success($input);
    $desc = $this->describe_order($input);
    wp_send_json_success($desc->to_client());
  }

  /**
   * Create the paypal order for the given order id
   */
  public function create_order_post() {
  }


  /**
   * When the registration page sends an initial post
   */
  public function register_initial_post() {
    require_once('mdsc_deps.php');
    $data = MDSC_Hacks::get_real_post();
    // see https://developer.paypal.com/demo/checkout/#/pattern/server
    // has to send the order ID back to the front end.
    // https://developer.paypal.com/docs/checkout/reference/server-integration/set-up-transaction/

    // validate it
    // create a summary and compute the price (includes coupon evaluation)
    // create the order in paypal (not yet)
    // save the order to the database, along with the paypal order id
    // return success w/ the order id and summary of the order (and price)
    wp_send_json_success($data);
  }

  private function describe_order($order) {
    require_once('mdsc_deps.php');
    $desc = new OrderDescription();

    $this->_describe_user($order, $desc);
    $this->_describe_pass($order, $desc);
    $this->_describe_comp($order, $desc);
    $this->_describe_choreo_comp($order, $desc);
    $this->_describe_shirt($order, $desc);
    $this->_describe_food($order, $desc);
    $this->_describe_housing($order, $desc);
    $this->_describe_volunteering($order, $desc);
    $this->_describe_policies($order, $desc);
    $this->_describe_voucher($order, $desc);

    return $desc;
  }

  private function _describe_voucher($order, $desc) {
    $vcode = $this->safe_fetch($order, 'voucher', 'is_string');
    if (empty($vcode)) {
      return;
    }

    // PUT VALID VOUCHERS HERE

    $desc->add_error("Unrecognized voucher code: $vcode");
  }

  private function _describe_policies($order, $desc) {
    $selection = $this->safe_fetch($order, 'policies', 'accepted');
    if (empty($selection) || ($selection !== 'yes' && $selection !== true)) {
      $desc->add_error("Policies: Please review and accept the Policies");
    }
  }

  private function _describe_volunteering($order, $desc) {
    $selection = $this->safe_fetch($order, 'volunteer', 'type', 'is_string');
    if (empty($selection)) {
      $desc->add_error("Volunteering: Please make a selection");
    } elseif ($selection !== 'no' && $selection !== 'yes') {
      $desc->add_error("Volunteering: Invalid selection. Please reload the page and try again.");
    }

    $desc->add_line_item("Volunteering: $selection", 0.0);
  }

  private function _describe_housing($order, $desc) {
    $print_name = "Housing";
    $role = $this->safe_fetch($order, 'house', 'type', 'is_string');
    if (empty($role)) {
      $desc->add_error("$print_name: Please make a selection");
      return;
    }

    if ($role === 'none') {
      $desc->add_line_item("Housing: Not participating", 0.0);
      return;
    } elseif ($role !== 'host' && $role !== 'guest') {
      $desc->add_error("$print_name: Invalid host/guest selection (\"{$role}\"). Please reload the page and try again.");
      return;
    }

    $line_desc = "Housing: {$role}";
    $long_desc = array();

    if ($role === 'guest') {
      $v = $this->safe_fetch($order, 'pass', 'type', 'is_string');
      if (!empty($v) && $v === 'none') {
        $desc->add_error("$print_name: Housing is only available to attendees purchasing an event pass. Select \"Not participating in housing\" to continue. Please contact sweetmohousing@gmail.com for more information or for help.");
        return;
      }
    }

    // EITHER
    $v = $this->safe_fetch($order, 'house', 'days', 'is_array');
    if (empty($v) || count($v) === 0) {
      $desc->add_error("$print_name: Please select at least one night, or choose \"Not participating in housing\".");
    } else {
      $pdays = array("thursday", "friday", "saturday", "sunday", "monday");
      $sdays = array();
      foreach ($pdays as $pd) {
        if (array_search($pd, $v) !== false) {
          $sdays[] = $pd;
        }
      }
      $long_desc[] = "nights: " . implode(", ", $sdays);
    }

    // good matches
    $v = $this->safe_fetch($order, 'house', 'matchyes', 'is_string');
    if (!empty($v)) {
      $long_desc[] = "good matches: $v";
    }

    // bad matches
    $v = $this->safe_fetch($order, 'house', 'matchno', 'is_string');
    if (!empty($v)) {
      $long_desc[] = "avoid matches: $v";
    }

    // attending
    $v = $this->safe_fetch($order, 'house', 'attending', 'is_array');
    if (empty($v) || count($v) === 0) {
      if ($role === 'guest') {
        $desc->add_error("$print_name: As a guest, please tell us what parts of the event you plan to attend.");
      } else {
        $long_desc[] = "attending none of the weekend";
      }
    } else {
      $long_desc[] = "attending " . implode(", ", $v);
    }

    // car details
    $v = $this->safe_fetch($order, 'house', 'car', 'is_string');
    if (!empty($v) && $v === 'yes') {
      $v = $this->safe_fetch($order, 'house', 'carseats', 'is_numeric');
      if ($v !== 0 && $v !== "0" && empty($v)) {
        $desc->add_error("$print_name: Please indicate how many seats are available in your car. 0 is permitted.");

      } else {
        $fv = floatval($v);
        if ($fv < 0) {
          $desc->add_error("$print_name: You have a negative number of seats in your car. Amazing.");
        }
        $long_desc[] = "car, $fv seats";
      }
    } else {
      $long_desc[] = "no car";
    }

    // GUESTS ONLY
    if ($role === 'guest') {
      // allergies
      $v = $this->safe_fetch($order, 'house', 'avoid', 'is_array');
      if (!empty($v)) {
        $long_desc[] = "avoid: " . implode(", ", $v);
      }

      // allergies
      $v = $this->safe_fetch($order, 'house', 'alsoavoid', 'is_array');
      if (!empty($v)) {
        $long_desc[] = "also avoid: $v";
      }
    }

    // HOSTS ONLY
    if ($role === 'host') {
      // address
      $v = $this->safe_fetch($order, 'house', 'address', 'is_string');
      if (empty($v)) {
        $desc->add_error("$print_name: as a host, please provide your address");
      } else {
        $long_desc[] = "address: $v";
      }

      // num guests
      $v = $this->safe_fetch($order, 'house', 'guestcount', 'is_numeric');
      if ($v !== 0 && $v !== "0" && empty($v)) {
        $desc->add_error("$print_name: as a host, please provide a number of guests");
      } else {
        $fv = floatval($v);
        if ($fv <= 0) {
          $desc->add_error("$print_name: The number of guests was misformatted or <= 0.");
        } else {
          $line_desc = "$line_desc ($fv guests)";
        }
      }

      // num parking
      $v = $this->safe_fetch($order, 'house', 'parking', 'is_numeric');
      if ($v !== 0 && $v !== "0" && empty($v)) {
        $desc->add_error("$print_name: as a host, please provide a number of parking spaces");
      } else {
        $fv = floatval($v);
        if ($fv < 0) {
          $desc->add_error("$print_name: You have a negative number of parking spaces. Amazing.");
        } else {
          $long_desc[] = "$fv parking spaces";
        }
      }

      $v = $this->safe_fetch($order, 'house', 'sleeping', 'is_string');
      if (empty($v)) {
        $desc->add_error("$print_name: as a host, please describe the sleeping arrangements");
      }

      // allergies
      $v = $this->safe_fetch($order, 'house', 'allergens', 'is_array');
      if (!empty($v)) {
        $long_desc[] = "allergens: " . implode(", ", $v);
      }
    }

    $desc->add_line_item($line_desc, 0.0);
    foreach ($long_desc as $v) {
      $desc->add_line_item(">  $v", 0.0);
    }
  }

  private function _describe_food($order, $desc) {
    $selection = $this->safe_fetch($order, 'food', 'type', 'is_string');

    $print_name = "Sunday Dinner";
    $schema = "food";
    $data = $this->_get_published_choice($print_name, $schema, $selection, $desc);
    if ($data === null) {
      // error added by _get_published_choice
      return;
    }

    $line_desc = "$print_name: {$data['longname']}";
    $desc->add_line_item($line_desc, $data['price']);
  }

  private function _describe_shirt($order, $desc) {
    $selection = $this->safe_fetch($order, 'shirt', 'type', 'is_string');

    $print_name = "Shirt";
    $schema = "shirts";
    $data = $this->_get_published_choice($print_name, $schema, $selection, $desc);
    if ($data === null) {
      // error added by _get_published_choice
      return;
    }

    $line_desc = $data['longname'];

    if ($data['id'] !== 'none') {
      $size = $this->safe_fetch($order, 'shirt', 'size', 'is_string');
      if (empty($size)) {
        $desc->add_error("$print_name: Please make a size selection");
        return;
      }

      $valid_sizes = explode(",", $data['sizes']);
      if (array_search($size, $valid_sizes) === false) {
        $desc->add_error("$print_name: Invalid size selection. Please reload the page and try again.");
        return;
      }

      $line_desc = "$line_desc ($size)";
    }

    $desc->add_line_item("$print_name: $line_desc", $data['price']);
  }

  private function _describe_choreo_comp($order, $desc) {
    // TODO
  }

  private function _describe_comp($order, $desc) {
    $selection = $this->safe_fetch($order, 'comp', 'type', 'is_string');

    $print_name = "Competition";
    $schema = "comps";
    $data = $this->_get_published_choice($print_name, $schema, $selection, $desc);
    if ($data === null) {
      // error added by _get_published_choice
      return;
    }

    $line_desc = $data['longname'];

    if ($data['id'] !== 'none') {
      $v = $this->safe_fetch($order, 'comp', 'role', 'is_string');
      if (empty($v)) {
        $desc->add_error("$print_name: Please make a role selection.");
        return;
      }
      if ($v !== 'lead' && $v !== 'follow') {
        $desc->add_error("$print_name: Invalid role selection. Please reload the page and try again.");
      }

      $line_desc = "$line_desc ($v)";
    }

    $desc->add_line_item("$print_name: $line_desc", $data['price']);
  }

  private function _describe_pass($order, $desc) {
    $selection = $this->safe_fetch($order, 'pass', 'type', 'is_string');

    $print_name = "Event Pass";
    $schema = "passes";
    $data = $this->_get_published_choice($print_name, $schema, $selection, $desc);
    if ($data === null) {
      // error added by _get_published_choice
      return;
    }

    if (!empty($data['code'])) {
      $v = $this->safe_fetch($order, 'pass', 'code', 'is_string');
      if (empty($v)) {
        $desc->add_error("$print_name: The selected pass requires an approval code. Please read the description carefully and try again.");
        return;
      } elseif($v !== $data['code']) {
        $desc->add_error("$print_name: Incorrect approval code. Please try again.");
        return;
      }
    }

    $desc->add_line_item($print_name . ": " . $data['longname'], $data['price']);
  }

  private function _get_published_choice($print_name, $schema_name, $selection, $desc) {
    if (empty($selection)) {
      $desc->add_error("$print_name: Please make a selection");
      return null;
    }

    $all_data = MDSC::instance()->data->get_data($schema_name);

    if (!isset($all_data[$selection])) {
      $desc->add_error("$print_name: Invalid selection (\"$selection\" not found). Please reload the page and try again.");
      return;
    }

    $data = $all_data[$selection];
    if (!$data['published']) {
      $desc->add_error("$print_name: Invalid selection (\"$selection\" no longer available). Please reload the page and try again.");
      return null;
    }

    return $data;
  }


  private function _describe_user($order, $desc) {
    // validate about you
    $v = $this->safe_fetch($order, 'user', 'firstname', 'is_string');
    if (empty($v)) {
      $desc->add_error("About You: first name is required.");
    } else {
      $desc->firstname = $v;
    }

    $v = $this->safe_fetch($order, 'user', 'lastname', 'is_string');
    if (empty($v)) {
      $desc->add_error("About You: last name is required.");
    } else {
      $desc->lastname = $v;
    }

    $v = $this->safe_fetch($order, 'user', 'email', 'is_string');
    if (empty($v)) {
      $desc->add_error("About You: email is required.");
    } elseif (!filter_var($v, FILTER_VALIDATE_EMAIL)) {
      $desc->add_error("About You: \"{$v}\" is not a valid email address");
    } else {
      $desc->email = $v;
    }
  }

  /**
   * When the theme is activated or reactivted. Must be idempotent.
   */
  public function configure() {
    global $wpdb;

    $sql = "CREATE TABLE " . $wpdb->prefix . "_sweetmo_registrations (\n";
    $sql .= "id INT NOT NULL AUTO_INCREMENT,\n";
    $sql .= "created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n";
    $sql .= "updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
    $sql .= "status VARCHAR(127) NOT NULL,\n";
    $sql .= "firstname VARCHAR(127) NOT NULL,\n";
    $sql .= "lastname VARCHAR(127) NOT NULL,\n";
    $sql .= "email VARCHAR(127) NOT NULL,\n";
    $sql .= "order_body TEXT NOT NULL,\n";
    $sql .= "order_summary TEXT NOT NULL,\n";
    $sql .= "price DECIMAL(6,2) NOT NULL,\n";
    $sql .= "PRIMARY KEY  (id)\n";
    $sql .= ") " . $wpdb->get_charset_collate() . ";";

    dbDelta($sql);
  }
}

// initialize
SweetMoRegistration::instance();

true;
