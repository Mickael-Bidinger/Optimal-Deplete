<?php


namespace MB;


interface SessionHandlerInterface
{
    public function __construct();

    public static function isInit(): bool;
}