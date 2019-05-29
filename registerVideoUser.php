<?php
/**
* extend wooCommerce funcionality
* create new user role = 'video' if video purchase is made
* work done for paincareclinic.co.uk
* Dec 2018
* Greg Birch
*/
?>

<?php

// REGISTER VIDEO USER FROM THANK YOU SCREEN -  //

//call wc_register_guests() function on the thank you page
add_action( 'woocommerce_thankyou', 'wc_register_video_user', 10, 1 ); 

function wc_register_video_user( $order_id ) {
  // get the order & item data
  $order    =   new WC_Order($order_id);
  $items    =   $order->get_items();
  $slug_arr =   array();
  
  // get customer details 
  $ordernumber      =   $order->get_id();
  $billing_email    =   get_post_meta($order_id, '_billing_email', true);
  $first_name       =   get_post_meta($order_id, '_billing_first_name', true);
  $last_name        =   get_post_meta($order_id, '_billing_last_name', true);
  $postcode         =   get_post_meta($order_id, '_billing_postcode', true);
  $company_name     =   get_post_meta($order_id, '_billing_company', true);
  
  // get a list of product categories contained in this order 
  foreach ( $items as $item ) {
    $product_id =   $item['product_id'];
    $terms      =   get_the_terms( $product_id, 'product_cat' );
    $catslug    =   $terms[0]->slug;
    array_push($slug_arr, $catslug );
  }
  // does the list of categories include videos?
  if (in_array( "videos", $slug_arr )) {
      echo '<h2>Your order contains a <b>Video Package</b> purchase</h2>';
  
      // Display customer data for testing
      /*
      echo('order number = ' . $ordernumber . '<br>');
      echo('First name = ' . $first_name . '<br>');
      echo('Last name = ' . $last_name . '<br>');
      echo('Postcode = ' . $postcode . '<br>');
      echo('Email = ' . $billing_email . '<br>');
      echo('Company = ' . $company_name . '<br>');
      */
      // check if there are any users with this email or username
      $email = email_exists( $billing_email );  
      $user = username_exists( $billing_email );
      
      if( $user == false && $email == false ){
        echo '<h3>You are now registered with Pain Care Clinic as a Video Subscriber. Your login details are below</h3>';
        $new_password = wp_generate_password();
        // prepend company name to username if company name exists
        ($company_name) ? $company_name .= '_' : $company_name = '' ;   
        $new_username = strtolower($company_name . $first_name . '_' . $last_name);  
        $userdata = array(
            'user_login'  => $new_username, 
            'user_email'  => $billing_email,
            'first_name'  => $first_name, 
            'last_name'   => $last_name,
            'user_pass'   => $new_password,  
            'role'        => 'videos'  
        );
        //create user & assign video role
        $user_id = wp_insert_user( $userdata );
        update_user_meta( $user_id, 'videos', 'yes' );
      
        // On success... 
        if ( ! is_wp_error( $user_id ) ) {
            echo '<h4>You may use these details to access the <a href="https://paincareclinic.co.uk/living-pain-free-online">Living Pain Free video package.</a> Please check your inbox for details.</h4>';
            echo '<p><b>Your username is: </b>' . $userdata['user_login'] . '<br><b>Your password is: </b>' . $userdata['user_pass'] . '</p><p>These details have been sent to: ' . $userdata['user_email'] . '</p>';
                                            
            $mailResult = wp_mail($userdata['user_email'], "Welcome to Pain Care Clinic Videos", "Dear " . $userdata['first_name'] . "\r\n\r\n" . "Welcome to Living Pain Free Online.\r\n\r\n Your Usename is: " . $userdata['user_login'] . "\r\n" . "Your Password is: " . $userdata['user_pass'] . "\r\n\r\n" . "You can change your password once you have logged in for the first time by clicking on 'My Account' \r\n" . "Please keep these details secure. \r\n\r\n" . "To access Living Pain Free Online please go to the website page and sign in: https://paincareclinic.co.uk/living-pain-free-online.\r\n\r\nWe hope you enjoy Living Pain Free!"  );
           }
      } // end 'if this user doesn't previously exist' conditional 
  } // end $slug_arr contains 'video' conditional 
  else {
    // not a video purchase code
  }
  
} // end wc_register_video_user()


?>
