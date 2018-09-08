<?php

namespace App\Services\v1;

use App\Flight;

class FlightsService
{
    protected $supportedIncludes = [
        'arrivalAirport' => 'arrival',
        'departureAirport' => 'departure',
    ];

    protected $clausePropertise = [
        'status',
        'flightNumber',
    ];

    public function getFlights($parameters)
    {
        if (empty($parameters)) {
            return $this->filterFlights(Flight::all());
        }

        $withKeys = $this->getKeys($parameters);
        $whereClauses = $this->getWhereClause($parameters);
        $flights = Flight::with($withKeys)->where($whereClauses)->get();

        return $this->filterFlights($flights, $withKeys);
    }

    protected function filterFlights($flights,$keys = [])
    {
        $data = [];
        foreach ($flights as $f) {
            $entry = [
                'flightNumber' => $f->flightNumber,
                'status' => $f->status,
                'href' => route('flights.show',['id'=> $f->flightNumber]),
            ];

            if (in_array('arrivalAirport', $keys)) {
                $entry['arrival'] = [
                    'datetime' => $f->arrivalDateTime,
                    'iataCode' => $f->arrivalAirport->iataCode,
                    'city' => $f->arrivalAirport->iataCode,
                    'state' => $f->arrivalAirport->state,
                ];
            }

            if (in_array('departureAirport', $keys)) {
                $entry['departure'] = [
                    'datetime' => $f->departureDateTime,
                    'iataCode' => $f->departureAirport->iataCode,
                    'city' => $f->departureAirport->iataCode,
                    'state' => $f->departureAirport->state,
                ];
            }

            $data[] = $entry;
        }
        return $data;
    }

    // get keys
    protected function getKeys($parameters){
        $withKeys = [];
        if (isset($parameters['include'])) {
            $includeParms = explode(',', $parameters['include']);
            $includes = array_intersect($this->supportedIncludes, $includeParms); // array intersect match two array value than returned the matched array
            $withKeys = array_keys($includes); // array keys pluck keys from array
        }
        return $withKeys;
    }

    // get clause
    protected function getWhereClause($parameters){
        $clause = [];
        foreach ($this->clausePropertise as $prop) {
            if (in_array($prop, array_keys($parameters))) {
                $clause[$prop] = $parameters[$prop];
            }
        }
        return $clause;
    }
}
