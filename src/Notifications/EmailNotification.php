<?php

namespace Architekt\Notifications;

use Architekt\Library\File;
use Architekt\Logger;
use Architekt\Notifications\Interfaces\EmailMotorInterface;
use Architekt\Notifications\Motors\EmailLocalMotor;
use Architekt\Notifications\Motors\EmailPhpMailerMotor;
use Architekt\Notifications\Motors\EmailSendGridMotor;

class EmailNotification
{
    private array $recipients;
    /** @var File[] */
    private array $attachments;
    public static string $motorName = 'SendGrid';
    public static bool $active = true;
    public static bool $debug = false;

    public static function motor(): ?EmailMotorInterface
    {
        if (self::$motorName === 'PHPMailer') {
            return new EmailPhpMailerMotor();
        }
        if (self::$motorName === 'SendGrid') {
            return new EmailSendGridMotor();
        }
        if (self::$motorName === 'Local') {
            return new EmailLocalMotor();
        }

        return null;
    }

    public static function build(
        EmailTemplate $emailTemplate,
        string|array $recipient,
        string       $subject,
        mixed        $templateVars = null,
        File|array   $attachments = [],
    ): void
    {
        new self(
            $emailTemplate,
            $recipient,
            $subject,
            $templateVars,
            $attachments
        );
    }

    private function __construct(
        private EmailTemplate $emailTemplate,
        string|array   $recipient,
        private string $subject,
        private array  $templateVars = [],
        File|array     $attachments = [],
    )
    {
        $this->recipients = is_array($recipient) ? $recipient : [$recipient];
        $this->attachments = is_array($attachments) ? $attachments : [$attachments];

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
        try {
            ($mail = self::motor())
                ->encoding("UTF-8")
                ->from(EMAIL_SENDER_EMAIL, EMAIL_SENDER_NAME)
                ->replyTo(EMAIL_SENDER_EMAIL, EMAIL_SENDER_NAME)
                ->subject($this->subject)
                ->template($this->emailTemplate, $this->templateVars);

            foreach ($this->attachments as $file) {
                $mail->attachment($file);
            }


            if (!$mail->send($recipient)) {
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