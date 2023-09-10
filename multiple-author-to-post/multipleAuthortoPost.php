<?php
/**
 * Plugin Name: Multiple Author To Post
 * Description: Multiple author To a single post
 * Version: 1.0.0
 * Author: dkwebdev
 */

 if(!defined("ABSPATH")) exit;
 Class multipleAuthotoPost{

  function __construct(){
	
    	add_action( 'add_meta_boxes', array( $this, 'add_contributor_meta_box' ) );
    	add_action( 'save_post',      array( $this, 'save_contributor_meta') );
	
	add_filter('the_content',  array( $this,'modify_post_content'));
  }
 /**
	 * Adds the meta box container.
	 */
	public function add_contributor_meta_box( $post_type ) {
		// Limit meta box to certain post types.
		$post_types = array( 'post');
		
		if ( in_array( $post_type, $post_types ) ) {
			add_meta_box(
				'autho_meta_box',
				__( 'Add Contributor', 'textdomain' ),
				array( $this, 'render_contributor_meta_box_content' ),
				$post_type,
				'advanced',
				'high'
			);
		}
	
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_contributor_meta( $post_id ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['myplugin_inner_custom_box_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['myplugin_inner_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'myplugin_inner_custom_box' ) ) {
			return $post_id;
		}

		/*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}
		/* OK, it's safe for us to save the data now. */

		// Sanitize the user input.
		$mydata = $_POST['contributor'];
		// Update the meta field.
		update_post_meta( $post_id, '_post_multiple_contributor', $mydata );
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_contributor_meta_box_content( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );

		// Display the form, using the current value.
		$roles = array( 'publish_posts' );
		foreach( wp_roles()->roles as $role_slug => $role ) {
			if( ! empty( $role['capabilities']['publish_posts'] ) ) {
				$roles[] = $role_slug;
			}
		}
		$users = get_users( array( 'role__in' => $roles ) );
		
		
	
		 $postmeta = get_post_meta( $post->ID, '_post_multiple_contributor', true );
		 foreach ( $users as $user) {
			
			if($postmeta){
				if (in_array( $user->ID, $postmeta ) ) {
					$checked = 'checked="checked"';
				} else {
					$checked = null;
				}
				
			}
			else{
				$checked = null;
			}
			if ( is_user_logged_in() ) {
					$current_user = get_current_user_id();
					if($current_user == $user->ID){
						$disabled = 'readonly';
					}
					else{
						$disabled = '';
					}
			} 
			?>
			<p>
				
				<input  type="checkbox" name="contributor[]" value="<?php echo $user->ID;?>" 
				<?php echo $checked.' '.$disabled; ?> />
				<?php echo $user->display_name." (". $user->roles[0].")";?>
			</p>
	
			<?php
		}
		
		?>
		
		<?php
	}

	public function modify_post_content($content) {
    // Check if it's a single post
	global $post;
	$postmeta = get_post_meta( $post->ID, '_post_multiple_contributor', true );
	
		if (is_single()) {
			$content .= "<ul>";
			foreach($postmeta as $authormeta){
				$userid = get_userdata($authormeta);
				$author_url = site_url('/author/').$userid->user_login;
				$author_name = $userid->display_name;
				$author_img = get_avatar_url($userid->ID, ['size' => '60']);

			// Add your modification here
				$content .= "<li class='author_list' style='list-style-type:none;'>";
				$content .= "<img src='".$author_img."' )/>&nbsp;&nbsp;";
				$content .= "<a href='".$author_url."'>";
				$content .= "<a href='".$author_url."'>";
				$content .= $author_name;
				$content .= "</li>";
			}
			$content .= "</ul>";
			
		}
    	return $content;
	}




 }
 $multipleAuthotoPost = new multipleAuthotoPost();