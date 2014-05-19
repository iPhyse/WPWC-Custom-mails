<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Custom_Order_Status_Email{

    function __construct($email, $subject, $message){
        
        // Mail body and design, this represents the design of your mail
        $mail_body = '
			<div>
				<!-- Mail title pointer -->
				<div class="mail_header">
					{header}
				</div>
				<!-- Mail body content pointer -->
				<div class="mail_body">
					{message}
				</div>
			</div>';

        // Replacing the pointers with the correct content
        $mail_body = str_replace("{header}", $subject, $mail_body);
        $mail_body = str_replace("{message}", $message, $mail_body);

        // Set-up the headers
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		// Sender name and email
        $headers .= 'From: YourName/Company <no-reply@yourdomain.com>' . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion();

        // Push the mail to the customer
        mail($email, $subject, $mail_body, $headers);

    }
}