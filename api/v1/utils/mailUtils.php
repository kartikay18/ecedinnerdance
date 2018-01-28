<?php

class mailUtils {

    public $app;

    public function __construct(\Slim\Container $app) {
        $this->app = $app; 
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
        
        /*
        try {
            $year = date("Y");
            $template_name = 'account-creation';
            $template_content = array(
                array(
                    'name' => $name,
                    'year' => $year,
                    'ticketNum' => $ticketNum,
                    'password' => $password,
                    'url' => $appHome . "#/login"
                )
            );
            $message = array(
                'to' => array(
                    array(
                        'email' => $email,
                        'name' => $name,
                        'type' => 'to'
                    )
                ),
                'important' => false,
                'track_opens' => null,
                'track_clicks' => null,
                'auto_text' => null,
                'auto_html' => null,
                'inline_css' => null,
                'url_strip_qs' => null,
                'preserve_recipients' => null,
                'view_content_link' => null,
                'tracking_domain' => null,
                'signing_domain' => null,
                'return_path_domain' => null,
                'merge' => true,
                'merge_language' => 'mailchimp',
                'tags' => array('account-creation'),
            );
            $async = false;
            $ip_pool = '';
            $send_at = '';
            $result = $this->app->mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
            return $result;
        } catch(Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            $this->app->logger->error('A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage());
            throw $e;
        }
        */
    }

    public function sendPasswordResetRequestEmail($appHome, $email, $resetLink){
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
        $headers[] = "To: <${email}>";
        $headers[] = 'From: ECE Club <ece@skule.ca>';

        // Mail it
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }

    public function sendPasswordResetEmail($appHome, $email, $ticketNum, $password){
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
                Hi there!
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
        $headers[] = "To: ${email}";
        $headers[] = 'From: ECE Club <ece@skule.ca>';

        // Mail it
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }

    public function checkEmailResults($action, $results){
        $ret = true;
        foreach ($results as $result) {
            if ($result['status'] == 'rejected'){
                $ret = false;
            }
            $this->app->logger->addInfo("Email results for $action: ", $result);
        }
        return $ret;
    }
}

?>
