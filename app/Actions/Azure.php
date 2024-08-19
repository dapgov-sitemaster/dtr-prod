<?php

namespace App\Actions;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class Azure
{
    public function get($path = null)
    {
        try {
            $endpoint = env('AZURE_STORAGE_API_ENDPOINT');
            $sas_token = env('AZURE_STORAGE_SAS_TOKEN');

            $url = $endpoint . $path . $sas_token;
            // $client = new Client();
            // $response = $client->get($url, [
            //     'headers' => [
            //         'x-ms-version' => '2020-08-04',
            //     ]
            // ]);
            // $imageData = $response->getBody()->getContents();
            // $imageBase64 = base64_encode($imageData);

            // return $imageBase64;
            $response = Http::get($url);

            if ($response->successful()) {
                $imageBase64 = base64_encode($response->body());
                return $imageBase64;
            } else {
                return $response->status();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function put(string $path = null, $file, string $filename = null)
    {
        try {
            $endpoint = env('AZURE_STORAGE_API_ENDPOINT');
            $sas_token = env('AZURE_STORAGE_PUT_SAS_TOKEN');
            $url = $endpoint . $path . '/' . $filename . $sas_token;

            $ext = explode('.', $filename);
            $cont_type = ($ext[1] == "pdf") ? "application/pdf" : 'image/' . $ext[1];

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
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function delete(string $path = null)
    {
        try {
            $endpoint = env('AZURE_STORAGE_API_ENDPOINT');
            $sas_token = env('AZURE_STORAGE_DEL_SAS_TOKEN');
            $url = $endpoint . $path . $sas_token;

            $client = new Client();
            $response = $client->request('DELETE', $url);

            return $response->getStatusCode();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
