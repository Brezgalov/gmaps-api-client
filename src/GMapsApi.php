<?php

namespace brezgalov\GmapsApiClient;

use brezgalov\ApiWrapper\Client;
use brezgalov\ApiWrapper\Response;

class GMapsApi extends Client
{
    /**
     * {@inheritdoc}
     */
    public function getBasePath()
    {
        return 'https://maps.googleapis.com/maps/api';
    }

    /**
     * Adds error messages if response has some
     * @param Response $resp
     */
    protected function validateResponse(Response $resp)
    {
        if (array_key_exists('error_message', $resp->data) && !empty($resp->data['error_message'])) {
            $resp->addError($resp->data['error_message']);
        }
        if (@$resp->data['status'] !== 'OK') {
            $resp->addError('Status is not OK: ' . $resp->data['status']);
        }
        if (@$resp->data['status'] === 'ZERO_RESULTS') {
            $resp->data = [];
        }
    }

    /**
     * Try to get coords from google api
     * @param $address
     * @return \brezgalov\ApiWrapper\Response
     * @throws \Exception
     */
    public function getPointByAddress($address)
    {
        $result = $this
            ->prepareRequest('/geocode/json')
            ->setQueryParams([
                'key'       => $this->token,
                'address'   => $address,
            ])
            ->execJson()
        ;

        //handle response
        $this->validateResponse($result);
        if ($result->isSuccessful()) {
            $lat = @$result->data['results'][0]['geometry']['location']['lat'];
            $lon = @$result->data['results'][0]['geometry']['location']['lng'];
            if ($lat === null && $lon === null) {
                $result->addError('Could not fetch coords from response');
            } else {
                $result->data = new Point($lat, $lon);
            }
        }

        return $result;
    }

    /**
     * Find distance between 2 points
     * @param Point $from
     * @param Point $to
     * @return Response
     * @throws \Exception
     */
    public function getDistance(Point $from, Point $to)
    {
        $result = $this->prepareRequest('/distancematrix/json')
            ->setQueryParams([
                'origins'                       =>  $from->lat . ',' . $from->lon,
                'destinations'                  =>  $to->lat . ',' . $to->lon,
                'transit_routing_preference'    =>  'less_walking',
                'key'                           =>  $this->token,
            ])
            ->execJson()
        ;

        //handle response
        $this->validateResponse($result);
        $distance = @$result->data['rows'][0]['elements'][0]['distance']['value'];
        if ($distance !== null) {
            $result->data = floatval($distance);
        } else {
            $result->addError('Could not get distance from response');
        }

        return $result;
    }

    /**
     * Get info about point
     * @param Point $point
     * @return Response
     * @throws \Exception
     */
    public function getPointInfo(Point $point)
    {
        $result = $this->prepareRequest('/geocode/json')
            ->setQueryParams([
                'latlng' => $point->lat . ', ' . $point->lon,
                'key' => $this->token,
            ])
            ->execJson()
        ;

        $this->validateResponse($result);

        return $result;
    }
}