<?php

use Illuminate\Support\Facades\Http;

function calculateDistanceFromCardiff($postcode)
{
    $apiKey = env('GOOGLE_MAPS_API_KEY');
    $origin = '51.70352, -2.90337';
    $destination = $postcode . ', UK';

    $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
        'origins' => $origin,
        'destinations' => $destination,
        'key' => $apiKey
    ]);

    if ($response->successful()) {
        $data = $response->json();
        if (!empty($data['rows'][0]['elements'][0]['distance']['value'])) {
            // Distance in meters
            $distanceInMeters = $data['rows'][0]['elements'][0]['distance']['value'];
            // Convert meters to miles (1 mile = 1609.34 meters)
            $distanceInMiles = $distanceInMeters / 1609.34;
            return $distanceInMiles;
        }
    }

    // Return a default large distance in case of failure
    return 999;


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // $apiKey = env('GOOGLE_MAPS_API_KEY');
    // $origin = 'Cardiff, UK';
    // $destination = $postcode . ', UK';

    // $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
    //     'origins' => $origin,
    //     'destinations' => $destination,
    //     'key' => $apiKey
    // ]);

    // if ($response->successful()) {
    //     $data = $response->json();
    //     if (!empty($data['rows'][0]['elements'][0]['distance']['value'])) {
    //         // Distance in meters
    //         $distanceInMeters = $data['rows'][0]['elements'][0]['distance']['value'];
    //         // Convert meters to miles (1 mile = 1609.34 meters)
    //         $distanceInMiles = $distanceInMeters / 1609.34;
    //         return $distanceInMiles;
    //     }
    // }

    // // Return a default large distance in case of failure
    // return 999;

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // $response = [
    //     'rows' => [
    //         [
    //             'elements' => [
    //                 [
    //                     'distance' => [
    //                         'value' => 25000 // Distance in meters (25 kilometers)
    //                     ]
    //                 ]
    //             ]
    //         ]
    //     ]
    // ];

    // // Process the response to calculate distance
    // $data = $response;
    // if (!empty($data['rows'][0]['elements'][0]['distance']['value'])) {
    //     // Distance in meters
    //     $distanceInMeters = $data['rows'][0]['elements'][0]['distance']['value'];
    //     // Convert meters to miles (1 mile = 1609.34 meters)
    //     $distanceInMiles = $distanceInMeters / 1609.34;
    //     // echo "Distance: " . $distanceInMiles . " miles";
    // } else {
    //     // echo "Error: Distance not found in the response.";
    // }

}
