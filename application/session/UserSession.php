<?php

namespace App\session;

use MB\SessionHandlerInterface;

class UserSession implements SessionHandlerInterface
{
    static private $isInit = false;
    const KEY = 'user';

    public function __construct()
    {
        if (\session_status() === PHP_SESSION_NONE) {
            \session_start();
        }
        if (!\array_key_exists(self::KEY, $_SESSION)) {
            $_SESSION[self::KEY] = [];
        }
        self::$isInit = true;
    }

    public function __toString(): ?string
    {
        if (!self::isAuthenticated()) {
            return null;
        }
        return $_SESSION[self::KEY]['nickName'];
    }

    public function create(int $id, string $nickName)
    {
        $_SESSION[self::KEY] = [
            'id' => $id,
            'nickName' => $nickName,
        ];
        \session_regenerate_id(true);
    }

    public function destroy()
    {
        \session_regenerate_id(true);
        $_SESSION = [];
        \session_destroy();
        self::$isInit = false;
    }

    public function isAuthenticated(): bool
    {
        return !empty($_SESSION[self::KEY]);
    }

    public static function isInit(): bool
    {
        return self::$isInit;
    }

    public function setToken(string $token)
    {
        $_SESSION[self::KEY]['token'] = $token;
    }

    public function fetchToken(): ?string
    {
        $token = $_SESSION[self::KEY]['token'] ?? null;
        $_SESSION[self::KEY]['token'] = null;

        return $token;
    }

}