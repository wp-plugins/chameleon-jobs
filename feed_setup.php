<?php
   /*
    Plugin Name: Chameleoni.com Job Feed
    Plugin URI: https://www.chameleoni.com/chameleoni-website-design/wordpress-website-integration/
    Description: Plugin for displaying jobs from chameleoni.com
    Author: Chameleoni.com
    Version: 1.0
    Author URI: https://www.chameleoni.com/
    */
  
define('JOBINFO_FOLDER', dirname(plugin_basename(__FILE__)));
@define('JOBINFO_URL', plugins_url().'/'.JOBINFO_FOLDER);

function cjf_jobadmin() {
    include('job_admin.php');
}
function cjf_jobadmin_menu() 
{ 
	@add_menu_page(
		"Jobs Setting",
		"Jobs Setting",
		8,
		__FILE__,
		"cjf_jobadmin",
		".@site_url."."/wp-admin/images/generic.png"
	); 
	
	add_submenu_page(__FILE__, 'Job Listing', 'Job Listing', 'manage_options', __FILE__.'/JobListing', 'cjf_listing_admin_fun');
}
function cjf_listing_admin_fun()
{
	include 'job_listing_admin_page.php';
	
}
function cjf_jobs_install()
{
        // do NOT forget this global
	global $wpdb;
	$table_name = $wpdb->prefix . 'jobs';
	// this if statement makes sure that the table doe not exist already
	$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
		  	id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id INT( 11 ) NOT NULL,
     		job_id VARCHAR(255)  NOT NULL ,
  			job_title VARCHAR(255)  NOT NULL , 
			name VARCHAR(255)  NOT NULL ,
			email VARCHAR(255)  NOT NULL ,
			cv VARCHAR(255)  NOT NULL ,
			date DATE NOT NULL ,
			ordering INT(11)  NOT NULL ,
			state TINYINT(1)  NOT NULL ,
			checked_out INT(11)  NOT NULL ,
			checked_out_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			created_by INT(11)  NOT NULL ,
			UNIQUE KEY `id` (`id`));";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta(@$wpdb->prepare($sql));
	$table_name2 = $wpdb->prefix . 'jobs_settings';
	// this if statement makes sure that the table doe not exist already
	$sql2 = "CREATE TABLE IF NOT EXISTS ".$table_name2." (
		  	id mediumint(9) NOT NULL AUTO_INCREMENT,
     		http_url VARCHAR(255)  NOT NULL ,
			authKey  VARCHAR(255)  NOT NULL ,
			authPassword  VARCHAR(255)  NOT NULL ,
			aPIKey  VARCHAR(255)  NOT NULL ,
			userName VARCHAR(255)  NOT NULL ,
			thank_you_page mediumint(9),
  			feed_location VARCHAR(255)  NOT NULL DEFAULT '0',
			feed_type VARCHAR(255)  NOT NULL DEFAULT '0',
			feed_salary VARCHAR(255)  NOT NULL DEFAULT '0',
			feed_summary VARCHAR(255)  NOT NULL DEFAULT '0',
			summary_characters mediumint(9),
			number_of_jobsper_Page mediumint(9),
			UNIQUE KEY `id` (`id`));";
	dbDelta(@$wpdb->prepare($sql2));
	$row = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix . 'jobs_settings'));
	if(!$row)
	{
		$table_name3 = $wpdb->prefix . 'jobs_settings';
		$sql3 = "INSERT INTO ".$table_name3." VALUES(null,'https://jobs.chameleoni.com/api/PostXML/PostXml.aspx','Guest','KgwLLm7TL6G6','D12E9CF3-F742-47FC-97CB-295F4488C2FA','David','','0','0','0','0',200,5)";
		dbDelta(@$wpdb->prepare($sql3));	
	}
}
function cjf_jobs_install_del() 
{
    global $wpdb;
	$table_name = $wpdb->prefix . 'jobs';
    $structure1 = "DROP TABLE $table_name";
	$wpdb->query(@$wpdb->prepare($structure1));
}

function cjf_settings_install_del() 
{
    global $wpdb;
	$table_name3 = $wpdb->prefix . 'jobs_settings';
    $structure3 = "DROP TABLE $table_name3";
	$wpdb->query(@$wpdb->prepare($structure3));
}
function cjf_front_view_job_func()
{
	include 'jobs.php';
} 
function cjf_job_loginfunc()
{
	include 'joblogin.php';
} 
add_shortcode('front_view_joblogin', 'cjf_job_loginfunc');

function cjf_stylesheetcss_scripts() {
	wp_register_style( 'prefix-style', JOBINFO_URL.'/css/job_style.css');
    wp_enqueue_style( 'prefix-style' );
}
add_action( 'wp_enqueue_scripts', 'cjf_stylesheetcss_scripts' );
add_action('admin_menu','cjf_jobadmin_menu'); 
register_activation_hook(__FILE__,'cjf_jobs_install');
register_deactivation_hook(__FILE__,'cjf_settings_install_del');
add_shortcode('Jobs_disp_front', 'cjf_front_view_job_func'); 