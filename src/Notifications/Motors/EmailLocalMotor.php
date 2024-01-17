<?php

namespace Architekt\Notifications\Motors;

use Architekt\Library\File;
use Architekt\Notifications\EmailTemplate;
use Architekt\Notifications\Interfaces\EmailMotorInterface;
use Architekt\Notifications\TemplateMotors\EmailSmartyMotor;

class EmailLocalMotor implements EmailMotorInterface
{
    private EmailSmartyMotor $template;
    private EmailTemplate $templateEmail;

    public function __construct()
    {
        $this->template = (new EmailSmartyMotor())->init();
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
        return $this;
    }

    public function template(EmailTemplate $emailTemplate, array $templateVars = []): static
    {
        $this->templateEmail = $emailTemplate;
        $this->template->assign($templateVars);

        return $this;
    }

    public function attachment(File $file): static
    {

        return $this;
    }

    public function send(string $email): bool
    {
        $this->template->assign('RECIPIENT', $email);

        $body = $this->template->fetch(sprintf('string:%s', $this->templateEmail->htmlBodySmarty()));

        $text = $this->template->fetch(sprintf('string:%s', $this->templateEmail->htmlTextSmarty()));

        $dir = PATH_APPLICATION . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR;
        $uniq = uniqid();
        file_put_contents($dir . $uniq . '.html', $body);
        file_put_contents($dir . $uniq . '.txt', $text);

        return true;
    }
}