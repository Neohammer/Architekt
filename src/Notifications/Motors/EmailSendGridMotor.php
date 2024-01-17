<?php

namespace Architekt\Notifications\Motors;

use Architekt\Library\File;
use Architekt\Logger;
use Architekt\Notifications\EmailTemplate;
use Architekt\Notifications\Interfaces\EmailMotorInterface;
use SendGrid\Mail\Attachment;
use SendGrid\Mail\Mail;
use SendGrid\SendGrid;

class EmailSendGridMotor implements EmailMotorInterface
{
    private SendGrid $sendGrid;
    private Mail $sendGridEmail;
    private EmailTemplate $templateEmail;
    private array $templateVars;
    private string $subject;

    public function __construct()
    {
        $this->sendGrid = new SendGrid(SENDGRID_API_KEY);
        $this->sendGridEmail = new Mail();
        $this->templateVars = [];
    }

    public function encoding(string $encoding): static
    {
        return $this;
    }

    public function from(string $email, string $name): static
    {
        return $this;
    }

    public function replyTo(string $email, string $name): static
    {
        return $this;
    }

    public function subject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function template(EmailTemplate $emailTemplate, array $templateVars = []): static
    {
        $this->templateEmail = $emailTemplate;
        $this->sendGridEmail->setTemplateId($emailTemplate->templateCode());
        $this->templateVars = $templateVars;

        return $this;
    }

    /**
     * @param string $email
     * @return bool
     */
    public function send(string $email): bool
    {
        $this->templateVars['content']['texts']['subject'] = $this->subject;
        $this->sendGridEmail->addTo($email, substitutions: $this->templateVars);

        try {
            $response = $this->sendGrid->send($this->sendGridEmail);

            if ($response->statusCode() >= 200 || $response->statusCode() < 300) {
                return true;
            }
            Logger::critical(sprintf('Email sendgrid fail %d : error code %s', $this->templateEmail->_primary(), $response->statusCode()));

            return false;
        } catch (\Exception $e) {
            Logger::critical(sprintf('Email sendgrid fail %d : %s', $this->templateEmail->_primary(), $e->getMessage()));

            return false;
        }
    }

    public function attachment(File $file): static
    {
        $this->sendGridEmail->addAttachment(
            new Attachment($file->base64(), $file->_get('mime_type'), $file->_get('name'))
        );

        return $this;
    }
}