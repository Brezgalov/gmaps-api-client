<?php

namespace brezgalov\GmapsApiClient;

/**
 * Class represents a point with coords
 * @package brezgalov\GmapsApiClient
 */
class Point
{
    /**
     * @var float
     */
    public $lat;

    /**
     * @var float
     */
    public $lon;

    /**
     * Create class instance from array
     * @param array $coords - [34.7, 38.45]
     * @return Point
     */
    public static function createFromArray(array $coords)
    {
        list($lat, $lon) = $coords;
        return new Point($lat, $lon);
    }

    /**
     * Point constructor. Exapm
     * @param null|array $lat
     * @param null $lon
     */
    public function __construct($lat = null, $lon = null)
    {
        $this->lat = $lat;
        $this->lon = $lon;
    }
}