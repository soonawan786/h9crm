<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class UrlShortenerController extends Controller
{
    public static function shorten($long_url)
    {
        $client = new Client();

        // Set the API endpoint and access token for Bitly
        $api_endpoint = 'https://api-ssl.bitly.com/v4/shorten';
        $access_token = '78ad7fc7717945ef9ab33334f83242f7bc06eb81';

        // Get the long URL from the request
        //$long_url = $request->input('url');

        // Send a POST request to the Bitly API to shorten the URL
        $response = $client->post($api_endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'long_url' => $long_url,
            ],
        ]);

        // Get the shortened URL from the response
        $data = json_decode($response->getBody()->getContents(), true);
        $short_url = $data['link'];

        // Return the shortened URL
        return $short_url;
        // return response()->json([
        //     'short_url' => $short_url,
        // ]);
    }
}
