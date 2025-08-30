<?php

define( 'WPWIZ_PLAINTEXT_THEME_URL', get_template_directory_uri() );
define( 'WPWIZ_PLAINTEXT_THEME_VERSION', '1.1.0' );

/**
 * Theme setup.
 */
function wpwiz_setup() {

	// Theme Supports.
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );

	// Custom Logo.
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 300,
			'width'       => 300,
			'flex-height' => true,
		)
	);

	add_theme_support( 'custom-background' );

	add_editor_style( '/css/editor-style.css' );

	register_nav_menus(
		array(
			'header_right' => __( 'Main Menu', 'plaintext' ),
		)
	);

	add_theme_support(
		'banner-header',
		apply_filters(
			'wpwiz_custom_header_args',
			array(
				'width'            => 1110,
				'height'           => 555,
				'flex-height'      => true,
				'wp-head-callback' => 'wpwiz_banner',
			)
		)
	);

	// Set content width.
	if ( ! isset( $content_width ) ) {
		$content_width = 780;
	}

	$ret = load_theme_textdomain( 'plaintext' );
	// var_dump($ret);exit;
}

add_action( 'after_setup_theme', 'wpwiz_setup' );

/**
 * Header style.
 */
function wpwiz_header_style() {
	$header_text_color = get_header_textcolor();
	?>
		<style id="plaintext-custom-header-styles" type="text/css">
			.site-title>a ,.header_menu>li>a
			{
				color: #<?php echo esc_attr( $header_text_color ); ?>;
			} 
			.site_header {
				background-image: url( <?php header_image(); ?> );
			}
		</style>
	<?php
}

/**
 * Menu Walker class.
 */
class wpwiz_Menu_Walker extends Walker {

	/**
	 * @var array Fields Fields.
	 */
	public $db_fields = array(
		'parent' => 'menu_item_parent',
		'id'     => 'db_id',
	);

	/**
	 * Start level.
	 *
	 * @param string $output
	 * @param int    $depth
	 * @param array  $args
	 *
	 * @return void
	 */
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "\n$indent<ul>\n";
	}

	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n";
	}

	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {

		global $wp_query;
		$indent      = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		$class_names = $value = '';
		$classes     = empty( $item->classes ) ? array() : (array) $item->classes;

		/* Add active class */
		if ( in_array( 'current-menu-item', $classes ) ) {
			$classes[] = 'active';
			unset( $classes['current-menu-item'] );
		}

		/* Check for children */
		$children = get_posts(
			array(
				'post_type'   => 'nav_menu_item',
				'nopaging'    => true,
				'numberposts' => 1,
				'meta_key'    => '_menu_item_menu_item_parent',
				'meta_value'  => $item->ID,
			)
		);
		if ( ! empty( $children ) ) {
			$classes[] = 'has-sub';
		}

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $value . $class_names . '>';

		$attributes  = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
		$attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
		$attributes .= ! empty( $item->url ) ? ' href="' . esc_attr( $item->url ) . '"' : '';

		$item_output  = $args->before;
		$item_output .= '<a' . $attributes . '><span>';
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</span></a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	function end_el( &$output, $item, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}
}


/**
 * Enqueue frontend scripts.
 */
function wpwiz_frontend_scripts() {
	wp_enqueue_style( 'wpwiz', get_template_directory_uri() . '/style.css', array(), WPWIZ_PLAINTEXT_THEME_URL );
	wp_enqueue_style( 'meanmenu', get_template_directory_uri() . '/css/meanmenu.css', array(), WPWIZ_PLAINTEXT_THEME_URL );
	wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css', array(), WPWIZ_PLAINTEXT_THEME_URL );
	wp_enqueue_script( 'meanmenu-js', get_template_directory_uri() . '/js/jquery.meanmenu.js', array( 'jquery' ), WPWIZ_PLAINTEXT_THEME_URL, true );
	wp_enqueue_script( 'plaintextjs', get_template_directory_uri() . '/js/script.js', array( 'jquery' ), WPWIZ_PLAINTEXT_THEME_URL, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'wpwiz_frontend_scripts' );

/**
 * Sidebars.
 */
function wpwiz_registering_sidebar() {
	register_sidebar(
		array(
			'id'            => 'primary_sidebar',
			'name'          => __( 'Primary Sidebar', 'plaintext' ),
			'description'   => __( 'This is the Primary Sidebar', 'plaintext' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'before_title'  => '<h3 class="wpwiz_primary_sidebar">',
			'after_title'   => '</h3>',
			'after_widget'  => '</div>',
		)
	);

}

add_action( 'widgets_init', 'wpwiz_registering_sidebar' );

function headless_preview() {
    $slug = basename(get_permalink());
    return "http://www.contentcucumber.com/preview/wkerfhberfiq4fu79q48/$slug";
}

add_filter( 'preview_post_link', 'headless_preview' );

// workaround script until there's an official solution for https://github.com/WordPress/gutenberg/issues/13998
function fix_preview_link_on_draft() {
	echo '<script type="text/javascript">
		jQuery(document).ready(function () {
			const checkPreviewInterval = setInterval(checkPreview, 1000);
			function checkPreview() {
				const editorPreviewButton = jQuery(".editor-post-preview");
				const editorPostSaveDraft = jQuery(".editor-post-save-draft");
				if (editorPostSaveDraft.length && editorPreviewButton.length && editorPreviewButton.attr("href") !== "' . get_preview_post_link() . '" ) {
					editorPreviewButton.attr("href", "' . get_preview_post_link() . '");
					editorPreviewButton.off();
					editorPreviewButton.click(false);
					editorPreviewButton.on("click", function() {
						editorPostSaveDraft.click();
						setTimeout(function() { 
							const win = window.open("' . get_preview_post_link() . '", "_blank");
							if (win) {
								win.focus();
							}
						}, 1000);
					});
				}
			}
		});
	</script>';
}
add_action('admin_footer', 'fix_preview_link_on_draft');