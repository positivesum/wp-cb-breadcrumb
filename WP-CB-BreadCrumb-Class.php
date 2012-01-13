<?php
if(!class_exists('WPCBBreadCrumb'))
{

class WPCBBreadCrumb extends cfct_build_module
{
	public function __construct()
	{
		$this->pluginDir		= basename(dirname(__FILE__));
		$this->pluginPath		= WP_PLUGIN_DIR . '/' . $this->pluginDir;
		$this->pluginUrl 		= WP_PLUGIN_URL.'/'.$this->pluginDir;
		$this->delimiter        = '&#155;';

		$opts = array
		(
			'description' => 'Display a breadcrumb.',
			'icon' => $this->pluginUrl.'/icon.png'
		);
		parent::__construct('cfct-wp-cb-page-breadcrumb', 'Breadcrumb', $opts);
	}
	public function text($data)
	{
		return "";
	}
	
	public function display($data)
	{
        ob_start();
		$this->the_breadcrumbs();
        $bc = ob_get_contents();
        ob_end_clean();
        return $bc;
	}
	
	public function admin_form($data)
	{
		return 'Please save to activate module.';
	}


	public function the_breadcrumbs() {
		global $id; // Current page/post ID

		// Get primary menu
		$locations = get_nav_menu_locations();
		$menu = wp_get_nav_menu_object( $locations[ 'primary' ] );

		// Get menu items
		// TODO: I think need add option in admin panel for choose menu for breadcrumbs
		$_items = wp_get_nav_menu_items($menu->term_id);

		// Prepared menu items array
		$items = array();

		// Search current page/post in menu
		// Search controller
		$_in_menu = false;
        if ( !empty($_items) ) {
            foreach ($_items as $i => $item) {
                // Build new array of menu items (ID => item)
                $items[$item->ID] = $item;

                // Founded
                if ($item->object_id == $id) {
                    $_in_menu = $item->ID;
                }
            }
        }

		// If post/page in menu build breadcrumbs base on it
		if ($_in_menu !== false) {
			// Breadcrumbs array
			$breadcrumbs = array();

			// Get start point
			$_point = $items[$_in_menu];
			$breadcrumbs[] = '<span class="current">'.trim($_point->title).'</span>';

			// Build Breadcrumbs array
			while ($_point !== false) {
				if (isset($items[$_point->menu_item_parent])) {
					$_point = $items[$_point->menu_item_parent];
					// Check "dead" links
					if ($_point->target == '_nothing') {
						$breadcrumbs[] = '<span>'.trim($_point->title).'</span>';
					} else { // Live link
						$breadcrumbs[] = '<a href="'.$_point->url.'">'.trim($_point->title).'</a>';
					}
				} else {
					$_point = false;
				}
			}

			// Add home
			$breadcrumbs[] = '<a href="'.get_bloginfo('url').'">'.__('Home').'</a>';

			// Reorder
			krsort($breadcrumbs);

			// Output
			echo '<div id="breadcrumb">';
			echo implode('&nbsp;'.$this->delimiter.'&nbsp;', $breadcrumbs);
			echo '</div>';

		} else { // Else run current behaviour
			// Output
			$this->_the_breadcrumbs();
		}

	}
	
	private function _the_breadcrumbs()
	{
		$delimiter = $this->delimiter;
		$name = __('Home'); //text for the 'Home' link
		$currentBefore = '<span class="current">';
		$currentAfter = '</span>';

		if(!is_home() && !is_front_page() || is_paged())
		{
            ob_start();
			echo '<div id="breadcrumb">';
			global $post;
			$home = get_bloginfo('url');
			echo '<a href="' . $home . '">' . $name . '</a> ' . $delimiter . ' ';

			if(is_category())
			{
				global $wp_query;
				$cat_obj = $wp_query->get_queried_object();
				$thisCat = $cat_obj->term_id;
				$thisCat = get_category($thisCat);
				$parentCat = get_category($thisCat->parent);
				if($thisCat->parent != 0) echo(get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' '));
				echo $currentBefore . 'Archive by category &#39;';
				single_cat_title();
				echo '&#39;' . $currentAfter;
			}
			elseif (is_day())
			{
				echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
				echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
				echo $currentBefore . get_the_time('d') . $currentAfter;
			}
			elseif(is_month())
			{
				echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
				echo $currentBefore . get_the_time('F') . $currentAfter;

			}
			elseif (is_year())
			{
				echo $currentBefore . get_the_time('Y') . $currentAfter;
			}
			elseif (is_single() && !is_attachment())
			{
				$cat = get_the_category(); $cat = $cat[0];
				echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
				echo $currentBefore;
				the_title();
				echo $currentAfter;
			}
			elseif (is_attachment())
			{
				$parent = get_post($post->post_parent);
				$cat = get_the_category($parent->ID); $cat = $cat[0];
				echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
				echo '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a> ' . $delimiter . ' ';
				echo $currentBefore;
				the_title();
				echo $currentAfter;
			}
			elseif (is_page() && !$post->post_parent)
			{
				echo $currentBefore;
				the_title();
				echo $currentAfter;
			}
			elseif (is_page() && $post->post_parent)
			{
				$parent_id  = $post->post_parent;
				$breadcrumbs = array();
				while($parent_id)
				{
					$page = get_page($parent_id);
					$breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
					$parent_id  = $page->post_parent;
				}
				$breadcrumbs = array_reverse($breadcrumbs);
				foreach ($breadcrumbs as $crumb) echo $crumb . ' ' . $delimiter . ' ';
				echo $currentBefore;
				the_title();
				echo $currentAfter;
			}
			elseif (is_search())
			{
				echo $currentBefore . 'Search results for &#39;' . get_search_query() . '&#39;' . $currentAfter;
			}
			elseif (is_tag())
			{
				echo $currentBefore . 'Posts tagged &#39;';
				single_tag_title();
				echo '&#39;' . $currentAfter;
			}
			elseif (is_author())
			{
				global $author;
				$userdata = get_userdata($author);
				echo $currentBefore . 'Articles posted by ' . $userdata->display_name . $currentAfter;
			}
			elseif (is_404())
			{
				echo $currentBefore . 'Error 404' . $currentAfter;
			}

			if(get_query_var('paged'))
			{
				if(is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
				echo __('Page') . ' ' . get_query_var('paged');
				if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
			}

			echo '</div>';
            
		}
	}
}
cfct_build_register_module('cfct-wp-cb-page-breadcrumb', 'WPCBBreadCrumb');
}