<?php

namespace Brezgalov\GmapsApiClient;

use Brezgalov\ApiWrapper\Client;
use Brezgalov\ApiWrapper\Response;
use Brezgalov\IPoint\IPoint;
use Brezgalov\IPoint\Point;

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
        if (@$resp->data['status'] === 'ZERO_RESULTS') {
            $resp->data = [];
            return;
        }

        $errMsg = @$resp->data['error_message'];
        if (!empty($errMsg)) {
            $resp->addError($errMsg);
        }
        if (@$resp->data['status'] !== 'OK') {
            $resp->addError('Status is not OK: ' . $resp->data['status']);
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
            $data = [
                'lat' => @$result->data['results'][0]['geometry']['location']['lat'],
                'lon' => @$result->data['results'][0]['geometry']['location']['lng'],
            ];
            if ($data['lat'] === null && $data['lon'] === null) {
                $result->addError('Could not fetch coords from response');
            } else {
                $result->data = new Point($data);
            }
        }

        return $result;
    }

    /**
     * Find distance between 2 points
     * @param IPoint $from
     * @param IPoint $to
     * @return Response
     * @throws \Exception
     */
    public function getDistance(IPoint $from, IPoint $to)
    {
        $result = $this->prepareRequest('/distancematrix/json')
            ->setQueryParams([
                'origins'                       =>  $from->getLat() . ',' . $from->getLon(),
                'destinations'                  =>  $to->getLat() . ',' . $to->getLon(),
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
     * @param IPoint $point
     * @return Response
     * @throws \Exception
     */
    public function getPointInfo(IPoint $point)
    {
        $result = $this->prepareRequest('/geocode/json')
            ->setQueryParams([
                'latlng' => $point->getLat() . ', ' . $point->getLon(),
                'key' => $this->token,
            ])
            ->execJson()
        ;

        $this->validateResponse($result);

        return $result;
    }

    /**
     * @param IPoint $from
     * @param IPoint $to
     * @return Response
     * @throws \Exception
     */
    public function getDirections(IPoint $from, IPoint $to)
    {
        $result = $this->prepareRequest('/directions/json')
            ->setQueryParams([
                'origin'        => $from->getLat() . ',' . $from->getLon(),
                'destination'   => $to->getLat() . ',' . $to->getLon(),
                'key'           => $this->token,
            ])
            ->execJson()
        ;

        $this->validateResponse($result);

        return $result;
    }
}