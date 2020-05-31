<?php

namespace App\Services\v1;

use App\Flight;

class FlightsService
{
    protected $supportedInclude = [
        'arrivalAirport' => 'arrival',
        'departureAirport' => 'departure'
    ];

    public function getFlights($params)
    {
        if (empty($params)) {
            return $this->filterFlights(Flight::all());
        }

        $keys = [];

        if (isset($params['include'])) {
            $includeParams = explode(',', $params['include']);
            $includes = array_intersect($this->supportedInclude, $includeParams);
            $keys = array_keys($includes);
        }

        // Eager loading to optimize the N+1 problem
        return $this->filterFlights(Flight::with($keys)->get(), $keys);

    }

    public function getFlight($flightNumber)
    {
        return $this->filterFlights(Flight::where('flightNumber', $flightNumber)->get());
    }

    protected function filterFlights($flights, $keys = [])
    {
        $data = [];

        foreach ($flights as $flight) {
            $entry = [
                'flightNumber' => $flight->flightNumber,
                'status' => $flight->status,
                'url' => route('flights.show', ['flight' => $flight->flightNumber])
            ];

            if (in_array('arrivalAirport', $keys)) {
                $entry['arrival'] = [
                    'datetime' => $flight->arrivalDatetime,
                    'iataCode' => $flight->arrivalAirport->iataCode,
                    'city' => $flight->arrivalAirport->city,
                    'state' => $flight->arrivalAirport->state
                ];
            }

            if (in_array('departureAirport', $keys)) {
                $entry['departure'] = [
                    'datetime' => $flight->departureDatetime,
                    'iataCode' => $flight->departureAirport->iataCode,
                    'city' => $flight->departureAirport->city,
                    'state' => $flight->departureAirport->state
                ];
            }

            $data[] = $entry;
        }

        return $data;
    }
}