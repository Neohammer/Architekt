<?php

namespace Architekt\Notifications\Motors;

use Architekt\Library\File;
use Architekt\Notifications\EmailTemplate;
use Architekt\Notifications\Interfaces\EmailMotorInterface;
use Architekt\Notifications\TemplateMotors\EmailSmartyMotor;
use PHPMailer\PHPMailer\PHPMailer;

class EmailPhpMailerMotor implements EmailMotorInterface
{
    private PHPMailer $PHPMailer;
    private EmailSmartyMotor $template;
    private EmailTemplate $templateEmail;

    public function __construct()
    {
        $this->PHPMailer = new PHPMailer();
        $this->template = (new EmailSmartyMotor())->init();
    }

    public function encoding(string $encoding): static
    {
        $this->PHPMailer->CharSet = $encoding;

        return $this;
    }

    public function from(string $email, string $name): static
    {
        $this->PHPMailer->setFrom($email, $name);

        return $this;
    }

    public function replyTo(string $email, string $name): static
    {
        $this->PHPMailer->addReplyTo($email, $name);

        return $this;
    }

    public function subject(string $subject): static
    {
        $this->PHPMailer->Subject = $subject;

        return $this;
    }

    public function template(EmailTemplate $emailTemplate, array $templateVars = []): static
    {
        $this->PHPMailer->isHTML();
        $this->templateEmail = $emailTemplate;
        $this->template->assign($templateVars);

        return $this;
    }

    public function attachment(File $file): static
    {
        $this->PHPMailer->addAttachment($file->filePath(), $file->_get('name'));

        return $this;
    }

    public function send(string $email): bool
    {
        $this->PHPMailer->addAddress($email);

        $this->PHPMailer->Body = $this->template->fetch(sprintf('string:%s', $this->templateEmail->htmlBodySmarty()));

        $this->PHPMailer->AltBody = $this->template->fetch(sprintf('string:%s', $this->templateEmail->htmlTextSmarty()));

        return $this->PHPMailer->send();
    }
}