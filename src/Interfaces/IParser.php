<?php


namespace crystlbrd\DatabaseHandler\Interfaces;


interface IParser
{
    /**
     * Translates the result of a data source into an array
     * @param $data
     * @return array
     */
    public static function parse($data): array;
}