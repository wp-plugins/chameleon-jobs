<?php
  
$task = $_REQUEST['task'];
if($task != 'forgetpass')
{ ?>

<form name="login_form" action="" method="post">
<h2>Login</h2>
<p><label>Username</label><input type="text" name="username" id="usernme"></p>
<p><label>Password</label><input type="password" name="password" id="password"></p>
<p><input type="submit" name="login_submit" value="Login"></p>
</form>
<p><a href="?page_id=<?php echo $_REQUEST['page_id'];  ?>&task=forgetpass">Forgot your password</a></p>
	<?php 
	if(isset($_POST['login_submit']) == 'Login')
	{
		

	} // if

}
 else { ?>

 <form name="fpass" id="fpass" action="" Method="POST">
<h3>Forget Password</h3>
<input type="text" name="Email" id="Email">
<input type="submit" name="fpass_submit" value="Submit" class="btn btn-primary">  
</form>
		<?php 
		if(isset($_POST['fpass_submit']) == 'Submit')
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


			 $request_fpassemail = '<ChameleonIAPI>
    <Method>PasswordReminder</Method>
    <APIKey>'.$APIKey.'</APIKey>
    <UserName>'.$UserName.'</UserName>
    <InputData>
         <Input Name="Email" Value="'.sanitize_email($_POST['Email']).'" />
    </InputData>
</ChameleonIAPI>';



    $encoded = 'Xml='.$request_fpassemail.'&Action=postxml&AuthKey='.$AuthKey.'&AuthPassword='.$AuthPassword;
     $ch = curl_init($feed_url);
     curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
     curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
     curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_VERBOSE, 0);
     $result = curl_exec($ch);

    curl_close($ch);
    echo 'Your Mail Successfully Send.';
    
    
    

		} 
 } // else
 ?>