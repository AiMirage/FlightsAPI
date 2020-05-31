<?php

namespace App\Services\v1;

use App\Flight;

class FlightsService
{
    public function getFlights() {
        return Flight::all();
    }
}