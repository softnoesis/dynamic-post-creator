<?php
/*
Plugin Name: Dynamic Post Creator
Description: Create custom posts with ease using Dynamic Post Creator. Effortlessly manage and customize your content types from the WordPress admin. Tailor your posts to fit your unique needs and unleash your creativity like never before.
Version: 1.0.0
Author: Softnoesis Pvt. Ltd.
Author URI: http://www.softnoesis.com/
Text Domain: dynamic-post-creator
Domain Path: /lang
*/

/* Start coding for plugin */
class Sndpc {
	private $dir;
	private $path;
	private $version;

	/* Call to Constructor */
	function __construct() {
		$this->dir     = plugins_url( '', __FILE__ );
		$this->path    = plugin_dir_path( __FILE__ );

		// start coding for actions
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'sndpc_create_custom_post_types' ) );
		add_action( 'admin_menu', array( $this, 'sndpc_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'sndpc_styles' ) );
		add_action( 'add_meta_boxes', array( $this, 'sndpc_create_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'sndpc_save_post' ) );
		add_action( 'admin_init', array( $this, 'sndpc_plugin_settings_flush_rewrite' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'sndpc_custom_columns' ), 10, 2 );
		add_action( 'manage_posts_custom_column', array( $this, 'sndpc_tax_custom_columns' ), 10, 2 );
		add_action( 'admin_footer', array( $this, 'sndpc_admin_footer' ) );
		add_action( 'wp_prepare_attachment_for_js', array( $this, 'wp_prepare_attachment_for_js' ), 10, 3 );

		// start coding for filters
		add_filter( 'manage_sndpc_posts_columns', array( $this, 'sndpc_change_columns' ) );
		add_filter( 'manage_edit-sndpc_sortable_columns', array( $this, 'sndpc_sortable_columns' ) );
		add_filter( 'manage_sndpc_tax_posts_columns', array( $this, 'sndpc_tax_change_columns' ) );
		add_filter( 'manage_edit-sndpc_tax_sortable_columns', array( $this, 'sndpc_tax_sortable_columns' ) );
		add_filter( 'post_updated_messages', array( $this, 'sndpc_post_updated_messages' ) );

		// start coding for hooks
		register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
		register_activation_hook( __FILE__, array( $this, 'sndpc_plugin_activate_flush_rewrite' ) );

		// start coding for set textdomain
		load_plugin_textdomain( 'dynamic-post-creator', false, basename( dirname( __FILE__ ) ) . '/lang' );
	}

	/* Start code for initialize plugi */
	public function init() {
		$labels = array(
			'name'               => __( 'Dyanmic Post Creator', 'dynamic-post-creator' ),
			'singular_name'      => __( 'Custom Post Type', 'dynamic-post-creator' ),
			'add_new'            => __( 'Add New', 'dynamic-post-creator' ),
			'add_new_item'       => __( 'Add New Custom Post Type', 'dynamic-post-creator' ),
			'edit_item'          => __( 'Edit Custom Post Type', 'dynamic-post-creator' ),
			'new_item'           => __( 'New Custom Post Type', 'dynamic-post-creator' ),
			'view_item'          => __( 'View Custom Post Type', 'dynamic-post-creator' ),
			'search_items'       => __( 'Search Custom Post Types', 'dynamic-post-creator' ),
			'not_found'          => __( 'No Custom Post Types found', 'dynamic-post-creator' ),
			'not_found_in_trash' => __( 'No Custom Post Types found in Trash', 'dynamic-post-creator' ),
		);

		register_post_type(
			'sndpc',
			array(
				'labels'          => $labels,
				'public'          => false,
				'show_ui'         => true,
				'_builtin'        => false,
				'capability_type' => 'page',
				'hierarchical'    => false,
				'rewrite'         => false,
				'query_var'       => 'sndpc',
				'supports'        => array(
					'title',
				),
				'show_in_menu'    => false,
			)
		);

		if ( function_exists( 'add_image_size' ) && ! defined( 'SNDPC_DONT_GENERATE_ICON' ) ) {
			add_image_size( 'sndpc_icon', 16, 16, true );
		}
	}

	/* start code for add admin menu items*/
	public function sndpc_admin_menu() {
		add_menu_page( __( 'SNDCP Maker', 'dynamic-post-creator' ), __( 'Dynamic Post Creator', 'dynamic-post-creator' ), 'manage_options', 'edit.php?post_type=sndpc', '', 'dashicons-layout' );
	}

	/*  Start code for register admin styles */
	public function sndpc_styles( $hook ) {
		// register sndpc-style style
		if ( 'edit.php' == $hook && isset( $_GET['post_type'] ) && ( 'sndpc' == $_GET['post_type'] || 'sndpc_tax' == $_GET['post_type'] ) ) {
			wp_register_style( 'sndpc_admin_styles', $this->dir . '/css/sndpc-style.css' );
			wp_enqueue_style( 'sndpc_admin_styles' );

			wp_register_script( 'sndpc_admin_js', $this->dir . '/js/sndpc-style.js', 'jquery', '0.0.1', true );
			wp_enqueue_script( 'sndpc_admin_js' );

			wp_enqueue_script( array( 'jquery', 'thickbox' ) );
			wp_enqueue_style( array( 'thickbox' ) );
		}

		if ( ( 'post-new.php' == $hook && isset( $_GET['post_type'] ) && 'sndpc' == $_GET['post_type'] ) || ( 'post.php' == $hook && isset( $_GET['post'] ) && get_post_type( $_GET['post'] ) == 'sndpc' ) || ( $hook == 'post-new.php' && isset( $_GET['post_type'] ) && 'sndpc_tax' == $_GET['post_type'] ) || ( 'post.php' == $hook && isset( $_GET['post'] ) && get_post_type( $_GET['post'] ) == 'sndpc_tax' ) ) {
			wp_register_style( 'sndpc_add_edit_styles', $this->dir . '/css/sndpc-style-edit.css' );
			wp_enqueue_style( 'sndpc_add_edit_styles' );

			wp_register_script( 'sndpc_admin__add_edit_js', $this->dir . '/js/sndpc-style-edit.js', 'jquery', '0.0.1', true );
			wp_enqueue_script( 'sndpc_admin__add_edit_js' );

			wp_enqueue_media();
		}
	}

	/* start code for create custom post types */
	public function sndpc_create_custom_post_types() {
		$sndpcs     = array();
		$get_sndpc        = array(
			'numberposts'      => -1,
			'post_type'        => 'sndpc',
			'post_status'      => 'publish',
			'suppress_filters' => false,
		);
		$sndpc_post_types = get_posts( $get_sndpc );
		if ( $sndpc_post_types ) {
			foreach ( $sndpc_post_types as $sndpc ) {
				$sndpc_meta = get_post_meta( $sndpc->ID, '', true );
				$sndpc_name = ( array_key_exists( 'sndpc_name', $sndpc_meta ) && $sndpc_meta['sndpc_name'][0] ? esc_html( $sndpc_meta['sndpc_name'][0] ) : 'no_name' );
				$sndpc_label = ( array_key_exists( 'sndpc_label', $sndpc_meta ) && $sndpc_meta['sndpc_label'][0] ? esc_html( $sndpc_meta['sndpc_label'][0] ) : $sndpc_name );
				$sndpc_singular_name = ( array_key_exists( 'sndpc_singular_name', $sndpc_meta ) && $sndpc_meta['sndpc_singular_name'][0] ? esc_html( $sndpc_meta['sndpc_singular_name'][0] ) : $sndpc_label );
				$sndpc_description   = ( array_key_exists( 'sndpc_description', $sndpc_meta ) && $sndpc_meta['sndpc_description'][0] ? $sndpc_meta['sndpc_description'][0] : '' );
				$sndpc_icon_url = ( array_key_exists( 'sndpc_icon_url', $sndpc_meta ) && $sndpc_meta['sndpc_icon_url'][0] ? $sndpc_meta['sndpc_icon_url'][0] : false );
				$sndpc_icon_slug = ( array_key_exists( 'sndpc_icon_slug', $sndpc_meta ) && $sndpc_meta['sndpc_icon_slug'][0] ? $sndpc_meta['sndpc_icon_slug'][0] : false );

				if ( ! empty( $sndpc_icon_slug ) ) {
					$sndpc_icon_name = $sndpc_icon_slug;
				} else {
					$sndpc_icon_name = $sndpc_icon_url;
				}

				$sndpc_custom_rewrite_slug = ( array_key_exists( 'sndpc_custom_rewrite_slug', $sndpc_meta ) && $sndpc_meta['sndpc_custom_rewrite_slug'][0] ? esc_html( $sndpc_meta['sndpc_custom_rewrite_slug'][0] ) : $sndpc_name );
				$sndpc_menu_position       = ( array_key_exists( 'sndpc_menu_position', $sndpc_meta ) && $sndpc_meta['sndpc_menu_position'][0] ? (int) $sndpc_meta['sndpc_menu_position'][0] : null );

				$sndpc_public              = ( array_key_exists( 'sndpc_public', $sndpc_meta ) && $sndpc_meta['sndpc_public'][0] == '1' ? true : false );
				$sndpc_show_ui             = ( array_key_exists( 'sndpc_show_ui', $sndpc_meta ) && $sndpc_meta['sndpc_show_ui'][0] == '1' ? true : false );
				$sndpc_has_archive         = ( array_key_exists( 'sndpc_has_archive', $sndpc_meta ) && $sndpc_meta['sndpc_has_archive'][0] == '1' ? true : false );
				$sndpc_exclude_from_search = ( array_key_exists( 'sndpc_exclude_from_search', $sndpc_meta ) && $sndpc_meta['sndpc_exclude_from_search'][0] == '1' ? true : false );
				$sndpc_capability_type     = ( array_key_exists( 'sndpc_capability_type', $sndpc_meta ) && $sndpc_meta['sndpc_capability_type'][0] ? $sndpc_meta['sndpc_capability_type'][0] : 'post' );
				$sndpc_hierarchical        = ( array_key_exists( 'sndpc_hierarchical', $sndpc_meta ) && $sndpc_meta['sndpc_hierarchical'][0] == '1' ? true : false );
				$sndpc_rewrite             = ( array_key_exists( 'sndpc_rewrite', $sndpc_meta ) && $sndpc_meta['sndpc_rewrite'][0] == '1' ? true : false );
				$sndpc_withfront           = ( array_key_exists( 'sndpc_withfront', $sndpc_meta ) && $sndpc_meta['sndpc_withfront'][0] == '1' ? true : false );
				$sndpc_feeds               = ( array_key_exists( 'sndpc_feeds', $sndpc_meta ) && $sndpc_meta['sndpc_feeds'][0] == '1' ? true : false );
				$sndpc_pages               = ( array_key_exists( 'sndpc_pages', $sndpc_meta ) && $sndpc_meta['sndpc_pages'][0] == '1' ? true : false );
				$sndpc_query_var           = ( array_key_exists( 'sndpc_query_var', $sndpc_meta ) && $sndpc_meta['sndpc_query_var'][0] == '1' ? true : false );
				$sndpc_show_in_rest        = ( array_key_exists( 'sndpc_show_in_rest', $sndpc_meta ) && $sndpc_meta['sndpc_show_in_rest'][0] == '1' ? true : false );

				if ( ! array_key_exists( 'sndpc_publicly_queryable', $sndpc_meta ) ) {
					$sndpc_publicly_queryable = true;
				} elseif ( $sndpc_meta['sndpc_publicly_queryable'][0] == '1' ) {
					$sndpc_publicly_queryable = true;
				} else {
					$sndpc_publicly_queryable = false;
				}

				$sndpc_show_in_menu = ( array_key_exists( 'sndpc_show_in_menu', $sndpc_meta ) && $sndpc_meta['sndpc_show_in_menu'][0] == '1' ? true : false );
				$sndpc_supports           = ( array_key_exists( 'sndpc_supports', $sndpc_meta ) && $sndpc_meta['sndpc_supports'][0] ? $sndpc_meta['sndpc_supports'][0] : 'a:2:{i:0;s:5:"title";i:1;s:6:"editor";}' );
				$sndpc_builtin_taxonomies = ( array_key_exists( 'sndpc_builtin_taxonomies', $sndpc_meta ) && $sndpc_meta['sndpc_builtin_taxonomies'][0] ? $sndpc_meta['sndpc_builtin_taxonomies'][0] : 'a:0:{}' );

				$sndpc_rewrite_options = array();
				if ( $sndpc_rewrite ) {
					$sndpc_rewrite_options['slug'] = _x( $sndpc_custom_rewrite_slug, 'URL Slug', 'dynamic-post-creator' );
				}

				$sndpc_rewrite_options['with_front'] = $sndpc_withfront;

				if ( $sndpc_feeds ) {
					$sndpc_rewrite_options['feeds'] = $sndpc_feeds;
				}
				if ( $sndpc_pages ) {
					$sndpc_rewrite_options['pages'] = $sndpc_pages;
				}

				$sndpcs[] = array(
					'sndpc_id'                  => $sndpc->ID,
					'sndpc_name'                => $sndpc_name,
					'sndpc_label'               => $sndpc_label,
					'sndpc_singular_name'       => $sndpc_singular_name,
					'sndpc_description'         => $sndpc_description,
					'sndpc_icon_name'           => $sndpc_icon_name,
					'sndpc_custom_rewrite_slug' => $sndpc_custom_rewrite_slug,
					'sndpc_menu_position'       => $sndpc_menu_position,
					'sndpc_public'              => (bool) $sndpc_public,
					'sndpc_show_ui'             => (bool) $sndpc_show_ui,
					'sndpc_has_archive'         => (bool) $sndpc_has_archive,
					'sndpc_exclude_from_search' => (bool) $sndpc_exclude_from_search,
					'sndpc_capability_type'     => $sndpc_capability_type,
					'sndpc_hierarchical'        => (bool) $sndpc_hierarchical,
					'sndpc_rewrite'             => $sndpc_rewrite_options,
					'sndpc_query_var'           => (bool) $sndpc_query_var,
					'sndpc_show_in_rest'        => (bool) $sndpc_show_in_rest,
					'sndpc_publicly_queryable'  => (bool) $sndpc_publicly_queryable,
					'sndpc_show_in_menu'        => (bool) $sndpc_show_in_menu,
					'sndpc_supports'            => unserialize( $sndpc_supports ),
					'sndpc_builtin_taxonomies'  => unserialize( $sndpc_builtin_taxonomies ),
				);

				if ( is_array( $sndpcs ) ) {
					foreach ( $sndpcs as $sndpc_post_type ) {
						$labels = array(
							'name'               => __( $sndpc_post_type['sndpc_label'], 'dynamic-post-creator' ),
							'singular_name'      => __( $sndpc_post_type['sndpc_singular_name'], 'dynamic-post-creator' ),
							'add_new'            => __( 'Add New', 'dynamic-post-creator' ),
							'add_new_item'       => __( 'Add New ' . $sndpc_post_type['sndpc_singular_name'], 'dynamic-post-creator' ),
							'edit_item'          => __( 'Edit ' . $sndpc_post_type['sndpc_singular_name'], 'dynamic-post-creator' ),
							'new_item'           => __( 'New ' . $sndpc_post_type['sndpc_singular_name'], 'dynamic-post-creator' ),
							'view_item'          => __( 'View ' . $sndpc_post_type['sndpc_singular_name'], 'dynamic-post-creator' ),
							'search_items'       => __( 'Search ' . $sndpc_post_type['sndpc_label'], 'dynamic-post-creator' ),
							'not_found'          => __( 'No ' . $sndpc_post_type['sndpc_label'] . ' found', 'dynamic-post-creator' ),
							'not_found_in_trash' => __( 'No ' . $sndpc_post_type['sndpc_label'] . ' found in Trash', 'dynamic-post-creator' ),
						);

						$args = array(
							'labels'              => $labels,
							'description'         => $sndpc_post_type['sndpc_description'],
							'menu_icon'           => $sndpc_post_type['sndpc_icon_name'],
							'rewrite'             => $sndpc_post_type['sndpc_rewrite'],
							'menu_position'       => $sndpc_post_type['sndpc_menu_position'],
							'public'              => $sndpc_post_type['sndpc_public'],
							'show_ui'             => $sndpc_post_type['sndpc_show_ui'],
							'has_archive'         => $sndpc_post_type['sndpc_has_archive'],
							'exclude_from_search' => $sndpc_post_type['sndpc_exclude_from_search'],
							'capability_type'     => $sndpc_post_type['sndpc_capability_type'],
							'hierarchical'        => $sndpc_post_type['sndpc_hierarchical'],
							'show_in_menu'        => $sndpc_post_type['sndpc_show_in_menu'],
							'query_var'           => $sndpc_post_type['sndpc_query_var'],
							'show_in_rest'        => $sndpc_post_type['sndpc_show_in_rest'],
							'publicly_queryable'  => $sndpc_post_type['sndpc_publicly_queryable'],
							'_builtin'            => false,
							'supports'            => $sndpc_post_type['sndpc_supports'],
							'taxonomies'          => $sndpc_post_type['sndpc_builtin_taxonomies'],
						);
						if ( $sndpc_post_type['sndpc_name'] != 'no_name' ) {
							register_post_type( $sndpc_post_type['sndpc_name'], $args );
						}
					}
				}
			}
		}
	}

	/* start code for create admin meta boxes */
	public function sndpc_create_meta_boxes() {
		// add options meta box
		add_meta_box(
			'sndpc_options',
			__( 'Dynamic Post Creator Options', 'dynamic-post-creator' ),
			array( $this, 'sndpc_meta_box' ),
			'sndpc',
			'advanced',
			'high'
		);
		add_meta_box(
			'sndpc_tax_options',
			__( 'Options', 'dynamic-post-creator' ),
			array( $this, 'sndpc_tax_meta_box' ),
			'sndpc_tax',
			'advanced',
			'high'
		);
	}

	/* start code for create custom post meta box */
	public function sndpc_meta_box( $post ) {
		$values = get_post_custom( $post->ID );
		$sndpc_name          = isset( $values['sndpc_name'] ) ? esc_attr( $values['sndpc_name'][0] ) : '';
		$sndpc_label         = isset( $values['sndpc_label'] ) ? esc_attr( $values['sndpc_label'][0] ) : '';
		$sndpc_singular_name = isset( $values['sndpc_singular_name'] ) ? esc_attr( $values['sndpc_singular_name'][0] ) : '';
		$sndpc_description   = isset( $values['sndpc_description'] ) ? esc_attr( $values['sndpc_description'][0] ) : '';
		$sndpc_icon_url = isset( $values['sndpc_icon_url'] ) ? esc_attr( $values['sndpc_icon_url'][0] ) : '';
		$sndpc_icon_slug = isset( $values['sndpc_icon_slug'] ) ? esc_attr( $values['sndpc_icon_slug'][0] ) : '';
		if ( ! empty( $sndpc_icon_slug ) ) {
			$sndpc_icon_name = $sndpc_icon_slug;
		} else {
			$sndpc_icon_name = $sndpc_icon_url;
		}
		$sndpc_custom_rewrite_slug = isset( $values['sndpc_custom_rewrite_slug'] ) ? esc_attr( $values['sndpc_custom_rewrite_slug'][0] ) : '';
		$sndpc_menu_position       = isset( $values['sndpc_menu_position'] ) ? esc_attr( $values['sndpc_menu_position'][0] ) : '';
		$sndpc_public              = isset( $values['sndpc_public'] ) ? esc_attr( $values['sndpc_public'][0] ) : '';
		$sndpc_show_ui             = isset( $values['sndpc_show_ui'] ) ? esc_attr( $values['sndpc_show_ui'][0] ) : '';
		$sndpc_has_archive         = isset( $values['sndpc_has_archive'] ) ? esc_attr( $values['sndpc_has_archive'][0] ) : '';
		$sndpc_exclude_from_search = isset( $values['sndpc_exclude_from_search'] ) ? esc_attr( $values['sndpc_exclude_from_search'][0] ) : '';
		$sndpc_capability_type     = isset( $values['sndpc_capability_type'] ) ? esc_attr( $values['sndpc_capability_type'][0] ) : '';
		$sndpc_hierarchical        = isset( $values['sndpc_hierarchical'] ) ? esc_attr( $values['sndpc_hierarchical'][0] ) : '';
		$sndpc_rewrite             = isset( $values['sndpc_rewrite'] ) ? esc_attr( $values['sndpc_rewrite'][0] ) : '';
		$sndpc_withfront           = isset( $values['sndpc_withfront'] ) ? esc_attr( $values['sndpc_withfront'][0] ) : '';
		$sndpc_feeds               = isset( $values['sndpc_feeds'] ) ? esc_attr( $values['sndpc_feeds'][0] ) : '';
		$sndpc_pages               = isset( $values['sndpc_pages'] ) ? esc_attr( $values['sndpc_pages'][0] ) : '';
		$sndpc_query_var           = isset( $values['sndpc_query_var'] ) ? esc_attr( $values['sndpc_query_var'][0] ) : '';
		$sndpc_show_in_rest        = isset( $values['sndpc_show_in_rest'] ) ? esc_attr( $values['sndpc_show_in_rest'][0] ) : '';
		$sndpc_publicly_queryable  = isset( $values['sndpc_publicly_queryable'] ) ? esc_attr( $values['sndpc_publicly_queryable'][0] ) : '';
		$sndpc_show_in_menu        = isset( $values['sndpc_show_in_menu'] ) ? esc_attr( $values['sndpc_show_in_menu'][0] ) : '';
		$sndpc_supports                 = isset( $values['sndpc_supports'] ) ? unserialize( $values['sndpc_supports'][0] ) : array();
		$sndpc_supports_title           = ( isset( $values['sndpc_supports'] ) && in_array( 'title', $sndpc_supports ) ? 'title' : '' );
		$sndpc_supports_editor          = ( isset( $values['sndpc_supports'] ) && in_array( 'editor', $sndpc_supports ) ? 'editor' : '' );
		$sndpc_supports_excerpt         = ( isset( $values['sndpc_supports'] ) && in_array( 'excerpt', $sndpc_supports ) ? 'excerpt' : '' );
		$sndpc_supports_trackbacks      = ( isset( $values['sndpc_supports'] ) && in_array( 'trackbacks', $sndpc_supports ) ? 'trackbacks' : '' );
		$sndpc_supports_custom_fields   = ( isset( $values['sndpc_supports'] ) && in_array( 'custom-fields', $sndpc_supports ) ? 'custom-fields' : '' );
		$sndpc_supports_comments        = ( isset( $values['sndpc_supports'] ) && in_array( 'comments', $sndpc_supports ) ? 'comments' : '' );
		$sndpc_supports_revisions       = ( isset( $values['sndpc_supports'] ) && in_array( 'revisions', $sndpc_supports ) ? 'revisions' : '' );
		$sndpc_supports_featured_image  = ( isset( $values['sndpc_supports'] ) && in_array( 'thumbnail', $sndpc_supports ) ? 'thumbnail' : '' );
		$sndpc_supports_author          = ( isset( $values['sndpc_supports'] ) && in_array( 'author', $sndpc_supports ) ? 'author' : '' );
		$sndpc_supports_page_attributes = ( isset( $values['sndpc_supports'] ) && in_array( 'page-attributes', $sndpc_supports ) ? 'page-attributes' : '' );
		$sndpc_supports_post_formats    = ( isset( $values['sndpc_supports'] ) && in_array( 'post-formats', $sndpc_supports ) ? 'post-formats' : '' );

		$sndpc_builtin_taxonomies            = isset( $values['sndpc_builtin_taxonomies'] ) ? unserialize( $values['sndpc_builtin_taxonomies'][0] ) : array();
		$sndpc_builtin_taxonomies_categories = ( isset( $values['sndpc_builtin_taxonomies'] ) && in_array( 'category', $sndpc_builtin_taxonomies ) ? 'category' : '' );
		$sndpc_builtin_taxonomies_tags       = ( isset( $values['sndpc_builtin_taxonomies'] ) && in_array( 'post_tag', $sndpc_builtin_taxonomies ) ? 'post_tag' : '' );
		wp_nonce_field( 'sndpc_meta_box_nonce_action', 'sndpc_meta_box_nonce_field' );
		global $pagenow;
		$sndpc_supports_title   = $pagenow === 'post-new.php' ? 'title' : $sndpc_supports_title;
		$sndpc_supports_editor  = $pagenow === 'post-new.php' ? 'editor' : $sndpc_supports_editor;
		$sndpc_supports_excerpt = $pagenow === 'post-new.php' ? 'excerpt' : $sndpc_supports_excerpt;
		?>
		<table class="sndpc">
			<tr>
				<td class="label">
					<label for="sndpc_name"><span class="required">*</span> <?php _e( 'Post Type Slug', 'dynamic-post-creator' ); ?></label>
					<p><?php _e( 'The post type name/slug. Used for various queries for post type content.', 'dynamic-post-creator' ); ?></p>
					<p><?php _e( 'Slugs should only contain alphanumeric, latin characters. Underscores should be used in place of spaces. Set "Custom Rewrite Slug" field to make slug use dashes for URLs.', 'dynamic-post-creator' ); ?></p>
				</td>
				<td>
					<input type="text" name="sndpc_name" placeholder="(e.g. movies)" id="sndpc_name" class="widefat" tabindex="1" value="<?php echo $sndpc_name; ?>" required/>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="sndpc_label"><span class="required">*</span><?php _e( 'Plural Label', 'dynamic-post-creator' ); ?></label>
					<p><?php _e( 'Used for the post type admin menu item.', 'dynamic-post-creator' ); ?></p>
				</td>
				<td>
					<input type="text" name="sndpc_label" placeholder="(e.g. Movies)" id="sndpc_label" class="widefat" tabindex="2" value="<?php echo $sndpc_label; ?>" required/>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="sndpc_singular_name"><span class="required">*</span><?php _e( 'Singular Name', 'dynamic-post-creator' ); ?></label>
					<p><?php _e( 'Used when a singular label is needed.', 'dynamic-post-creator' ); ?></p>
				</td>
				<td>
					<input type="text" name="sndpc_singular_name" id="sndpc_singular_name" class="widefat" tabindex="3" placeholder="(e.g. Movies)" value="<?php echo $sndpc_singular_name; ?>" required/>
				</td>
			</tr>
			<tr>
				<td class="label top">
					<label for="sndpc_description"><?php _e( 'Description', 'dynamic-post-creator' ); ?></label>
					<p><?php _e( 'Perhaps describe what your custom post type is used for?.', 'dynamic-post-creator' ); ?></p>
				</td>
				<td>
					<textarea name="sndpc_description" id="sndpc_description" class="widefat" tabindex="4" rows="4"><?php echo $sndpc_description; ?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="section">
					<h3><?php _e( 'Admin Menu Options', 'dynamic-post-creator' ); ?></h3>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="sndpc_show_ui"><?php _e( 'Show UI', 'dynamic-post-creator' ); ?></label>
					<p><?php _e( '(default: true) Whether or not to generate a default UI for managing this post type.' ); ?></p>
				</td>
				<td>
					<select name="sndpc_show_ui" id="sndpc_show_ui" tabindex="13">
						<option value="1" <?php selected( $sndpc_show_ui, '1' ); ?>><?php _e( 'True', 'dynamic-post-creator' ); ?> (<?php _e( 'default', 'dynamic-post-creator' ); ?>)</option>
						<option value="0" <?php selected( $sndpc_show_ui, '0' ); ?>><?php _e( 'False', 'dynamic-post-creator' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="sndpc_show_in_menu"><?php _e( 'Show in Menu', 'dynamic-post-creator' ); ?></label>
					<p><?php _e( '(Custom Post Type UI default: true) Whether or not this post type is available for selection in navigation menus.' ); ?></p>
				</td>
				<td>
					<select name="sndpc_show_in_menu" id="sndpc_show_in_menu" tabindex="15">
						<option value="1" <?php selected( $sndpc_show_in_menu, '1' ); ?>><?php _e( 'True', 'dynamic-post-creator' ); ?> (<?php _e( 'default', 'dynamic-post-creator' ); ?>)</option>
						<option value="0" <?php selected( $sndpc_show_in_menu, '0' ); ?>><?php _e( 'False', 'dynamic-post-creator' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="current-sndpc-icon"><?php _e( 'Icon', 'dynamic-post-creator' ); ?></label>
					<p><?php _e( 'Custom image should be 20px by 20px.', 'dynamic-post-creator' ); ?></p>
				</td>
				<td>
					<div class="sndpc-icon">
						<div class="current-sndpc-icon">
							<?php if ( $sndpc_icon_url ) { ?><img src="<?php echo $sndpc_icon_url; ?>" /><?php } ?></div>
							<a href="/" class="remove-sndpc-icon button-secondary"<?php if ( ! $sndpc_icon_url ) { ?> style="display: none;"<?php } ?> tabindex="16">Remove Icon</a>
							<a  href="/"class="media-uploader-button button-primary" data-post-id="<?php echo $post->ID; ?>" tabindex="17"><?php if ( ! $sndpc_icon_url ) { ?><?php _e( 'Add icon', 'dynamic-post-creator' ); ?><?php } else { ?><?php _e( 'Upload Icon', 'dynamic-post-creator' ); ?><?php } ?></a>
						</div>
						<input type="hidden" name="sndpc_icon_url" id="sndpc_icon_url" class="widefat" value="<?php echo $sndpc_icon_url; ?>" />
					</td>
				</tr>
				<tr>
					<td class="label">
						<label for="sndpc_icon_slug"><?php _e( 'Slug Icon', 'dynamic-post-creator' ); ?></label>
						<p><?php _e( 'Image URL or  (<a href="https://developer.WordPress.org/resource/dashicons/">Dashicon class name</a>) to use for icon. Custom image should be 20px by 20px.', 'dynamic-post-creator' ); ?></p>
					</td>
					<td>
						<?php if ( $sndpc_icon_slug ) { ?><span id="sndpc_icon_slug_before" class="dashicons-before <?php echo $sndpc_icon_slug; ?>"><?php } ?></span>
						<input type="text" name="sndpc_icon_slug" id="sndpc_icon_slug" class="widefat" tabindex="18" value="<?php echo $sndpc_icon_slug; ?>" />
					</td>
				</tr>
				<tr>
					<td colspan="2" class="section">
						<h3><?php _e( 'WordPress Integration', 'dynamic-post-creator' ); ?></h3>
					</td>
				</tr>
				<tr>
					<td class="label">
						<label for="sndpc_capability_type"><?php _e( 'Capability Type', 'dynamic-post-creator' ); ?></label>
						<p><?php _e( 'The post type to use for checking read, edit, and delete capabilities. A comma-separated second value can be used for plural version.', 'dynamic-post-creator' ); ?></p>
					</td>
					<td>
						<select name="sndpc_capability_type" id="sndpc_capability_type" tabindex="18">
							<option value="post" <?php selected( $sndpc_capability_type, 'post' ); ?>><?php _e( 'Post', 'dynamic-post-creator' ); ?> (<?php _e( 'default', 'dynamic-post-creator' ); ?>)</option>
							<option value="page" <?php selected( $sndpc_capability_type, 'page' ); ?>><?php _e( 'Page', 'dynamic-post-creator' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="label">
						<label for="sndpc_hierarchical"><?php _e( 'Hierarchical', 'dynamic-post-creator' ); ?></label>
						<p><?php _e( '(default: false) Whether or not the post type can have parent-child relationships. At least one published content item is needed in order to select a parent.', 'dynamic-post-creator' ); ?></p>
					</td>
					<td>
						<select name="sndpc_hierarchical" id="sndpc_hierarchical" tabindex="19">
							<option value="0" <?php selected( $sndpc_hierarchical, '0' ); ?>><?php _e( 'False', 'dynamic-post-creator' ); ?> (<?php _e( 'default', 'dynamic-post-creator' ); ?>)</option>
							<option value="1" <?php selected( $sndpc_hierarchical, '1' ); ?>><?php _e( 'True', 'dynamic-post-creator' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="label">
						<label for="sndpc_query_var"><?php _e( 'Query Var', 'dynamic-post-creator' ); ?></label>
						<p><?php _e( '(default: true) Sets the query_var key for this post type.', 'dynamic-post-creator' ); ?></p>
					</td>
					<td>
						<select name="sndpc_query_var" id="sndpc_query_var" tabindex="20">
							<option value="1" <?php selected( $sndpc_query_var, '1' ); ?>><?php _e( 'True', 'dynamic-post-creator' ); ?> (<?php _e( 'default', 'dynamic-post-creator' ); ?>)</option>
							<option value="0" <?php selected( $sndpc_query_var, '0' ); ?>><?php _e( 'False', 'dynamic-post-creator' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="label">
						<label for="sndpc_show_in_rest"><?php _e( 'Show in REST', 'dynamic-post-creator' ); ?></label>
						<p><?php _e( '(Custom Post Type UI default: true) Whether or not to show this post type data in the WP REST API.', 'dynamic-post-creator' ); ?></p>
					</td>
					<td>
						<select name="sndpc_show_in_rest" id="sndpc_show_in_rest" tabindex="21">
							<option value="1" <?php selected( $sndpc_show_in_rest, '1' ); ?>><?php _e( 'True', 'dynamic-post-creator' ); ?> (<?php _e( 'default', 'dynamic-post-creator' ); ?>)</option>
							<option value="0" <?php selected( $sndpc_show_in_rest, '0' ); ?>><?php _e( 'False', 'dynamic-post-creator' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="label">
						<label for="sndpc_publicly_queryable"><?php _e( 'Publicly Queryable', 'dynamic-post-creator' ); ?></label>
						<p><?php _e( '(default: true) Whether or not queries can be performed on the front end as part of parse_request().', 'dynamic-post-creator' ); ?></p>
					</td>
					<td>
						<select name="sndpc_publicly_queryable" id="sndpc_publicly_queryable" tabindex="22">
							<option value="1" <?php selected( $sndpc_publicly_queryable, '1' ); ?>><?php _e( 'True', 'dynamic-post-creator' ); ?> (<?php _e( 'default', 'dynamic-post-creator' ); ?>)</option>
							<option value="0" <?php selected( $sndpc_publicly_queryable, '0' ); ?>><?php _e( 'False', 'dynamic-post-creator' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="label top">
						<label for="sndpc_supports"><?php _e( 'Supports', 'dynamic-post-creator' ); ?></label>
						<p><?php _e( 'Add support for various available post editor features on the right. A checked value means the post type feature is supported.' ); ?></p>
						<p><?php _e( 'Use the "None" option to explicitly set "supports" to false.' ); ?></p>
						<p><?php _e( 'Featured images and Post Formats need theme support added, to be used.' ); ?></p>
					</td>
					<td>
						<input type="checkbox" tabindex="23" name="sndpc_supports[]" id="sndpc_supports_title" value="title" <?php checked( $sndpc_supports_title, 'title' ); ?> /> <label for="sndpc_supports_title"><?php _e( 'Title', 'dynamic-post-creator' ); ?> <span class="default">(<?php _e( 'default', 'dynamic-post-creator' ); ?>)</span></label><br />
						<input type="checkbox" tabindex="24" name="sndpc_supports[]" id="sndpc_supports_editor" value="editor" <?php checked( $sndpc_supports_editor, 'editor' ); ?> /> <label for="sndpc_supports_editor"><?php _e( 'Editor', 'dynamic-post-creator' ); ?> <span class="default">(<?php _e( 'default', 'dynamic-post-creator' ); ?>)</span></label><br />
						<input type="checkbox" tabindex="25" name="sndpc_supports[]" id="sndpc_supports_excerpt" value="excerpt" <?php checked( $sndpc_supports_excerpt, 'excerpt' ); ?> /> <label for="sndpc_supports_excerpt"><?php _e( 'Excerpt', 'dynamic-post-creator' ); ?> <span class="default">(<?php _e( 'default', 'dynamic-post-creator' ); ?>)</span></label><br />
						<input type="checkbox" tabindex="26" name="sndpc_supports[]" id="sndpc_supports_trackbacks" value="trackbacks" <?php checked( $sndpc_supports_trackbacks, 'trackbacks' ); ?> /> <label for="sndpc_supports_trackbacks"><?php _e( 'Trackbacks', 'dynamic-post-creator' ); ?></label><br />
						<input type="checkbox" tabindex="27" name="sndpc_supports[]" id="sndpc_supports_custom_fields" value="custom-fields" <?php checked( $sndpc_supports_custom_fields, 'custom-fields' ); ?> /> <label for="sndpc_supports_custom_fields"><?php _e( 'Custom Fields', 'dynamic-post-creator' ); ?></label><br />
						<input type="checkbox" tabindex="28" name="sndpc_supports[]" id="sndpc_supports_comments" value="comments" <?php checked( $sndpc_supports_comments, 'comments' ); ?> /> <label for="sndpc_supports_comments"><?php _e( 'Comments', 'dynamic-post-creator' ); ?></label><br />
						<input type="checkbox" tabindex="29" name="sndpc_supports[]" id="sndpc_supports_revisions" value="revisions" <?php checked( $sndpc_supports_revisions, 'revisions' ); ?> /> <label for="sndpc_supports_revisions"><?php _e( 'Revisions', 'dynamic-post-creator' ); ?></label><br />
						<input type="checkbox" tabindex="30" name="sndpc_supports[]" id="sndpc_supports_featured_image" value="thumbnail" <?php checked( $sndpc_supports_featured_image, 'thumbnail' ); ?> /> <label for="sndpc_supports_featured_image"><?php _e( 'Featured Image', 'dynamic-post-creator' ); ?></label><br />
						<input type="checkbox" tabindex="31" name="sndpc_supports[]" id="sndpc_supports_author" value="author" <?php checked( $sndpc_supports_author, 'author' ); ?> /> <label for="sndpc_supports_author"><?php _e( 'Author', 'dynamic-post-creator' ); ?></label><br />
						<input type="checkbox" tabindex="32" name="sndpc_supports[]" id="sndpc_supports_page_attributes" value="page-attributes" <?php checked( $sndpc_supports_page_attributes, 'page-attributes' ); ?> /> <label for="sndpc_supports_page_attributes"><?php _e( 'Page Attributes', 'dynamic-post-creator' ); ?></label><br />
						<input type="checkbox" tabindex="33" name="sndpc_supports[]" id="sndpc_supports_post_formats" value="post-formats" <?php checked( $sndpc_supports_post_formats, 'post-formats' ); ?> /> <label for="sndpc_supports_post_formats"><?php _e( 'Post Formats', 'dynamic-post-creator' ); ?></label><br />
					</td>
				</tr>
				<tr>
					<td class="label top">
						<label for="sndpc_builtin_taxonomies"><?php _e( 'Built-in Taxonomies', 'dynamic-post-creator' ); ?></label>
						<p>&nbsp;</p>
					</td>
					<td>
						<input type="checkbox" tabindex="34" name="sndpc_builtin_taxonomies[]" id="sndpc_builtin_taxonomies_categories" value="category" <?php checked( $sndpc_builtin_taxonomies_categories, 'category' ); ?> /> <label for="sndpc_builtin_taxonomies_categories"><?php _e( 'Categories', 'dynamic-post-creator' ); ?></label><br />
						<input type="checkbox" tabindex="35" name="sndpc_builtin_taxonomies[]" id="sndpc_builtin_taxonomies_tags" value="post_tag" <?php checked( $sndpc_builtin_taxonomies_tags, 'post_tag' ); ?> /> <label for="sndpc_builtin_taxonomies_tags"><?php _e( 'Tags', 'dynamic-post-creator' ); ?></label><br />
					</td>
				</tr>
			</table>

			<?php
		}

		/* start code for save custom post */
		public function sndpc_save_post( $post_id ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( ! isset( $_POST['sndpc_meta_box_nonce_field'] ) || ! wp_verify_nonce( $_POST['sndpc_meta_box_nonce_field'], 'sndpc_meta_box_nonce_action' ) ) {
				return;
			}

			if ( isset( $_POST['sndpc_name'] ) ) {
				update_post_meta( $post_id, 'sndpc_name', sanitize_text_field( strtolower( str_replace( ' ', '', $_POST['sndpc_name'] ) ) ) );
			}

			if ( isset( $_POST['sndpc_label'] ) ) {
				update_post_meta( $post_id, 'sndpc_label', sanitize_text_field( $_POST['sndpc_label'] ) );
			}

			if ( isset( $_POST['sndpc_singular_name'] ) ) {
				update_post_meta( $post_id, 'sndpc_singular_name', sanitize_text_field( $_POST['sndpc_singular_name'] ) );
			}

			if ( isset( $_POST['sndpc_description'] ) ) {
				update_post_meta( $post_id, 'sndpc_description', esc_textarea( $_POST['sndpc_description'] ) );
			}

			if ( isset( $_POST['sndpc_icon_slug'] ) ) {
				update_post_meta( $post_id, 'sndpc_icon_slug', esc_textarea( $_POST['sndpc_icon_slug'] ) );
			}

			if ( isset( $_POST['sndpc_icon_url'] ) ) {
				update_post_meta( $post_id, 'sndpc_icon_url', esc_textarea( $_POST['sndpc_icon_url'] ) );
			}

			if ( isset( $_POST['sndpc_public'] ) ) {
				update_post_meta( $post_id, 'sndpc_public', esc_attr( $_POST['sndpc_public'] ) );
			}

			if ( isset( $_POST['sndpc_show_ui'] ) ) {
				update_post_meta( $post_id, 'sndpc_show_ui', esc_attr( $_POST['sndpc_show_ui'] ) );
			}

			if ( isset( $_POST['sndpc_has_archive'] ) ) {
				update_post_meta( $post_id, 'sndpc_has_archive', esc_attr( $_POST['sndpc_has_archive'] ) );
			}

			if ( isset( $_POST['sndpc_exclude_from_search'] ) ) {
				update_post_meta( $post_id, 'sndpc_exclude_from_search', esc_attr( $_POST['sndpc_exclude_from_search'] ) );
			}

			if ( isset( $_POST['sndpc_capability_type'] ) ) {
				update_post_meta( $post_id, 'sndpc_capability_type', esc_attr( $_POST['sndpc_capability_type'] ) );
			}

			if ( isset( $_POST['sndpc_hierarchical'] ) ) {
				update_post_meta( $post_id, 'sndpc_hierarchical', esc_attr( $_POST['sndpc_hierarchical'] ) );
			}

			if ( isset( $_POST['sndpc_rewrite'] ) ) {
				update_post_meta( $post_id, 'sndpc_rewrite', esc_attr( $_POST['sndpc_rewrite'] ) );
			}

			if ( isset( $_POST['sndpc_withfront'] ) ) {
				update_post_meta( $post_id, 'sndpc_withfront', esc_attr( $_POST['sndpc_withfront'] ) );
			}

			if ( isset( $_POST['sndpc_feeds'] ) ) {
				update_post_meta( $post_id, 'sndpc_feeds', esc_attr( $_POST['sndpc_feeds'] ) );
			}

			if ( isset( $_POST['sndpc_pages'] ) ) {
				update_post_meta( $post_id, 'sndpc_pages', esc_attr( $_POST['sndpc_pages'] ) );
			}

			if ( isset( $_POST['sndpc_custom_rewrite_slug'] ) ) {
				update_post_meta( $post_id, 'sndpc_custom_rewrite_slug', sanitize_text_field( $_POST['sndpc_custom_rewrite_slug'] ) );
			}

			if ( isset( $_POST['sndpc_query_var'] ) ) {
				update_post_meta( $post_id, 'sndpc_query_var', esc_attr( $_POST['sndpc_query_var'] ) );
			}

			if ( isset( $_POST['sndpc_show_in_rest'] ) ) {
				update_post_meta( $post_id, 'sndpc_show_in_rest', esc_attr( $_POST['sndpc_show_in_rest'] ) );
			}

			if ( isset( $_POST['sndpc_publicly_queryable'] ) ) {
				update_post_meta( $post_id, 'sndpc_publicly_queryable', esc_attr( $_POST['sndpc_publicly_queryable'] ) );
			}

			if ( isset( $_POST['sndpc_menu_position'] ) ) {
				update_post_meta( $post_id, 'sndpc_menu_position', sanitize_text_field( $_POST['sndpc_menu_position'] ) );
			}

			if ( isset( $_POST['sndpc_show_in_menu'] ) ) {
				update_post_meta( $post_id, 'sndpc_show_in_menu', esc_attr( $_POST['sndpc_show_in_menu'] ) );
			}

			$sndpc_supports = isset( $_POST['sndpc_supports'] ) ? $_POST['sndpc_supports'] : array(); {
				update_post_meta( $post_id, 'sndpc_supports', $sndpc_supports );
			}

			$sndpc_builtin_taxonomies = isset( $_POST['sndpc_builtin_taxonomies'] ) ? $_POST['sndpc_builtin_taxonomies'] : array();
			update_post_meta( $post_id, 'sndpc_builtin_taxonomies', $sndpc_builtin_taxonomies );

			if ( isset( $_POST['sndpc_tax_name'] ) ) {
				update_post_meta( $post_id, 'sndpc_tax_name', sanitize_text_field( strtolower( str_replace( ' ', '', $_POST['sndpc_tax_name'] ) ) ) );
			}

			if ( isset( $_POST['sndpc_tax_label'] ) ) {
				update_post_meta( $post_id, 'sndpc_tax_label', sanitize_text_field( $_POST['sndpc_tax_label'] ) );
			}

			if ( isset( $_POST['sndpc_tax_singular_name'] ) ) {
				update_post_meta( $post_id, 'sndpc_tax_singular_name', sanitize_text_field( $_POST['sndpc_tax_singular_name'] ) );
			}

			if ( isset( $_POST['sndpc_tax_show_ui'] ) ) {
				update_post_meta( $post_id, 'sndpc_tax_show_ui', esc_attr( $_POST['sndpc_tax_show_ui'] ) );
			}

			if ( isset( $_POST['sndpc_tax_hierarchical'] ) ) {
				update_post_meta( $post_id, 'sndpc_tax_hierarchical', esc_attr( $_POST['sndpc_tax_hierarchical'] ) );
			}

			if ( isset( $_POST['sndpc_tax_rewrite'] ) ) {
				update_post_meta( $post_id, 'sndpc_tax_rewrite', esc_attr( $_POST['sndpc_tax_rewrite'] ) );
			}

			if ( isset( $_POST['sndpc_tax_custom_rewrite_slug'] ) ) {
				update_post_meta( $post_id, 'sndpc_tax_custom_rewrite_slug', sanitize_text_field( $_POST['sndpc_tax_custom_rewrite_slug'] ) );
			}

			if ( isset( $_POST['sndpc_tax_query_var'] ) ) {
				update_post_meta( $post_id, 'sndpc_tax_query_var', esc_attr( $_POST['sndpc_tax_query_var'] ) );
			}

			if ( isset( $_POST['sndpc_tax_show_in_rest'] ) ) {
				update_post_meta( $post_id, 'sndpc_tax_show_in_rest', esc_attr( $_POST['sndpc_tax_show_in_rest'] ) );
			}

			if ( isset( $_POST['sndpc_tax_show_admin_column'] ) ) {
				update_post_meta( $post_id, 'sndpc_tax_show_admin_column', esc_attr( $_POST['sndpc_tax_show_admin_column'] ) );
			}

			$sndpc_tax_post_types = isset( $_POST['sndpc_tax_post_types'] ) ? $_POST['sndpc_tax_post_types'] : array();
			update_post_meta( $post_id, 'sndpc_tax_post_types', $sndpc_tax_post_types );

			update_option( 'sndpc_plugin_settings_changed', true );
		}

		/* start code for flush rewrite rules */
		function sndpc_plugin_settings_flush_rewrite() {
			if ( get_option( 'sndpc_plugin_settings_changed' ) == true ) {
				flush_rewrite_rules();
				update_option( 'sndpc_plugin_settings_changed', false );
			}
		}

		/* start code for flush rewrite rules on plugin activation*/
		function sndpc_plugin_activate_flush_rewrite() {
			$this->sndpc_create_custom_post_types();
			flush_rewrite_rules();
		}

		function sndpc_change_columns( $cols ) {
			$cols = array(
				'cb'                    => '<input type="checkbox" />',
				'title'                 => __( 'Post Type', 'dynamic-post-creator' ),
				'custom_post_type_name' => __( 'Post Type Name', 'dynamic-post-creator' ),
				'label'                 => __( 'Label', 'dynamic-post-creator' ),
				'description'           => __( 'Description', 'dynamic-post-creator' ),
			);
			return $cols;
		}

		function sndpc_sortable_columns() {
			return array(
				'title' => 'title',
			);
		}

		/* start code for insert custom column */
		function sndpc_custom_columns( $column, $post_id ) {
			switch ( $column ) {
				case 'custom_post_type_name':
				echo get_post_meta( $post_id, 'sndpc_name', true );
				break;
				case 'label':
				echo get_post_meta( $post_id, 'sndpc_label', true );
				break;
				case 'description':
				echo get_post_meta( $post_id, 'sndpc_description', true );
				break;
			}
		}

		/* start code for insert custom taxonomy columns */

		function sndpc_tax_custom_columns( $column, $post_id ) {
			switch ( $column ) {
				case 'custom_post_type_name':
				echo get_post_meta( $post_id, 'sndpc_tax_name', true );
				break;
				case 'label':
				echo get_post_meta( $post_id, 'sndpc_tax_label', true );
				break;
			}
		}

		function sndpc_admin_footer() {
			global $post_type;
			?>
			<div id="sndpc-col-right" class="hidden">

				<div class="wp-box">
					<div class="inner">
						<img src="https://www.softnoesis.com/images/logo.png">
					</div>
					<div class="footer footer-blue">
						<ul class="left">
							<li><?php _e( 'Created by', 'sndpc' ); ?> <a href="http://www.softnoesis.com" target="_blank" title="Softnoesis">Softnoesis Pvt. Ltd.</a></li>
							<li></li>
						</ul>
					</div>
				</div>
			</div>
			<?php
		}

		function sndpc_post_updated_messages( $messages ) {
			global $post, $post_ID;

			$messages['sndpc'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Custom Post Type updated.', 'dynamic-post-creator' ),
			2  => __( 'Custom Post Type updated.', 'dynamic-post-creator' ),
			3  => __( 'Custom Post Type deleted.', 'dynamic-post-creator' ),
			4  => __( 'Custom Post Type updated.', 'dynamic-post-creator' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Custom Post Type restored to revision from %s', 'dynamic-post-creator' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Custom Post Type published.', 'dynamic-post-creator' ),
			7  => __( 'Custom Post Type saved.', 'dynamic-post-creator' ),
			8  => __( 'Custom Post Type submitted.', 'dynamic-post-creator' ),
			9  => __( 'Custom Post Type scheduled for.', 'dynamic-post-creator' ),
			10 => __( 'Custom Post Type draft updated.', 'dynamic-post-creator' ),
		);

			return $messages;
		}

		function wp_prepare_attachment_for_js( $response, $attachment, $meta ) {
			if ( $response['type'] != 'image' ) {
				return $response;
			}

			$attachment_url = $response['url'];
			$base_url       = str_replace( wp_basename( $attachment_url ), '', $attachment_url );

			if ( isset( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $k => $v ) {
					if ( ! isset( $response['sizes'][ $k ] ) ) {
						$response['sizes'][ $k ] = array(
							'height'      => $v['height'],
							'width'       => $v['width'],
							'url'         => $base_url . $v['file'],
							'orientation' => $v['height'] > $v['width'] ? 'portrait' : 'landscape',
						);
					}
				}
			}

			return $response;
		}
	}
$sndpc = new Sndpc();