<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(App\Airport::class, function (Faker $faker) {
    return [
        'iataCode' => Str::random(3),
        'city' => $faker->city,
        'state' => $faker->state
    ];
});

$factory->define(App\Flight::class, function (Faker $faker) {

    // Assume that flights are between 1 to 5 hours long
    $flightHours = $faker->numberBetween(1, 5);

    // Get flight time as a dateInterval object
    $flightTime = new DateInterval('PT' . $flightHours . 'H');

    // Just random datetime
    $arrival = $faker->dateTime;

    // Clone the arrival datetime then subtract flight duration
    $depart = clone $arrival;
    $depart->sub($flightTime);

    return [

        'flightNumber' => Str::random(3) . $faker->randomNumber(5), // 3 random chars + 5 unique digits
        'arrivalAirport_id' => $faker->numberBetween(1, 5), // Assume only 5 airports
        'arrivalDatetime' => $arrival,
        'departureAirport_id' => $faker->numberBetween(1, 5),
        'departureDatetime' => $depart,
        'status' => $faker->boolean ? "on-time" : "delayed"
    ];
});

$factory->define(App\Customer::class, function (Faker $faker) {
    return [
        'firstName' => $faker->firstName,
        'lastName' => $faker->lastName
    ];
});
