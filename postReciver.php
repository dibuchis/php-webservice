<?php header('Content-type: text/xml'); ?>
<?php include("lib/cnx.php"); ?>
<?php
/* Php Webservice
*
* postReciver.php
*
* reciver Post data for werservice
* save Data for a DB System
* and Notifying for mailer system
*
* by: Chris Regalado
* dibuchis@gmail.com
* github https://github.com/dibuchis/php-webservice
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require 'lib/phpMailer/Exception.php';
require 'lib/phpMailer/PHPMailer.php';
require 'lib/phpMailer/SMTP.php';


//Simple verify POST control 
if(isset($_POST['image']) && isset($_POST['name']) && isset($_POST['email']) && isset($_POST['cod']) && isset($_POST['city']) && isset($_POST['date'])){

	$error = null;

	$image64 = $_POST['image'];
	$name = utf8_decode($_POST['name']);
	$email = $_POST['email'];
	$cod = $_POST['cod'];
	$city = $_POST['city'];
	$date = $_POST['date'];
	$ipServ = $_SERVER['REMOTE_ADDR'];
	$ipProx = ''; //For posible Proxy Mask

	//Connecting DataBase
	$cnx = new DB();
	if($cnx->conect()){
		$link = $cnx->getLink();
	}else{
    
    // Returning XML Code for error DB result
	 	echo '<webservice>';
		echo '<result>false</result>';
		echo '<status>DataBase Connection Error</status>';
		echo '</webservice>';
		$error = "Error: DataBase Connection." . " " . date("Y-m-d H:i:s");
		doDebug($error, false);
		die;

	}

	// In case you have more or different text
	// you have to validating them,
  // if you want to apply some validation data
	// just use a regexp expresion for testing your data
	// eg. if($validId($text))  
  // and save your validations on a function set
  if($image64 != ''){

		//Save Query DB
		$query = "INSERT INTO webServiceData (`image64`, `cod`, `name`, `email`, `city`, `date`, `ip_server`, `ip_proxserv` ) VALUES ('$image64', '$cod', '$name', '$email', '$city', '$date', '$ipServ', '$ipProx' )";
		$result = mysql_query($query,$link);

		//Varifying INSERT Query
		if( $result == true ){

            $queryId = "SELECT id FROM webServiceData WHERE cod = '" . $cod . "' ";
            $resultId = mysql_query($queryId, $link);
            
            $model = mysql_fetch_object($resultId);
			
			$idPhoto = $model->id;

      // Returning XML Code for true result
			echo '<webservice>';
			echo '<result>true</result>';
			echo '<status>Successful Operation</status>';
			echo '</webservice>';

			// return $output;

            $imageMail = str_replace(" ", "+", $image64);
            
			$message = "Message: Successfull INSERT :: " . date("Y-m-d H:i:s");
			doDebug($message, false, false, $email, $imageMail, $name, $cod, $idPhoto);

		}else{

      // Returning XML Code for SQL INSERT Error
			echo '<webservice>';
			echo '<result>false</result>';
			echo '<status>Failed operation SQL Insert!</status>';
			echo '</webservice>';
			$error = "Error: SQL Insert." . " " . date("Y-m-d H:i:s");
			doDebug($error);
		}

	}else{
			// Returning XML Code for Data Validation Error
			echo '<webservice>';
			echo '<result>false</result>';
			echo '<status>Data Validation Error</status>';
			echo '</webservice>';
		  $error = "Error: Data Validation :: " . ($vCed == false ? " VCED:ERR " : "") . ($vNom == false ? " VNOM:ERR ": "") . ($vApe == false ? " VAPE:ERR ": "") . ($vImg == false ? " VIMG:ERR ": "") . date("Y-m-d H:i:s");
		  doDebug($error);
	}

	  // Disconnecting from the db
    $cnx->close();


}else{

		// Returning XML Code for Unset Post Data
		echo '<webservice>';
		echo '<result>false</result>';
		echo '<status>Unset PostData Error</status>';
		echo '</webservice>';
	$error = "Error: Unset PostData." . " " . date("Y-m-d H:i:s");
	doDebug($error);

}

/** MY OWN DEBBUGUER OUTPUT **/
function doDebug($debugIn = null, $dumpFormat = false, $error = true, $emailSend = '', $image64 = '', $nameSend = '', $cod = '', $id = 0){

	$debugFile = "/home/server/public_html/domain.com/webservice/error.log";
	$emailFile = "/home/server/public_html/domain.com/webservice/emailError.log";

    // open the output file for writing
    $ifp = fopen( "/home/server/public_html/domain.com/photos/photo-" . $cod . ".jpg", 'wb' ); 

    // In case you need to seppare header string from base 64 image apply this code
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    // $image64 = explode( ',', $imageData );

    fwrite( $ifp, base64_decode( $image64 ) );

    // clean up the file resource
    fclose( $ifp ); 

	$debug = fopen($debugFile, "a") or die("Log Error.");

	if (is_array($debugIn) && $dumpFormat == false){
		$debugIn = print_r($debugIn, true);
	}

	if ($dumpFormat == true){
		ob_start();
		var_dump($debugIn);
		$debugIn = ob_get_clean();
	}
	fwrite($debug, $debugIn . "\n");
	fclose($debug);

	$headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-type: text/html; charset=UTF-8';
	$headers[] = 'From: ' . 'PHP Webservice' . '<info@webservice.com>';

	$message = '<div style="width:300px;height:fit-content;background-color:#cce6ff;border-radius:7px;padding:20px 20px 20px 20px;">';
	$message .= '<center><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAF4AAABeCAYAAACq0qNuAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA9FJREFUeNrsnNGRmzAQhu0bN3BXAg1kJlwJpARSAnnKM1cCec4TLsEugSvBzkwKwCXYJZCVs7pZbxDGBiOR+f+ZHVkgpNWnZQX2zS2bpllA0+sJCAAe4CGAB3gI4AEeAniAhwAe4CGAB3iAhwAe4CGAB3gI4AEeAniAhwAe4CGAD0WrMTr59PlnQkVG9swWO5q+kx1M+fvX921HfzkVhT5O1yyv+NH7urF99gKenU57tEu4zGjiJyrfaDJrT0Hn1WefqcZEWUmTKWeUIUbzOYQcbyIpXcxLg31+NPgXk185x76SuW7RIiCok/j8UPDk/El83pN9o48/WppGFEFxCNSn8tlHqll3bHahanSfnzxE1KFj4wpSj/A5pBeo0wzfg+72eTW1p5QXM8epQ49rqytNotB8DgI8TcDcmrnjdn6/4WVmTj5PD55fyyW0zJEX16Hkjql8fnTEVz3anF/DA8rbk/jse3M1E/gin51nsqEO9nnlcQLmVn27ZQL3fjvp02df4E8iP+65bjakbcezcQgR/XCfHwqeHH2Z24P5VD7jFyhPAniAB3gI4AEeAvj/Q0v8hyZEPMBDAA/wUEDgl8tlTdaQZVxPuV5wPeF6faWfhi3xAYHGjYzNKeLt16T2a1TrfKyOHwIPwB1ZPSX8oeDfFeBYlZFqF6oObJP9Ejbmn2lL0PrH4aB/2qN3mde5ba57xwIsOF8nMtXwHmD3hZJML5A5f+Q2sci/Gz5ursvFGK39cd20z8xnPraT11NZcT1t22P4uL2m5GP5Ff9vWu27jSO7YYu5LLg0f8Zc/R3i3Dbl4yafbvhzyedsH0e2czs+V3HdXGOgZTf2Z8cvHG3kHBI+l4ljFY9biPrFeHexGwKenTwK0A07aUtzrlYAUzHZo4KQ8l3ihCLG7dPfTsBMBLhILa4Gb+eUi/GkTxfj+QJfqUi3TpV2osrxC3NM/KMu+rHQnm/oL1W+yn4/wKrjsexLLVrrePfYGJvrnh0zdqBOT5T7TiLf6yea9Y2Pl/YPhzKxEF979qc39S3fIaljj2p7MHD5M3hHHxrxuYiAUt0FjcjJlaw7IjFtizhuE6uo7tNf4vB1J9NExx3XlmqioczGSjWJdlQtht6wjpyWCrEocjOsWxYxb0ldffrT4CNxbtMBvujYXI+invkEH3U9FbREXC0nICZeiwXbiE1sJ9pvZMRd6e8f8Hyu1ndKW3vVd9Y13j2GH0Lw7STAQwAP8BDAAzwE8AAPATzAQwAP8AAPBAAP8BDAAzwE8AAPATzAQwAP8BDAAzzAQ170R4ABAD5o0ZIm78asAAAAAElFTkSuQmCC" ></center>';
	$message .= '<h2 style="color:#471800;text-align:center;">Webservice</h2>';
	$message .= ($error == true ? '<p>An Error has been detected.</p>' : '<p>Registered Activity.</p>');
	$message .= '<h3>Detail: </h3>';
	$message .= ($error == true ? '<p style="color:red;">' : '<p style="color:green;">');
	$message .= $debugIn;
	$message .= '</p>';
	$message .= '<span>Atte.</span><br/>';
	$message .= '<span>Administration</span>';
	$message .= '</div>';

	$subject = ($error == true ? 'Alert! - Webservice Error' : 'Notification! - Webservice Activity');

	mail( "webadmin@webservice.com", $subject , $message, implode("\r\n", $headers));

	

	if($emailSend !== ''){

	$headersMailing[] = 'MIME-Version: 1.0';
	$headersMailing[] = 'Content-type: text/html; charset=UTF-8';
	$headersMailing[] = 'From: PHP Webservice <info@webservice.com>';

	$mailing = '';
	$mailing .= '<html>';
		$mailing .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf8">';
			$mailing .= '<title>Webservice</title>';
		$mailing .= '</head>';
		$mailing .= '<body>';
			$mailing .= '<div class="container" style="position:block; display:table; margin:auto; width:640px;height:500px;background:url(\'https://www.domain.com/webservice/images/mailing/back.jpg\') left top / 100% 100% no-repeat ;">';
				$mailing .= '<div class="header" style="margin:22px 0px; height:113px; width:100%; display:block; position:relative;">';
					$mailing .= '<div style="width:30%;padding:0px;display:block;position:relative;float:left;">';
						$mailing .= '<center><img style="height:120px;" src="https://www.domain.com/webservice/images/mailing/logo.png" alt="Webservice"/></center>';
					$mailing .= '</div>';
					$mailing .= '<div style="width:60%;padding:5px;display:block;position:relative;float:right;">';
						$mailing .= '<center><img style="margin-top:-20px;" src="https://www.domain.com/webservice/images/mailing/header.png"></center>';
				$mailing .= '</div>';
				$mailing .= '</div>';
				$mailing .= '<div class="body" style="margin:22px 0px; height:100%; width:100%; display:block; position:relative;">';
					$mailing .= '<div class="link" style="position: relative; float: left; width:50%;">';
						$mailing .= '<a style="text-decoration:none; color:none;" href="http://www.domain.com/showPhoto.php?id=' .  $id . '">';
						    $mailing .= '<img style="margin-left:25px; margin-bottom: 25px;" src="https://www.domain.com/webservice/images/mailing/link.png" >';
						$mailing .= '</a>';
					$mailing .= '</div>';
					$mailing .= '<div class="link" style="position: relative; float: left; width:50%;">';
					$mailing .= '<div class="photoWrapper" style="width:260px; height:300px; padding-top:15px; margin: 0px auto; background-color:#fff; position: relative; box-shadow:2px 2px 20px #000;">';
						$mailing .= '<img class="photoCode" style="position:relative; display:block; display: ruby-text-container; width:230px; height:180px; margin:15px auto;" src="https://www.domain.com/photos/photo-' . $cod . '.jpg" >';
						$mailing .= '<div class="photoName" style="color:#19266d; font-size:20px;  text-align:center; position:relative; top:20px; font-family: fantasy, sans-serif; display:block; width:230px; height:50px; margin:35px auto 15px;" >' . $nameSend . '</div>';
					$mailing .= '</div>';
					$mailing .= '</div>';
				$mailing .= '</div>';
			$mailing .= '</div>';
    	$mailing .= '</body>';
	$mailing .= '</html>';	

    $mail = new PHPMailer(true); 
    try {
        //Server settings
        $mail->CharSet = 'UTF-8';
		    $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.domain.com';                      // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'send@webservice.com';              // SMTP username
        $mail->Password = 'mypass';                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;                                    // TCP port to connect to
    
        //Recipients
        $mail->setFrom('info@webservice.com', 'PHP Webservice');
        $mail->addAddress($emailSend, $nameSend);     // Add a recipient
   
        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'PHP Webservice';
        $mail->Body    = $mailing;
        $mail->AltBody = 'If can\'t see this email please enter to  <a href="http://www.webservice.com/mailing.php?id=' . $id . '" />';
    
        $mail->send();
    } catch (Exception $e) {
        $errorMail = 'Mailer Error: ' . $mail->ErrorInfo;
	    	$messageEmail = "PHP Webservice - Send Error " . $emailSend . " whit cod: " . $cod . " whit ID: " . $id . " " . date("Y-m-d H:i:s");
		    mail("webadmin@webservice.com", "PHP Webservice - Email Error", $errorMail);
    	
    	  $debugEmail = fopen($emailFile, "a") or die("Log Error.");
    		
    	  fwrite($debugEmail, $messageEmail . "\n");
    	  fclose($debugEmail);
    }
	}
}

// Ten digits validation Text Example
// Apply this function and duplicate for more validatings ways
function validId($id){
	$validToken = '/^([0-9]{10})$/';
	$strID = strval($id);
	if(preg_match($validToken, $strID)){
		return true;
	}else{
		return false;
	}
}
