<?php

namespace Users;

interface UserControllerInterface
{
    public function create(): void;
    public function createClosed(): void;
    public function postCreate(): void;
    public function createConfirm(string $userPrimary, string $key): void;
    public function login(): void;
    public function postLogin(): void;
    public function logout(): void;
    public function passwordChoose(string $userPrimary, string $tokenKey): void;
    public function postPasswordChoose(string $userPrimary, string $tokenKey): void;
    public function passwordRecover(): void;
    public function postPasswordRecover(): void;
    public function passwordRecoverConfirm(string $userPrimary, string $tokenKey): void;
    public function postPasswordRecoverConfirm(string $userPrimary, string $hash): void;
}