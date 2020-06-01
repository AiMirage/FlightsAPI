<?php

namespace App\Services\v1;

use App\Flight;
use App\Airport;
use Illuminate\Http\Request;

class FlightsService
{
    protected $supportedIncludes = [
        'arrivalAirport' => 'arrival',
        'departureAirport' => 'departure',
        'status' => 'status'
    ];

    /**
     * Supported query options
     * @var array
     */
    protected $clauseProperties = [
        'status',
        'flightNumber'
    ];

    public function createFlight(Request $request)
    {
        // First check for airport data
        $arrivalAirport = $request->input('arrival.iataCode');
        $departureAirport = $request->input('departure.iataCode');

        $airports = Airport::whereIn('iataCode', [$arrivalAirport, $departureAirport])->get();

        $codes = [];

        foreach ($airports as $airport) {
            $codes[$airport->iataCode] = $airport->id;
        }

        // Create the flight
        $flight = new Flight();
        $flight->flightNumber = $request->input('flightNumber');
        $flight->status = $request->input('status');
        $flight->arrivalAirport_id = $codes[$arrivalAirport];
        $flight->arrivalDatetime = $request->input('arrival.datetime');
        $flight->departureAirport_id = $codes[$departureAirport];
        $flight->departureDatetime = $request->input('departure.datetime');

        $flight->save();

        // Filter the output
        return $this->filterFlights([$flight]);

    }

    public function updateFlight(Request $request, $flightNumber)
    {
        // Check if flight exist
        $flight = Flight::where('flightNumber', $flightNumber)->firstOrFail(); // Will throw model not found exception and return 404 to client

        /* Assume update all or nothing approach */
        // First check for airport data
        $arrivalAirport = $request->input('arrival.iataCode');
        $departureAirport = $request->input('departure.iataCode');

        $airports = Airport::whereIn('iataCode', [$arrivalAirport, $departureAirport])->get();

        $codes = [];

        foreach ($airports as $airport) {
            $codes[$airport->iataCode] = $airport->id;
        }

        // Create the flight
        $flight = new Flight();
        $flight->flightNumber = $request->input('flightNumber');
        $flight->status = $request->input('status');
        $flight->arrivalAirport_id = $codes[$arrivalAirport];
        $flight->arrivalDatetime = $request->input('arrival.datetime');
        $flight->departureAirport_id = $codes[$departureAirport];
        $flight->departureDatetime = $request->input('departure.datetime');

        $flight->save();

        // Filter the output
        return $this->filterFlights([$flight]);
    }

    public function getFlights($params)
    {
        if (empty($params)) {
            return $this->filterFlights(Flight::all());
        }

        $keys = $this->getKeys($params);
        $queryKeys = $this->getQueryKeys($params);

        // Eager loading to optimize the N+1 problem using the with method
        $flights = Flight::with($keys)->where($queryKeys)->get();

        return $this->filterFlights($flights, $keys);

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

    protected function getKeys($params)
    {
        $keys = [];
        if (isset($params['include'])) {
            $includeParams = explode(',', $params['include']);
            $includes = array_intersect($this->supportedIncludes, $includeParams);
            $keys = array_keys($includes);
        }

        return $keys;
    }

    protected function getQueryKeys($params)
    {
        $queryKeys = [];

        foreach ($this->clauseProperties as $prop) {
            if (in_array($prop, array_keys($params))) {
                $queryKeys[$prop] = $params[$prop];
            }
        }

        return $queryKeys;
    }
}