<?php

interface iMailer
{
	public function bulkSendAccountCreationEmail($appHome, $emails, $names, $ticketNums, $passwords);
	public function sendMassEmail($appHome, $template_name, $recipients, $global_merge_vars); //this should really be a private function of Mandrill Mailer
	//TODO: add sendReminderEmail
	public function sendTableAssignmentEmail($appHome, $users);
	public function sendAccountCreationEmail($appHome, $email, $name, $ticketNum, $password);
    public function sendPasswordResetRequestEmail($appHome, $email, $name, $resetLink);
    public function sendPasswordResetEmail($appHome, $email, $name, $ticketNum, $password);
}

?>