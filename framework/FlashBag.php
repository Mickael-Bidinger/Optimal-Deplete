<?php

namespace MB;


class FlashBag implements SessionHandlerInterface
{
    static private $isInit = false;
    const KEY = 'flashBag';

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

    public function add(string $message)
    {
        $_SESSION[self::KEY][] = $message;
    }

    public function fetchMessage(): ?string
    {
        return \array_shift($_SESSION[self::KEY]);
    }

    public function fetchMessages(): array
    {
        $messages = $_SESSION[self::KEY];
        $_SESSION[self::KEY] = [];

        return $messages;
    }

    public function hasMessages(): bool
    {
        return !empty($_SESSION[self::KEY]);
    }

    public static function isInit(): bool
    {
        return self::$isInit;
    }
}