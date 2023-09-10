<?php
/*
Plugin Name: User Data Form
Description: A  WordPress plugin for collecting user details.
Version: 1.0
Author: webdevdk
*/

// Activation Hook
register_activation_hook(__FILE__, 'custom_form_plugin_activate');

function custom_form_plugin_activate() {
   
    add_user_data_menu_item();
    custom_form_create_table();
    custom_form_shortcode();
    
}

// Deactivation Hook
register_deactivation_hook(__FILE__, 'custom_form_plugin_deactivate');

function custom_form_plugin_deactivate() {
    // Deactivation code (if needed)

}
function wpdocs_selectively_enqueue_admin_script( $hook ) {
    wp_enqueue_style( 'admin_custom_form_style', plugin_dir_url( __FILE__ ) . 'css/admin_custom_form_style.css', array(), '1.0' );
}
add_action( 'admin_enqueue_scripts', 'wpdocs_selectively_enqueue_admin_script' );
add_action('wp_enqueue_scripts', 'enqueue_plugin_styles');
function enqueue_plugin_styles() {
    // Enqueue your stylesheet when the plugin is activated
    wp_enqueue_style('user-formdata-style', plugins_url('/css/user-formdata-style.css', __FILE__));
}
// Add a menu item in the WordPress admin dashboard
function add_user_data_menu_item() {
    // Add the menu item under the "Users" menu
    add_menu_page(
        'User Data',    // Page Title
        'User Data',    // Menu Title
        'manage_options', // Capability required to access
        'user-data-page', // Menu Slug
        'user_data_page_content', // Callback function to display page content
        'dashicons-admin-users' // Icon
    );
}

add_action('admin_menu', 'add_user_data_menu_item');
// Callback function to display the content of the "User Data" page
function user_data_page_content() {
    echo '<div class="wrap">';
    echo '<h2>User Data</h2>';
      // Display format selection options
    echo '<h3>Use shortcode "[custom_form]" to display form on frontend on any page.</h3>';
      echo '<h3>Choose Format Type:</h3>';
      echo '<ul class="user_data_ul">';
      echo '<li><a href="?page=user-data-page&format=format1">Format 1: </a>Display a table with columns "Name" and "Likes".</li>';
      echo '<li><a href="?page=user-data-page&format=format2">Format 2: </a>Display a table with columns "Name" and "Date Filled</li>';
      echo '<li><a href="?page=user-data-page&format=format3">Format 3: </a>Display only the "Name</li>';
      echo '</ul>';
    // Check which format is selected and display the corresponding data
    if (isset($_GET['format'])) {
      $format = $_GET['format'];
      
      // Display the table based on the selected format
      if ($format == 'format1') {
        display_format1();
      } elseif ($format == 'format2') {
        display_format2();
      } elseif ($format == 'format3') {
        display_format3();
      } else {
        display_format1();
        echo '<p>Invalid format selected.</p>';
      }
    } else {
      // If no format is selected, display a message
      display_format1();
    }
    echo '<h4>Use shortcode "[display_userdata format="x"]" to display form data submitted by user on frontend, where x="format type".</h4>';
    echo '<p> For Example: [display_userdata format="1"]';
    echo '</div>';
}

// Function to display user data in Format 1
function display_format1() {
    // display it in a table with "Name" and "Likes" columns
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_user_form_entries';
    // Query the form data from the database
    $users = $wpdb->get_results("SELECT * FROM $table_name");
    
    if (!empty($users)) {
        echo '<h4>Format 1</h4>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Name</th><th>Likes</th></tr></thead>';
        echo '<tbody>';
            foreach ($users as $user) {
                echo '<tr><td>' . $user->name . '</td><td>' . $user->likes . '</td></tr>';
            }
        echo '</tbody></table>';
    }else{
        echo 'No data available.';
    }
  }
  
  // Function to display user data in Format 2
  function display_format2() {
    // display it in a table with "Name" and "Date Filled" columns
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_user_form_entries';
    // Query the form data from the database
    $users = $wpdb->get_results("SELECT * FROM $table_name");
    if (!empty($users)) {
        echo '<h4>Format 2</h4>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Name</th><th>Date Filled</th></tr></thead>';
        echo '<tbody>';
        foreach ($users as $user) {
        echo '<tr><td>' . $user->name . '</td><td>' . $user->date_filled . '</td></tr>';
        }
        echo '</tbody></table>';
    }else{
        echo 'No data available.';
    }
  }
  
  // Function to display user data in Format 3
  function display_format3() {
    // display only the "Name" column
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_user_form_entries';
    // Query the form data from the database
    $users = $wpdb->get_results("SELECT * FROM $table_name");
    if (!empty($users)) {
        echo '<h4>Format 3</h4>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Name</th></tr></thead>';
        echo '<tbody>';
        foreach ($users as $user) {
        echo '<tr><td>' . $user->name . '</td></tr>';
        }
        echo '</tbody></table>';
    }else{
        echo 'No data available.';
    }
  }

function custom_form_create_table() {

    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_user_form_entries';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        age int NOT NULL,
        school varchar(255),
        profession varchar(255),
        likes int (255),
        date_filled datetime NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
function process_custom_form() {
    if (isset($_POST['custom_form_submit'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_user_form_entries';
        
        $name = sanitize_text_field($_POST['name']);
        $age = intval($_POST['age']);
        $like = intval($_POST['like']);
        $school = sanitize_text_field($_POST['school']);
        $profession = sanitize_text_field($_POST['profession']);
        $date_filled = current_time('mysql', 1);
        // Server-side validation
        $errors = array();

        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        if (empty($age)) {
            $errors[] = 'Age is required.';
        }
        if (empty($school)) {
            $errors[] = 'School is required.';
        }
        if (empty($profession)) {
            $errors[] = 'Profession is required.';
        }
        if (empty($like)) {
            $errors[] = 'Like is required.';
        }
        if (empty($errors)) {
            $wpdb->insert(
                $table_name,
                array(
                    'name' => $name,
                    'age' => $age,
                    'school' => $school,
                    'profession' => $profession,
                    'likes' => $like,
                    'date_filled' => $date_filled,
                )
            );
            $message = 'Form submitted successfully.';
            set_transient('form_submission_message', $message, 10); // Store message for 10 seconds
            // Redirect back to the same page
            wp_redirect(add_query_arg('message', 'success', get_permalink()));
            exit;
        }else{
            $message = $errors;
            set_transient('form_submission_error', $message, 10); // Store message for 10 seconds
            // Redirect back to the same page
            wp_redirect(add_query_arg('message', 'error', get_permalink()));
            exit;
            echo '<div class="error"><ul>';
            foreach ($errors as $error) {
                echo '<li>' . esc_html($error) . '</li>';
            }
            echo '</ul></div>';
        }
        
       
    }
}
add_action('init', 'process_custom_form');

function custom_form_shortcode() {
    ob_start();
     // Check if the form was submitted
            $form_submission_message = get_transient('form_submission_message');
            if (!empty($form_submission_message)) {
                echo '<div class="message">' . esc_html($form_submission_message) . '</div>';
            }
            $form_submission_error = get_transient('form_submission_error');
            if (!empty($form_submission_error)) {
                echo '<div class="error"><ul>';
                foreach ($form_submission_error as $error) {
                    echo '<li>' . esc_html($error) . '</li>';
                }
                echo '</ul></div>';
              }
    
     ?>
    <form id="custom-form-data" name="custom-form-data" class="custom-form-data" method="post">  
    <div class="form-group">
            <label for="name">Name:</label>
            <input class="form-control" type="text" name="name" required><br>
        </div>   
        <div class="form-group">    
            <label for="age">Age:</label>
            <input class="form-control" type="number" name="age" min="10" max="100" required><br>
        </div>
        <div class="form-group">    
            <label for="age">School:</label>
            <input class="form-control" type="text" name="school" required><br>
        </div>
        <div class="form-group">   
            <label for="profession">Current Profession:</label>
            <select class="form-control" name="profession" id="profession" required>
                <option value="">Please choose a profession</option>
                <option value="Student">Student</option>
                <option value="Employed">Employed</option>
                <option value="Self-Employed">Self-Employed</option>
                <option value="Unemployed">Unemployed</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="form-group">    
            <label for="age">Likes:</label>
            <input class="form-control" type="number" name="like" required>
        </div>
        <div class="form-group"> 
            <input class="form-group-btn" type="submit" name="custom_form_submit" value="Submit">
        </div>    
    </form>
   
    <?php

    return ob_get_clean();
}
add_shortcode('custom_form', 'custom_form_shortcode');

// Shortcode function to display user data.
function display_userdata_shortcode($atts) {
    // Default format is 1.
    $format = isset($atts['format']) ? intval($atts['format']) : 1;
    // Define different formats.

    // Check if the specified format exists.
        if($format == 1){
            sprintf(display_format1());
        }
        elseif($format == 2){
            sprintf(display_format2());
        }
        elseif($format == 3){
            sprintf(display_format3());
        }
        else{
            return '<p>Invalid format. Please use format="1", "2", or "3".</p>';
        }
    
}

// Register the shortcode.
add_shortcode('display_userdata', 'display_userdata_shortcode');

?>