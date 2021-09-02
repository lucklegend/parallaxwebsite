<?php
foreach ($_POST as $key => $value) {
//echo '<p><strong>' . $key.':</strong> '.$value.'</p>';
}
 
$name = $_POST['name'];
$email = $_POST['email'];
$messagez = $_POST['message'];
$to_email= 'info@jade-company.com';
$to_email2= 'janno@axon.com.sg';
//$to_email2= 'elam@axon.com.sg';

require_once("mailer_class.php");
// require_once("recaptchalib.php");

// your secret key
// secret Key = 6LeKgj0cAAAAAA6BEmTYYW0CdDdDMevu8pBoHMci jade-company
$secret = "6LdThT0cAAAAADxpCXcTJyLaQUhkqkCnwvh3Kygs";
 
// empty response
$response = null;
 
// check secret key
// $reCaptcha = new ReCaptcha($secret);

// if submitted check response
// if ($_POST["g-recaptcha-response"]) {
//     $response = $reCaptcha->verifyResponse(
//         $_SERVER["REMOTE_ADDR"],
//         $_POST["g-recaptcha-response"]
//     );
// }

$_siteVerifyUrl = "https://www.google.com/recaptcha/api/siteverify?";
$data = array (
  'secret' => $secret,
  'remoteip' => $_SERVER["REMOTE_ADDR"],
  'v' => "php_1.0",
  'response' => $_POST["g-recaptcha-response"]
);
$req = "";
foreach ($data as $key => $value) {
    $req .= $key . '=' . urlencode(stripslashes($value)) . '&';
}
// Cut the last '&'
$req = substr($req, 0, strlen($req)-1);

// $response = file_get_contents($_siteVerifyUrl . $req);

function get_web_page($url) {
  $options = array(
      CURLOPT_RETURNTRANSFER => true,   // return web page
      CURLOPT_HEADER         => false,  // don't return headers
      CURLOPT_FOLLOWLOCATION => true,   // follow redirects
      CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
      CURLOPT_ENCODING       => "",     // handle compressed
      CURLOPT_USERAGENT      => "test", // name of client
      CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
      CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
      CURLOPT_TIMEOUT        => 120,    // time-out on response
  ); 

  $ch = curl_init($url);
  curl_setopt_array($ch, $options);

  $content  = curl_exec($ch);

  curl_close($ch);

  return $content;
}

$res = get_web_page($_siteVerifyUrl . $req);
$answers = json_decode($res, true);

if (trim($answers['success']) == true) {
  // echo 'yes sa wakas';
} else {
  echo '<script language=JavaScript>';
	echo 'alert ("Error PLS check whether Human or Robot");';
	echo 'self.location.href="http://axonsg.com/projects/jade-new/"';
	echo '</script>';
	exit;
}

// if ($response != null && $response->success) {
// 	// echo "Hi " . $_POST["name"] . " (" . $_POST["email"] . "), thanks for submitting the form!";
// }  else {
// 	//echo "Error";
// 	echo '<script language=JavaScript>';
// 	echo 'alert ("Error PLS check whether Human or Robot");';
// 	echo 'self.location.href="http://axonsg.com/projects/jade-new/"';
// 	echo '</script>';
// 	exit;
// }

$message  = '<table width="700" bgcolor="#fff" cellspacing="2" cellpadding="2" border="0">
			  <tr>
				  <td colspan="3" style="color:#FFFFFF; background-color:#0090C5; font-weight:bold"><b>Jade and Company Feedback</b></td>
			  </tr>
			  <tr>
				  <td style="border:#0090C5 1px dotted;">Feedback Date:</td>
				  <td colspan="2" style="border:#0090C5 1px dotted;">'.date('d F Y h:i a').'</td>
			  </tr>
			  <tr>
				  <td style="border:#0090C5 1px dotted;">Name:</td>
				  <td colspan="2" style="border:#0090C5 1px dotted;">'.$name.'</td>
			  </tr>
			  <tr>
				  <td style="border:#0090C5 1px dotted;">Email:</td>
				  <td colspan="2" style="border:#0090C5 1px dotted;">'.$email.'</td>
			  </tr>
			  <tr>
				  <td style="border:#0090C5 1px dotted;">Message:</td>
				  <td colspan="2" style="border:#0090C5 1px dotted;">'.$messagez.'</td>
			  </tr>
			</table>';
	
$emailz = new attach_mailer($name = 'Jade and Company Feedback', 
$from 	= $email, 	
$to 	= $to_email,
$cc 	= $to_email2, 
$bcc 	= 'amit@axon.com.sg',
$subject = 'Jade and Company Feedback - '.date('d F Y'),
$body 	=  $message);
$emailz->process_mail();
cancel_log($body);

		
echo '<script language=JavaScript>';
echo 'alert ("Thank you! We will revert back tou you shortly");';
echo 'self.location.href="http://axonsg.com/projects/jade-new/"';
echo '</script>';

header('Location:http://axonsg.com/projects/jade-new/');
exit;
?>