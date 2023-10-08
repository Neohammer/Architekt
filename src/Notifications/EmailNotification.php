<?php

namespace Architekt\Notifications;

use Architekt\Logger;
use PHPMailer\PHPMailer\PHPMailer;

class EmailNotification
{
    private array $recipients;
    private ?string $subject;
    private ?string $templateFile;
    private ?EmailTemplate $template;
    public static bool $active = true;

    public static function build(
        string|array $recipient,
        string       $subject,
        string       $templateFile,
        mixed        $templateVars = null
    ): void
    {
        new self(
            $recipient,
            $subject,
            $templateFile,
            $templateVars
        );
    }

    private function __construct(
        string|array $recipient,
        string       $subject,
        string       $templateFile,
        mixed        $templateVars = null
    )
    {
        $this->recipients = is_array($recipient) ? $recipient : [$recipient];

        $this->subject = $subject;

        $this->template = (new EmailTemplate())->init();
        if ($templateVars) {
            $this->template->assign($templateVars);
        }
        $this->templateFile = $templateFile;

        $this->_send();
    }

    private function _send(): bool
    {
        if (!self::$active) {
            return true;
        }

        $emailSent = 0;
        foreach ($this->recipients as $recipient) {
            $emailSent += (int)$this->_sendMail($recipient);
        }
        return ($emailSent === sizeof($this->recipients));
    }

    private function _sendMail(string $recipient): bool
    {
        $this->template->assign(['RECIPIENT' => $recipient]);

        try {
            $mail = new PHPMailer(true);

            $mail->addAddress($recipient);
            $mail->setFrom(EMAIL_SENDER_EMAIL, EMAIL_SENDER_NAME);
            $mail->addReplyTo(EMAIL_SENDER_EMAIL, EMAIL_SENDER_NAME);
            //Content
            $mail->isHTML();
            $mail->Subject = utf8_decode($this->subject);
            $mail->Body = $this->template->fetch('_header.html')
                . $this->template->fetch($this->templateFile . '.html')
                . $this->template->fetch('_footer.html');
            $mail->AltBody = $this->template->fetch('_header.txt')
                . $this->template->fetch($this->templateFile . '.txt')
                . $this->template->fetch('_footer.txt');


            if (!$mail->send()) {
                Logger::critical(sprintf(
                    'Email not sent to %s : %s',
                    $recipient,
                    $this->subject
                ));

                return false;
            }
            return true;
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return false;
        }
    }
}