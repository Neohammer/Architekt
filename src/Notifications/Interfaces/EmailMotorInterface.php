<?php

namespace Architekt\Notifications\Interfaces;

use Architekt\Library\File;
use Architekt\Notifications\EmailTemplate;

interface EmailMotorInterface
{

    public function encoding(string $encoding): static;

    public function from(string $email, string $name): static;

    public function replyTo(string $email, string $name): static;

    public function subject(string $subject): static;

    public function template(EmailTemplate $emailTemplate, array $templateVars = []): static;

    public function send(string $email): bool;

    public function attachment(File $file): static;
}