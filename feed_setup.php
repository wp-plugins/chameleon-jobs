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
		"/wp-admin/images/generic.png"); 
	
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
	switch(@$_REQUEST['task']){
		case 'jobdetails':
				global $post;
				$pageid = $post->ID;
				global $wpdb;

					$setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."jobs_settings"));
					$feed_url = $setting->http_url;//'http://jobs.chameleoni.com/xmlfeed.aspx';
					$AuthKey = $setting->authKey;
					$AuthPassword = $setting->authPassword;
					$APIKey = $setting->aPIKey;
					$UserName = $setting->userName;
					$Thank_you_page = $setting->thank_you_page;
					$feed_location = $setting->feed_location;
					$feed_type = $setting->feed_type;
					$feed_salary = $setting->feed_salary;
					$feed_summary = $setting->feed_summary;
					$jobId = $_REQUEST['jobid'];
					
						$request = '
						<ChameleonIAPI>
							<Method>SearchVacancies</Method>
							<APIKey>'.$APIKey.'</APIKey>
							<UserName>'.$UserName.'</UserName>
							<Filter>
								<!-- Options Placeholder -->
								<Param Name="VacancyId" Value="'.$jobId.'" />
							</Filter>	
						</ChameleonIAPI>';
						$encoded = 'Xml='.$request.'&Action=postxml&AuthKey='.$AuthKey.'&AuthPassword='.$AuthPassword;
						$ch = curl_init($feed_url);
						curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_VERBOSE, 0);
						$result = curl_exec($ch);
						curl_close($ch);
						$result = str_replace('utf-16','utf-8',$result);
						$xml = simplexml_load_string($result);
						$json = json_encode($xml);
						$array = json_decode($json,TRUE);
						if($array['Status'] == 'Pass'){
							$job_details = @$array['Vacancies']['Vacancy'];	
							//$session->set('recent_job',$this->job);
						}

					
		
					$job_details_str =	'<div id="vacancy_details">
					<p><span class="location"><label><b>Job Title:</b></label>'.esc_html($job_details['JobTitle']).'</span></p>
					<p><span class="location"><label><b>Location:</b></label>'.esc_html($job_details['LocationTag']).'</span></p>
					<p><span class="type"><label><b>Job Reference:</b></label>'.esc_html($job_details['Reference']).'</span></p>
					<p><span class="type"><label><b>Type:</b></label>'.$job_details['JobType'].'</span></p>
					<p><span class="salary"><label><b>Salary: </b></label>&#163;';
					$job_details_str .= ($job_details['MinSalary']) ? esc_html($job_details['MinSalary']) .' to &#163; '. esc_html($job_details['MaxSalary']) : 'Negotiable';
					$job_details_str .= '</span></p>
					<p><span class="type"><label><b>Close Date:</b></label>'.esc_html(date('Y-m-d',strtotime($job_details['CloseDate']))).'</span></p>
					<p><span class="summary"><label><b>Summary: </b></label>'. nl2br(esc_html($job_details['JobDescription'])).'</span></p>
					<p><span class="type"><label><b>Benefits:</b></label>'. esc_html($job_details['Benefits']).'</span></p>
					<p> <span class="consultantname"><label><b>Consultant Name:</b></label>'.esc_html($job_details['ConsultantName']).'</span></p>
					<p><span class="consultantemail"><label><b>Consultant Email:</b></label><a href="mailto:'.$job_details['ConsultantEmail'].'">'.esc_html($job_details['ConsultantEmail']).'</a></span></p>
				 <span><a class="view_morebtn" href="index.php?page_id='.$pageid.'&task=apply&jobid='.$_REQUEST['jobid'].'&job_title='.esc_html($job_details['JobTitle']).'">Apply</a> </span>
				<a href="?page_id='.$pageid.'" class="view_morebtn">Back</a>
				</p>
				</div>';
				return $job_details_str;
		break;			
		case 'apply':
			global $post;
			$pageid = $post->ID;
			global $wpdb;
			$setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."jobs_settings"));

			if(isset($_POST['apply-submit']) == "Submit")
			{
				global $wpdb;
				$setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."jobs_settings"));
				$feed_url = $setting->http_url;//'http://jobs.chameleoni.com/xmlfeed.aspx';
				$AuthKey = $setting->authKey;
				$AuthPassword = $setting->authPassword;
				$APIKey = $setting->aPIKey;
				$UserName = $setting->userName;
				$Thank_you_page = $setting->thank_you_page;
				$feed_location = $setting->feed_location;
				$feed_type = $setting->feed_type;
				$feed_salary = $setting->feed_salary;
				$feed_summary = $setting->feed_summary;
				$page_size = $setting->number_of_jobsper_Page; 


			  $request_email = '<ChameleonIAPI>
				<Method>CheckEmail</Method>
				<APIKey>'.$APIKey.'</APIKey>
				<UserName>'.$UserName.'</UserName>
				 <InputData>
				 <Input Name="Email" Value="'.sanitize_email($_POST['Email']).'" />
				 </InputData>
				</ChameleonIAPI>';
				 
				 $encoded = 'Xml='.$request_email.'&Action=postxml&AuthKey='.$AuthKey.'&AuthPassword='.$AuthPassword;
				 $ch = curl_init($feed_url);
				 curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
				 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
				 curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
				 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				 curl_setopt($ch, CURLOPT_VERBOSE, 0);
				 $result_email = curl_exec($ch);
				 $result_email = str_replace('utf-16','utf-8',$result_email);
				 $xml = simplexml_load_string($result_email);
				 $json = json_encode($xml);
				 $array_email = json_decode($json,TRUE);

				 // end verification 
				 
				if($array_email['ContactCount'] == 0)
				{
					

				  $request = '<ChameleonIAPI>
				<Method>CandidateRegister</Method>
				<APIKey>'.$APIKey.'</APIKey>
				<UserName>'.$UserName.'</UserName>
				<InputData>
						<Input Name="TitleId" Value="1" />
						<Input Name="Forename" Value="'.sanitize_text_field($_POST['Forename']).'" />
						<Input Name="Surname" Value="'.sanitize_text_field($_POST['Surname']).'" />
						<Input Name="Email" Value="'.sanitize_email($_POST['Email']).'" />
						<Input Name="WebPassword" Value="'.sanitize_text_field($_POST['WebPassword']).'" />
						<Input Name="HomeTelNo" Value="'.sanitize_text_field($_POST['HomeTelNo']).'" />
						<Input Name="MobileTelNo" Value="'.sanitize_text_field($_POST['MobileTelNo']).'" />
						<Input Name="WorkTelNo" Value="'.sanitize_text_field($_POST['WorkTelNo']).'" />
				</InputData>
			</ChameleonIAPI>';


				$encoded = 'Xml='.$request.'&Action=postxml&AuthKey='.$AuthKey.'&AuthPassword='.$AuthPassword;
				 $ch = curl_init($feed_url);
				 curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
				 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
				 curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
				 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				 curl_setopt($ch, CURLOPT_VERBOSE, 0);
				 $result = curl_exec($ch);
				 $result = str_replace('utf-16','utf-8',$result);
				 $xml = simplexml_load_string($result);
				 $json = json_encode($xml);
				 $array_contactid = json_decode($json,TRUE);
				 curl_close($ch);
				 session_start();
				 

				$ContactId = $array_contactid['ContactId']; 
				echo '</br>';
				$ftype = end(explode('.',@$_POST['cv']));
				$fcontent = file_get_contents($_FILES['cv']['tmp_name']); 
				$fcontent =  base64_encode ($fcontent);
			   
					  
				  
				 

				 $res_conid = '<ChameleonIAPI><Method>CandidateRegister</Method>
				  <APIKey>'.$APIKey.'</APIKey>
				<UserName>'.$UserName.'</UserName>
				<InputData><Input Name="ContactId" Value="'.$ContactId .'" />
				<Input Name="CVDocType" Value="'.$ftype	.'" />
				 <Input Name="CVBase64" Value="'.$fcontent.'"/>
				</InputData>
				</ChameleonIAPI>';

				 $encoded = 'Xml='.$res_conid.'&Action=postxml&AuthKey='.$AuthKey.'&AuthPassword='.$AuthPassword;
				 $ch = curl_init($feed_url);
				 curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
				 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
				 curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
				 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				 curl_setopt($ch, CURLOPT_VERBOSE, 0);
				 $result = curl_exec($ch);


				 curl_close($ch); 


					$_SESSION['Forename'] = '';
					$_SESSION['Surname'] = '';
					$_SESSION['WebPassword'] = '';
					$_SESSION['HomeTelNo'] = '';
					$_SESSION['MobileTelNo'] = '';
					$_SESSION['WorkTelNo'] = '';
					$_SESSION['cv'] = '';
					$_SESSION['Email'] ='';

					 if($setting->thank_you_page == 0)
					 {
					  
					  $redpage = "index.php";
					 }
					 else
					 {
						 $redpage = "?page_id=$setting->thank_you_page";
					 }



				 echo "<script type=\"text/javascript\">alert('Thank you for applying for this job.');  window.location = '$redpage'; </script>";
		

			 } // if email not exit;
			 else
			 {

			 
				if ( !session_id() )
						session_start();

				  
					$_SESSION['Forename'] = sanitize_text_field($_POST['Forename']);
					$_SESSION['Surname'] = sanitize_text_field($_POST['Surname']);
					$_SESSION['WebPassword'] = sanitize_text_field($_POST['WebPassword']);
					$_SESSION['HomeTelNo'] = sanitize_text_field($_POST['HomeTelNo']);
					$_SESSION['MobileTelNo'] = sanitize_text_field($_POST['MobileTelNo']);
					$_SESSION['WorkTelNo'] = sanitize_text_field($_POST['WorkTelNo']);
					$_SESSION['cv'] = sanitize_file_name($_FILES['cv']['name']); 
					$_SESSION['Email'] =  sanitize_email($_POST['Email']);


				$job_settings_str = 'Email Address Already Exits';

			 }
			}
			$job_settings_str = '';
			$job_settings_str .= '<script type="application/javascript">
				function app_validate()
				{
					validation_string = new Array();
					if(document.getElementById("Forename").value == "")
					{
						document.getElementById("Forename").focus();
						validation_string.push("Forename");
					}
					if(document.getElementById("Surname").value == "")
					{
						document.getElementById("Surname").focus();
						validation_string.push("Surname");
					}
					if(document.getElementById("Email").value == "")
					{
						validation_string.push("Email");
						document.getElementById("Email").focus();
					}
					if(document.getElementById("cv").value == "")
					{
						validation_string.push("CV");
						document.getElementById("cv").focus();
					}
					if(validation_string != ""){
						alert("Please enter "+validation_string.toString());
						return false;
					}
					if(document.getElementById("Email").value != "")
					{
							var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
							if(document.getElementById("Email").value.match(emailExp)){
							}else{
								alert("Please enter Valid Email Address");
								document.getElementById("Email").focus();
								return false;
							}
					}
					return true;
				}
				</script> 
				</script>';
				$job_settings_str .= '<div class="application-edit front-end-edit">
					<h3>Apply for position: '.$_REQUEST['job_title'].'</h3>
					<form id="form-application" action="" method="post" class="form-validate" enctype="multipart/form-data" >        		
						<input  type="hidden" class="required"  id="job_id" name="job_id"   value="'.$_REQUEST['jobid'].'">
						<input type="hidden" class="required" value="'.$_REQUEST['job_title'].'" id="job_title" name="job_title">
						<table border="0">
							<tr>							
								<td>Forename <span class="star">&nbsp;*</span> </td>
								<td><input type="hidden" name="TitleId" value="1"><input type="text" name="Forename" id="Forename"  value="'.@$_SESSION['Forename'].'" /></td>
							</tr>
							<tr>
								<td>Surname <span class="star">&nbsp;*</span> </td>
								<td><input type="text" name="Surname" id="Surname"  value="'.@$_SESSION['Surname'].'" /></td>
							</tr>
							<tr>
								<td>Email<span class="star">&nbsp;*</span> </td>
								<td><input type="text" name="Email" id="Email" value="'.@$_SESSION['Email'].'" /></td>
							</tr>
							<tr>
								<td>Password </td>
								<td><input type="password" name="WebPassword" id="WebPassword"  value="'. @$_SESSION['WebPassword'].'" /></td>
							</tr> <tr>
								<td>Home Tel </td>
								<td><input type="text" name="HomeTelNo" id="HomeTelNo"  value="'.@$_SESSION['HomeTelNo'].'" /></td>
							</tr> <tr>
								<td>Mobile Tel </td>
								<td><input type="text" name="MobileTelNo" id="MobileTelNo"  value="'.@$_SESSION['MobileTelNo'].'" /></td>
							</tr> <tr>
								<td>Work Tel  </td>
								<td><input type="text" name="WorkTelNo" id="WorkTelNo"  value="'.@$_SESSION['WorkTelNo'].'" /></td>
							</tr>
							<tr>
								<td>Attach CV <span class="star">&nbsp;*</span></td>
								<td><input type="file" name="cv"  id="cv"  value="'.@$_SESSION['cv'].'" /></td>
							</tr>
							<tr>
								<td><input type="submit" value="Submit" name="apply-submit"  class="jobapply" onclick="return app_validate();"/>
								<button class="jobapply" onclick="window.history.back();">Back</button>
								</td>
							</tr>
						</table>
					 </form>
				</div>';
				return $job_settings_str;
		break;
		default:
			
					global $wpdb;
					$setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."jobs_settings"));
					$feed_url = $setting->http_url;//'https://jobs.chameleoni.com/api/PostXML/PostXml.aspx';
					$AuthKey = $setting->authKey;
					$AuthPassword = $setting->authPassword;
					$APIKey = $setting->aPIKey;
					$UserName = $setting->userName;
					$Thank_you_page = $setting->thank_you_page;
					$feed_location = $setting->feed_location;
					$feed_type = $setting->feed_type;
					$feed_salary = $setting->feed_salary;
					$feed_summary = $setting->feed_summary;
					$page_size = $setting->number_of_jobsper_Page; 
					$summary_characters =  $setting->summary_characters;
					$t1tag = 'Web Location';
						/* Get T2 tags from T1 tag */
						$request = '<ChameleonIAPI>
							<Method>TagListT2ForT1</Method>
							<APIKey>'.$APIKey.'</APIKey>
							<UserName>'.$UserName.'</UserName>
							<Filter>
								<Param Name="T1" Value="'.$t1tag.'" />
							</Filter>
						</ChameleonIAPI>';
						$encoded = 'Xml='.$request.'&Action=postxml&AuthKey='.$AuthKey.'&AuthPassword='.$AuthPassword;
						$ch = curl_init($feed_url);
						curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_VERBOSE, 0);
						$result = curl_exec($ch);
						curl_close($ch);
						$result = str_replace('utf-16','utf-8',$result);
						$xml = simplexml_load_string($result);
						$json = json_encode($xml);
						$array = json_decode($json,TRUE);
						if($array['Status'] == 'Pass'){
							$job_taglist1 =  @$array['TagListT2ForT1']['Tag'];	
						}
					$t2tag = 'Web Expertise';
						/* Get T2 tags from T1 tag */
						$request = '<ChameleonIAPI>
							<Method>TagListT2ForT1</Method>
							<APIKey>'.$APIKey.'</APIKey>
							<UserName>'.$UserName.'</UserName>
							<Filter>
								<Param Name="T1" Value="'.$t2tag.'" />
							</Filter>
						</ChameleonIAPI>';
						$encoded = 'Xml='.$request.'&Action=postxml&AuthKey='.$AuthKey.'&AuthPassword='.$AuthPassword;
						$ch = curl_init($feed_url);
						curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_VERBOSE, 0);
						$result = curl_exec($ch);
						curl_close($ch);
						$result = str_replace('utf-16','utf-8',$result);
						$xml = simplexml_load_string($result);
						$json = json_encode($xml);
						$array = json_decode($json,TRUE);
						if($array['Status'] == 'Pass'){
							$job_taglist2 =  @$array['TagListT2ForT1']['Tag'];
						}
						$post = @$_REQUEST['post'];
						$PageNo = (@$_REQUEST['PageNo']) ? @$_REQUEST['PageNo'] : ((@$_SESSION['PageNo']) ? @$_SESSION['PageNo'] : 1);
						$_SESSION['PageNo'] = $PageNo;
						$filter = (@$_REQUEST['filter']) ? @$_REQUEST['filter'] : ((@$_SESSION['filter']) ? @$_SESSION['filter'] : array());
						$_SESSION['filter'] = $filter;
						$permanent = true;
						$contract = true;
						$temporary = true;
						if(count($_SESSION['filter']) > 0){
							if(isset($_SESSION['filter']['permanent'])){ $permanent = true;} else {$permanent = false;}
							if(isset($_SESSION['filter']['contract'])) {$contract = true;} else {$contract = false;}
							if(isset($_SESSION['filter']['temporary'])) {$temporary = true;} else {$temporary = false;}
						}
						$page_rows = 5;
						$t1tag=$t2tag='';
						$t1tag = @$_REQUEST['t1tag'] ? @$_REQUEST['t1tag'] : '' ;
						$t2tag = @$_REQUEST['t2tag'] ? @$_REQUEST['t2tag'] : '';
						$taglistpass = $t1tag.','.$t2tag;

						$taglistpass = trim($taglistpass,',');
						$request = '<ChameleonIAPI>
										<Method>SearchVacancies</Method>
										<APIKey>'.$APIKey.'</APIKey>
										<UserName>'.$UserName.'</UserName>
										<Filter>
													  <Param Name="Permanent" Value="'.$permanent.'" />
													  <Param Name="Contract" Value="'.$contract.'" />
													  <Param Name="Temporary" Value="'.$temporary.'" />
													  <Param Name="PageNo" Value="'.$_SESSION['PageNo'].'" />
													  <Param Name="PageSize" Value="'.$page_size.'" />
													  <Param Name="TagIDCSV" Value="!AND'.$taglistpass.'" />
										</Filter>
									</ChameleonIAPI>';
						$encoded = 'Xml='.$request.'&Action=postxml&AuthKey='.$AuthKey.'&AuthPassword='.$AuthPassword;
						$ch = curl_init($feed_url);
						curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_VERBOSE, 0);
						$result = curl_exec($ch);
						curl_close($ch);
						$result = str_replace('utf-16','utf-8',$result);
						$xml = simplexml_load_string($result);
						$json = json_encode($xml);
						$array = json_decode($json,TRUE);
						/*echo $array['SearchVacanciesCount'];
						echo '<pre>';
						print_r($array['SearchVacanciesCount']);
						echo '</pre>';*/
						//exit;
						if($array['Status'] == 'Pass'){
							
							if($array['SearchVacanciesCount'] == 1)
							{	
							  $jobs_vac =  @$array['Vacancies'];	
							  $total = (isset($array['Vacancies']['Vacancy'])) ? $array['Vacancies']['Vacancy']['TotalCount'] : $array['Vacancies']['Vacancy']['TotalCount'];
							}
							else
							{ 
								$jobs_vac =  @$array['Vacancies']['Vacancy']; 
								@$total = (isset($array['Vacancies']['Vacancy'][0])) ? $array['Vacancies']['Vacancy'][0]['TotalCount'] : $array['Vacancies']['Vacancy']['TotalCount'];
							}
						}  
						$_SESSION['currentpage']  = $_SESSION['PageNo'];
				?>
			<?php
			$job_listing = "";
			$job_listing .=	'<div class="chameleoni_listing">
				<div class="search_form">
				<div id="vacancy_search_top"><h3>Vacancy Search</h3></div>
				<form name="frmsearch" action="" method="post">
					<table border="0" style="width:100%">
						<tbody><tr>
							<td>
								<label for="location" class="searchlabel">Location</label>
								<select name="t1tag" id="t1tag">
								<option value="">Select</option>';
								foreach($job_taglist1 as $value )
								  { 
								$job_listing .= '<option value="'. $value['TagId'].'"';
								 if($value['TagId'] == @$t1tag){ ?>
								<?php 
									$job_listing .= 'selected="selected"'; ?>
								<?php } ?>
								<?php $job_listing .= '>'; ?><?php $job_listing .= $value['Tag']?>
								<?php $job_listing .= '</option>'; ?>
								
								<?php }
								?>
								<?php $job_listing .= '</select>
							</td>
						   </tr> 
						   <tr>
							<td>
								<label for="tag" class="searchlabel">Sector</label>
								<select name="t2tag" id="t2tag">
								<option value="">Select</option>';
								foreach($job_taglist2 as $value )
								  { ?>
								<?php $job_listing .= '<option value="'. $value['TagId'].'"'; ?> <?php if($value['TagId'] == @$t1tag){
								$job_listing .= ' selected="selected"';
								} 
								$job_listing .= '>'.$value['Tag'].'</option>';
								 }
								
							$job_listing .= ' </select>
							</td></tr>';

							$job_listing .= '<tr>
								<td>
									<input type="checkbox"  id="permanent" name="filter[permanent]"';
									if($permanent){									
										$job_listing .= ' checked="checked" ';
									}
									$job_listing .= ' >';
									$job_listing .= '<label for="permanent">Permanent</label>
									<input type="checkbox"  ';
									if($contract){
									 $job_listing .= ' checked="checked" ';
									}
									$job_listing .= 'id="contract" name="filter[contract]" >
									<label for="permanent">Contract</label>
									<input type="checkbox"  id="temporary" name="filter[temporary]" ';
									if($temporary){
                                    $job_listing .= ' checked="checked" ';
									}
									$job_listing .= ' >';
									$job_listing .= ' <label for="permanent">Temporary</label>
								</td>
							</tr>
							<tr>
							<td><input type="submit" name="submit" value="Search" class="jobapply"></td>
						</tr>    
					</tbody></table>
				</form>
				</div>
				</div>';
				
				global $post;
				$pageid = $post->ID;
				$det =  JOBINFO_URL . '/jobs_details.php';
				//echo '<a href"'.$det.'">View</a>';

				if(!empty($jobs_vac)){
					$job_listing .= '<h2 class="title">Search Results</h2>';
				   foreach($jobs_vac as $job){
					$job_listing .= '<div class="job">
						<p><span class="type"><label><b>Job Title:</b></label>'. esc_html($job['JobTitle']).'</span></p>';
						if($feed_location == '1') { 
							$job_listing .= '<p><span class="location"><label><b>Location:</b></label>';
							$job_listing .=  esc_html(is_array($job['LocationTag'])?implode(', ',$job['LocationTag']):$job['LocationTag']);
							$job_listing .= '</span><br></p>';
						}
						$job_listing .= '<p><span class="type"><label><b>Job Reference:</b></label>'.esc_html(($job['Reference'])).'</span></p>';
						if($feed_type == '1') {
						$job_listing .= '<p><span class="type"><label><b>Type:</b></label>'. esc_html(($job['JobType'])).'</span></p>';
						}  
						if($feed_salary == '1' && $job['MinSalary'] != '') { 
						$job_listing .= '<p><span class="salary"><label><b>Salary:</b></label>&#163;';
						$job_listing .= ($job['MinSalary']) ? esc_html($job['MinSalary']) .' to &#163;'. $job['MaxSalary'] : 'Negotiable';
						$job_listing .= '</span><br /></p> ';
						 } 
						$job_listing .= '<p><span class="type"><label><b>Close Date:</b></label>';
						$job_listing .= esc_html(date('Y-m-d',strtotime($job['CloseDate'])));
						$job_listing .= '</span></p>';
						if($feed_summary == '1' && $job['JobDescription'] != '') {
						      
						$job_listing .= '<p><span class="summary"><label><b>Summary:</b></label>';
						$job_listing .=  ($job['JobDescription']) ? substr(nl2br($job['JobDescription']),0,$summary_characters).((strlen($job['JobDescription']) > $summary_characters) ? '.....' : '') : '';
						$job_listing .= '</span><br /></p> ';
						 }  
					  $job_listing .= ' <p><span class="consultantname"><label><b>Consultant Name:</b></label>';
					  $job_listing .=  esc_html($job['ConsultantName']);
					 $job_listing .= ' </span></p>
					   <p> <span class="consultantemail"><label><b>Consultant Email:</b></label><a href="mailto:';
					   $job_listing .=  esc_html($job['ConsultantEmail']).'"'. $job['ConsultantEmail'].'</a></span></p>';
					 $job_listing .= ' <span><a class="view_morebtn" href="?page_id='. $pageid.'&task=jobdetails&jobid='. esc_html($job['VacancyID']).'">View More</a> </span>
					  </div>';
					  

					}  // foreach 
				

					 $job_listing .= '  <form name="frmpagination" id="frmpagination" method="post" action="">';


			 //This is the number of results displayed per page 
				//This checks to see if there is a page number. If not, it will set it to page 1 

				 if (!(isset($pagenum))) 
				 { 
					 $pagenum = 1; 
				 } 
				 $page_rows = 5; 
				 //This tells us the page number of our last page 
				 $last = ceil($total/$page_rows); 
				 //this makes sure the page number isn't below one, or more than our maximum pages 
				 if ($pagenum < 1) 
				 { 
					 $pagenum = 1; 
				 } 
				 elseif ($pagenum > $last) 
				 { 
					 $pagenum = $last; 
				 } 
				 $pagenum;
			
				 $job_listing .= '<ul class="pageul">';
				
				if($_SESSION['currentpage'] != 1){
			
				 $job_listing .= "<li onclick=\"nextprevious('p')\"><b>Pervious << </b> </li>"; ?>
				<!--<input    type="button" name="pervious" value="<<"  />-->
				<?php 
				}
				for($t=1;$t<=$last;$t++){
				 $job_listing .= "	  <li onclick=\"mysubmit( $t );\" ><b> $t </b> </li>";
					
				} ?>
				<?php if($last != $_SESSION['currentpage']){ ?>
					<!-- <input    onclick="nextprevious('n')" type="button" name="next" value=">>"  />-->
				<?php $job_listing .= "	 <li onclick=\"nextprevious('n')\"><b>Next >></b> </li>";
				 }
				$job_listing .= '	 </ul>
				<input type="hidden" name="PageNo" id="PageNo" value="1" />
				</form>';
				 } 
				else { 
					$job_listing .= '<h3>No listing found</h3>';
				 }
				$job_listing .= '<script type="text/javascript">function nextprevious(ch){if(ch == "p"){document.getElementById("PageNo").value = ';
				$job_listing .= $_SESSION['currentpage'] -1;
				$job_listing .= '}else if(ch == "n"){document.getElementById("PageNo").value = ';
				$job_listing .= $_SESSION['currentpage'] + 1 ;
				$job_listing .= '} document.frmpagination.submit();} function mysubmit(PageNo){document.getElementById("PageNo").value = PageNo; document.frmpagination.submit();}</script>';

				$job_listing .="<style>
				.pageul li{ 
				list-style:none;cursor:pointer;
					display:inline-block;
					margin:0 5px;
					border:solid 1px #ccc;
					border-radius: 3px;
					padding:0 3px;
					
				}
				.pageul{
					margin:5px 0;
				text-align:center;
				}
				</style>";				
							

			return $job_listing;
			
		break;
	}
	
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