<?php

namespace App\Utils;

class SpintaxParser
{
    /**
     * Parse a string with spintax and return a single, random variation.
     * e.g., "{Hello|Hi|Hey} world"
     */
    public static function parse(string $text): string
    {
        return preg_replace_callback(
            '/\{(((?>[^{}]+)|(?R))*)\}/x',
            [self::class, 'replace'],
            $text
        );
    }

    public static function replace(array $matches): string
    {
        $text = self::parse($matches[1]);
        $parts = explode('|', $text);
        return $parts[array_rand($parts)];
    }
}