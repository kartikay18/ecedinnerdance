<?php

class SimpleMailer implements iMailer{

    public $app;

    public function __construct(\Slim\Container $app) {
        $this->app = $app; 
    }

    public function sendTableAssignmentEmail($appHome, $users){
        //TODO:
        return false;
    }

    public function bulkSendAccountCreationEmail($appHome, $emails, $names, $ticketNums, $passwords){
        //TODO: 
        return false;
    }

    public function sendMassEmail($appHome, $template_name, $recipients, $global_merge_vars){    
        //TODO:
        return false;
    }

    public function sendAccountCreationEmail($appHome, $email, $name, $ticketNum, $password){
        $to = $email; // note the comma
        // Subject
        $subject = 'Welcome to ECE Dinner Dance';

        // Message
        $message = "
        <html>
        <head>
          <title>Welcome to ECE Dinner Dance</title>
        </head>
        <body>
            <p>
                Hi ${name},
            </p>
            <p>
                Welcome to ECE Dinner Dance 2017. Here are your login credentials.<br/>
                Ticket Number: ${ticketNum} <br/>
                Password: ${password}<br/>
                Please follow the link and login to complete the registration process<br>
                <a href=\"${appHome}#/login\">${appHome}#/login</a>
            </p>
            <p>
                Cheers,<br/>
                ECE Club
            </p>
        </body>
        </html>
        ";

        $this->app->logger->addInfo('Sending Email: ' . $message );

        // To send HTML mail, the Content-type header must be set
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';

        // Additional headers
        $headers[] = "To: ${name} <${email}>";
        $headers[] = 'From: ECE Club <ece@skule.ca>';

        // Mail it
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }

    public function sendPasswordResetRequestEmail($appHome, $email, $name, $resetLink){
        $to = $email; // note the comma
        // Subject
        $subject = 'ECE Dinner Dance - Password Reset Request';

        // Message
        $message = "
        <html>
        <head>
          <title>ECE Dinner Dance - Password Reset Request</title>
        </head>
        <body>
            <p>
                Hi there!
            </p>
            <p>
                We have received a password reset request. Please follow this link if you have initiated this request.<br/>
                <a href=\"${appHome}#/passwordReset/${resetLink}\">${appHome}#/passwordReset/${resetLink}</a><br/>
                This link will expire in an hour. <br/>
                
                If you did not initiate this request please disregard this email.
            </p>
            <p>
                Cheers,<br/>
                ECE Club
            </p>
        </body>
        </html>
        ";

        $this->app->logger->addInfo('Sending Email: ' . $message );

        // To send HTML mail, the Content-type header must be set
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';

        // Additional headers
        $headers[] = "To: ${name} <${email}>";
        $headers[] = 'From: ECE Club <ece@skule.ca>';

        // Mail it
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
    
    public function sendPasswordResetEmail($appHome, $email, $name, $ticketNum, $password){
        $to = $email; // note the comma
        // Subject
        $subject = 'ECE Dinner Dance - Password Reset';

        // Message
        $message = "
        <html>
        <head>
          <title>ECE Dinner Dance - Password Reset</title>
        </head>
        <body>
            <p>
                Hi ${name}!
            </p>
            <p>
                We have reset your password. Here are your login credentials.<br/>
                Ticket Number: ${ticketNum} <br/>
                Password: ${password}<br/>
                Please follow the link and login to complete the registration process<br>
                <a href=\"${appHome}#/login\">${appHome}#/login</a>
            </p>
            <p>
                Cheers,<br/>
                ECE Club
            </p>
        </body>
        </html>
        ";

        $this->app->logger->addInfo('Sending Email: ' . $message );

        // To send HTML mail, the Content-type header must be set
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';

        // Additional headers
        $headers[] = "To: $name ${email}";
        $headers[] = 'From: ECE Club <ece@skule.ca>';

        // Mail it
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}

?>
