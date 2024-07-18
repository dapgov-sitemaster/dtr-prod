<?php

namespace App\Actions;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class Azure
{
    public function get($path = null)
    {
        $endpoint = env('AZURE_STORAGE_API_ENDPOINT');
        $sas_token = env('AZURE_STORAGE_SAS_TOKEN');

        $url = $endpoint . $path . $sas_token;

        $client = new Client();

        try {
            $response = $client->get($url, [
                'headers' => [
                    'x-ms-version' => '2020-08-04',
                ]
            ]);
            $imageData = $response->getBody()->getContents();
            $imageBase64 = base64_encode($imageData);

            return $imageBase64;
        }
        catch(\Exception $e) {
            dd($e->getMessage());
        }
        // $response = Http::get($url);

        // if ($response->successful()) {
        //     return $response->body();
        // } else {
        //     $error = $response->status() . ' ' . $response->body();
        //     return $error;
        // }
    }

    public function put(string $path = null, $file, string $filename = null)
    {
        $endpoint = env('AZURE_STORAGE_API_ENDPOINT');
        $sas_token = env('AZURE_STORAGE_PUT_SAS_TOKEN');
        $url = $endpoint . $path . '/' . $filename . $sas_token;

        $ext = explode('.', $filename);
        $cont_type = ($ext[1] == "pdf") ? "application/pdf" : 'image/'.$ext[1];

        $client = new Client();
        $response = $client->request('PUT', $url, [
            'body' => $file,
            'headers' => [
                'x-ms-blob-type' => 'BlockBlob', // Set the blob type to 'BlockBlob'
                'x-ms-blob-content-type' => $cont_type, // Set the content type of the blob
                'x-ms-blob-name' => $filename
            ],
        ]);

        return $response->getStatusCode();
    }

    public function delete(string $path = null)
    {
        $endpoint = env('AZURE_STORAGE_API_ENDPOINT');
        $sas_token = env('AZURE_STORAGE_DEL_SAS_TOKEN');
        $url = $endpoint . $path . $sas_token;

        $client = new Client();
        $response = $client->request('DELETE', $url);

        return $response->getStatusCode();
    }
}
