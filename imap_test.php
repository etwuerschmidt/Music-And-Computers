<?php
	//Set up phpmailer requirements
	$mailpath = 'C:\xampp\PHPMailer-master\PHPMailer-master';
	$path = get_include_path();
	set_include_path($path . PATH_SEPARATOR . $mailpath);
	require 'PHPMailerAutoload.php';
	$mail = new PHPMailer();

	//Connect to database, drop old email table, create new email table
	$db = new mysqli('localhost', 'musi3390', 'musi3390', 'musi3390_db');
	$db->query("drop table emails");

	$sql = "CREATE TABLE emails (
		Name VARCHAR(50),
		Email VARCHAR(100),
		Body VARCHAR(5000),
		ID int PRIMARY KEY)";
	
	$db->query($sql);

	//Set up imap requirements
	$imap = imap_open("{imap.gmail.com:993/imap/ssl}INBOX", 'musi3390.etw', 'musi3390') or die('Cannot connect to Gmail: ' . imap_last_error());
	$numMessages = imap_num_msg($imap);
	echo $numMessages;
	for ($i = 1; $i < $numMessages + 1; $i++) {
	    $header = imap_header($imap, $i);

	    $fromInfo = $header->from[0];
	    $replyInfo = $header->reply_to[0];

	    $details = array(
	        "fromAddr" => (isset($fromInfo->mailbox) && isset($fromInfo->host))
	            ? $fromInfo->mailbox . "@" . $fromInfo->host : "",
	        "fromName" => (isset($fromInfo->personal))
	            ? $fromInfo->personal : "",
	        "subject" => (isset($header->subject))
	            ? $header->subject : "",
	    );

	    $uid = imap_uid($imap, $i);
	    $message = explode("--", imap_fetchbody($imap, $i, 1));
	    $body = str_replace("'", "", $message[0]);

	    //Display emails
	    echo "<ul>";
	    echo "<li><strong>From:</strong>" . $details["fromName"];
	    echo " " . $details["fromAddr"] . "</li>";
	    echo "<li><strong>Subject:</strong> " . $details["subject"] . "</li>";
	    echo "<li><strong>Body:</strong> " . $body . "</li>";
	    echo "</ul>";

	    //Insert email information into database
	    $db->query("INSERT INTO emails VALUES ('$details[fromName]', '$details[fromAddr]', '$body', '$i')");

	    //Email people back
		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->SMTPAuth = true; // enable SMTP authentication
		$mail->SMTPSecure = "tls"; // sets tls authentication
		$mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server; or your email service
		$mail->Port = 587; // set the SMTP port for GMAIL server; or your email server port
		//$mail->Username = "email"; // email username
		//$mail->Password = "password"; // email password
		$mail->Username = "musi3390.etw@gmail.com"; // email username
		$mail->Password = "musi3390"; // email password
		$mail->IsHTML(false);


		$sender = "musi3390.etw@gmail.com";
		$receiver = $details["fromAddr"];
		$subj = "Thanks!";
		$msg = "Thanks for participating $details[fromName]. Watch the screen and wait for your email to appear.";

		// Put information into the message
		$mail->addAddress($receiver);
		$mail->SetFrom($sender);
		$mail->Subject = "$subj";
		$mail->Body = "$msg";

		// echo 'Everything ok so far' . var_dump($mail);
		if(!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		} 

		$mail->ClearAddresses();
	}
	
?>