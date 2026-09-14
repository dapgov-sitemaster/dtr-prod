<?php

namespace App\Actions;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Azure
{
    public function get($path = null)
    {
        try {
            $endpoint = config('services.azure.storage.endpoint');
            $sas_token = config('services.azure.storage.read_sas_token');

            $url = $endpoint.$path.$sas_token;
            // $client = new Client();
            // $response = $client->get($url, [
            //     'headers' => [
            //         'x-ms-version' => '2020-08-04',
            //     ]
            // ]);
            // $imageData = $response->getBody()->getContents();
            // $imageBase64 = base64_encode($imageData);

            // return $imageBase64;
            $response = Http::connectTimeout(5)->timeout(15)->get($url);

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

    public function put(?string $path, $file, ?string $filename = null)
    {
        try {
            $endpoint = config('services.azure.storage.endpoint');
            $sas_token = config('services.azure.storage.write_sas_token');
            $url = $endpoint.$path.'/'.$filename.$sas_token;

            $ext = explode('.', $filename);
            $cont_type = ($ext[1] == 'pdf') ? 'application/pdf' : 'image/'.$ext[1];

            $client = new Client;
            $response = $client->request('PUT', $url, [
                'body' => $file,
                'connect_timeout' => 5,
                'timeout' => 30,
                'headers' => [
                    'x-ms-blob-type' => 'BlockBlob', // Set the blob type to 'BlockBlob'
                    'x-ms-blob-content-type' => $cont_type, // Set the content type of the blob
                    'x-ms-blob-name' => $filename,
                ],
            ]);

            return $response->getStatusCode();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function delete(?string $path = null)
    {
        try {
            $endpoint = config('services.azure.storage.endpoint');
            $sas_token = config('services.azure.storage.delete_sas_token');
            $url = $endpoint.$path.$sas_token;

            $client = new Client;
            $response = $client->request('DELETE', $url, [
                'connect_timeout' => 5,
                'timeout' => 15,
            ]);

            return $response->getStatusCode();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
