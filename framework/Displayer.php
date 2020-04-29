<?php


namespace MB;


class Displayer
{
    public function string(string $string, string $pattern = '', string $replace = ''):string
    {
        return \htmlspecialchars(\mb_eregi_replace($pattern, $replace, $string));
    }
}
