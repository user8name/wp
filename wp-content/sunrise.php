<?php
/**
 * File sunrise.php
 *
 * This allows us to copy the production multisite database to staging/dev and still use
 * it directly without altering domains
 */

/**
 * Filter /wp-includes/ms-load.php get_site_by_path to find production domains
 **/

$blog_do=[

];
function dev_get_site_by_path($_site, $_domain, $_path, $_segments, $_paths) {
    global $wpdb, $path,$blog_do;



    // Get our actual domain in the database (should be set to production domain)
    // The domain coming in should be the request domain


    //$dom=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."blogs_domain WHERE domain = %s AND is_del= %d",[WP_DEV_TLD,0]));

    if (!isset($blog_do[WP_DEV_TLD])){
        $domain = str_replace( WP_DEV_TLD, WP_PROD_TLD, $_domain);
    }else{
        $blog_domain = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->blogs WHERE blog_id = %d", [$blog_do[WP_DEV_TLD]]));

        $domain=$blog_domain->domain;
    }



    // Search for a site matching the domain and first path segment
    $site = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->blogs WHERE domain = %s and path = %s", [$domain, $_paths[0]] ) );
    $current_path = $_paths[0];

    if ($site === null) {
        // Specifically for the main blog - if a site is not found then load the main blog
        $site = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->blogs WHERE domain = %s and path = %s", [$domain, '/'] ) );
        $current_path = '/';
    }

    // Set path to match the first segment
    $path = $current_path;
    /*
    $host = $_SERVER['HTTP_HOST'];
    $https = $_SERVER['HTTPS'];
    if($https=='on'){
        define('WP_SITEURL', 'https://' . $_SERVER['HTTP_HOST'].$current_path);
        define('WP_HOME', 'https://' . $_SERVER['HTTP_HOST'].$current_path);
    }else{
        define('WP_SITEURL', 'http://' . $_SERVER['HTTP_HOST'].$current_path);
        define('WP_HOME', 'http://' . $_SERVER['HTTP_HOST'].$current_path);
    }
    */
    //echo WP_SITEURL;
    //echo WP_HOME;


    return $site;
}


/**
 * Filter the site_url and home options for each site, and
 * filter /wp-includes/link-template.php::network_site_url()
 * and /wp-includes/link-template.php::network_home_url()
 * so that our network site link is correct in the admin menu
 */
function dev_network_url( $_url = '' ) {
//	echo $_url;
    //echo str_replace( WP_PROD_TLD, WP_DEV_TLD, $_url );
//	die;
    global $wpdb,$blog_do;

    //$dom=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."blogs_domain WHERE domain = %s AND is_del= %d",[WP_DEV_TLD,0]));

    if (!isset($blog_do[WP_DEV_TLD])){
        return str_replace( WP_PROD_TLD, WP_DEV_TLD, $_url );
    }else{
        //$blog_domain1 = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->blogs WHERE blog_id = %d", [$dom->blog_id]));
//        return str_replace( WP_PROD_TLD, $blog_domain->domain, $_url );
        $blog_tab=[

        ];
        
        return str_replace( $blog_tab[$blog_do[WP_DEV_TLD]], WP_DEV_TLD, $_url );
    }

}
global $wpdb;


$site = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->blogs WHERE domain = %s",[WP_DEV_TLD] ));


if($site===null){
    add_filter('pre_get_site_by_path', 'dev_get_site_by_path', 1, 5);
    add_filter('pre_get_network_by_path', 'dev_get_site_by_path', 1, 5);


    add_filter( 'network_site_url', 'dev_network_url' );
    add_filter( 'network_home_url', 'dev_network_url' );
    add_filter( 'option_siteurl', 'dev_network_url' );
    add_filter( 'option_home', 'dev_network_url' );
}


