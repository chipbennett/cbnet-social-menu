<?php
/*
 * Plugin Name:   cbnet Social Menu
 * Plugin URI:    https://www.github.com/chipbennett/cbnet-social-menu/
 * Description:   Manage and display your social network profile links using a custom navigation menu
 * Version:       1.0
 * Author:        chipbennett
 * Author URI:    http://www.chipbennett.net/
 *
 * License:       GNU General Public License, v2 (or newer)
 * License URI:   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * cbnet Social Menu WordPress Plugin, Copyright (C) 2014 Chip Bennett,
 * Released under the GNU General Public License, version 2.0 (or newer)
 */
 
/**
 * @todo	support other icon sets
 * 
 * Possible icon sets (need to verify licenses):
 * Zurb Foundation Icons: http://zurb.com/playground/foundation-icons (MIT)
 * JustVector Social Icons: http://blog.martianwabbit.com/post/4344642365.html (Free Art License)
 * weloveiconfonts: http://weloveiconfonts.com/
 * 
 * @todo	add Widget option for Icon Size
 */
 
/**
 * Load Plugin textdomain
 */
function cbnet_social_menu_load_textdomain() {
	load_plugin_textdomain( 'cbnet-social-menu', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}
// Load Plugin textdomain
add_action( 'plugins_loaded', 'cbnet_social_menu_load_textdomain' );

/**
 * Register Social Nav Menu Theme Location
 * 
 * Only register a "social" Nav Menu Theme Location if 
 * the current Theme does not already register one.
 */
function cbnet_social_menu_register_theme_location() {
	if ( ! array_key_exists( 'social', get_registered_nav_menus() ) ) {
		register_nav_menu( 'social', __( 'Social Profile Links', 'cbnet-social-menu' ) );
	}
}
add_action( 'after_setup_theme', 'cbnet_social_menu_register_theme_location' );

/**
 * Add skype: and callto: to allowed protocols
 *
 * Filter kses_allowed_protocols to add skype: and callto: as 
 * valid href protocols. This is needed to allow Skype profile 
 * links in the Social nav menu.
 */
function cbnet_social_menu_filter_kses_allowed_protocols( $protocols ) {
    return array_merge( $protocols, array( 'skype' ) );
}
add_filter( 'kses_allowed_protocols', 'cbnet_social_menu_filter_kses_allowed_protocols' );


/**
 * Enqueue social icon stylesheets
 */
function cbnet_social_menu_stylesheets() {
		
	if ( ! is_admin() && is_active_widget( false, false, 'cbnet_social_menu_widget' ) ) {
		// Enqueue stylesheets
		wp_enqueue_style( 'cbnet-social-menu', plugins_url( 'css/cbnet-social-menu.css', __FILE__ ) );
		foreach ( cbnet_social_menu_valid_icon_fonts() as $slug => $name ) {
			wp_enqueue_style( 'cbnet-social-menu-font-' . $slug, plugins_url( 'css/' .  $slug . '.css', __FILE__ ) );
		}
	}	
}
add_action( 'wp_enqueue_scripts', 'cbnet_social_menu_stylesheets' );


/**
 * Register the Social Icons Widget
 */
function cbnet_social_menu_register_widgets() {
	register_widget( 'cbnet_social_menu_widget' );
}
add_action( 'widgets_init', 'cbnet_social_menu_register_widgets' );



/**
 * Define Social Icons Custom Widget 
 * 
 * @uses	cbnet_social_menu_social_icons()
 * 
 * @since	WordPress 2.8
 */
class cbnet_social_menu_widget extends WP_Widget {

    function cbnet_social_menu_widget() {
        $widget_ops = array('classname' => 'widget-cbnet-social-menu', 'description' => __( 'Widget to display social network profile link icons', 'cbnet-social-menu' ) );
        $this->WP_Widget( 'cbnet_social_menu_widget', __( 'cbnet Social Profile Links', 'cbnet-social-menu' ), $widget_ops);
    }

    function widget( $args, $instance ) {
        extract($args);
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? false : $instance['title'] );
		$font = ( isset( $instance['font'] ) && array_key_exists( $instance['font'], cbnet_social_menu_valid_icon_fonts() ) ? $instance['font'] : 'genericons' );

        echo $before_widget;
        if ( $title ) {
            echo $before_title . $title . $after_title;
		}
?>

<!-- Begin Social Icons -->
<ul class="leftcolcatlist">
	<?php echo cbnet_social_menu_get_social_icons( $font ); ?>
</ul>
<!-- End Social Icons -->

<?php
        echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
		$instance['font'] = ( array_key_exists( $new_instance['font'], cbnet_social_menu_valid_icon_fonts() ) ? $new_instance['font'] : $instance['font'] );

        return $instance;
    }

    function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'font' => 'genericons' ) );
        $title = strip_tags( $instance['title'] );
		$font = strip_tags( $instance['font'] );
?>
            <p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title', 'cbnet-social-menu' ); ?>:</label> 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</p>
			
            <p>
			<label for="<?php echo $this->get_field_id('font'); ?>"><?php _e( 'Icon Font', 'cbnet-social-menu' ); ?>:</label> 
			<select class="widefat" id="<?php echo $this->get_field_id('font'); ?>" name="<?php echo $this->get_field_name('font'); ?>">
				<?php
				foreach ( cbnet_social_menu_valid_icon_fonts() as $font_slug => $font_name ) {
					?>
					<option <?php selected( $font_slug == $font ); ?> value="<?php echo $font_slug; ?>"><?php echo $font_name; ?></option>
					<?php
				}
				?>
			</select>
			</p>
<?php
    }
	
}


/**
 * Display Social Icons
 */
function cbnet_social_menu_get_social_icons( $font = 'genericons' ) {

	$font = ( array_key_exists( $font, cbnet_social_menu_valid_icon_fonts() ) ? $font : 'genericons' );
	
	if ( has_nav_menu( 'social' ) ) {
		?>
		<div class="sidebar-social-icons">
			<?php 
			wp_nav_menu( array(
				'theme_location' => 'social',
				'container'       => 'div',
				'container_id'    => 'menu-social',
				'container_class' => 'menu',
				'menu_id'         => 'menu-social-items',
				'menu_class'      => 'menu-items ' . $font,
				'depth'           => 1,
				'link_before'     => '<span class="screen-reader-text">',
				'link_after'      => '</span>',
				'fallback_cb'     => '',
			) );
			?>
		</div>
		<?php
	}
}

/**
 * Return valid social icon fonts
 */
function cbnet_social_menu_valid_icon_fonts() {
	return array(
		'genericons' => __( 'Genericons', 'cbnet-social-menu' ),
		'font-awesome' => __( 'Font Awesome', 'cbnet-social-menu' ),
		'zurb-foundation' => __( 'Zurb Foundation Icons', 'cbnet-social-menu' ),
		'justvector' => __( 'JustVector Social Icons', 'cbnet-social-menu' )
	);
}