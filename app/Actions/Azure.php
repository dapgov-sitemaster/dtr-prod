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
        // switch ($path_ex[0]) {
        //     case 'signatures':
        //         $sas = "?sp=r&st=2023-10-26T05:54:54Z&se=2023-11-30T16:00:00Z&spr=https&sv=2022-11-02&sr=c&sig=iD1oXRtUb%2BFwuGjUZEZdeW6WROyxBUnV3HLi%2F%2Bi3nRY%3D";
        //         break;
        //     case 'movs':
        //         $sas = "?sp=r&st=2023-10-26T05:57:27Z&se=2023-11-30T16:00:00Z&spr=https&sv=2022-11-02&sr=c&sig=bUum1RLrDDCbT9JM2FHqbos8seBiwEwZLH9OUBaUcdQ%3D";
        //         break;
        //     case 'avatar':
        //         $sas = "?sp=r&st=2023-10-26T05:58:31Z&se=2023-11-30T16:00:00Z&spr=https&sv=2022-11-02&sr=c&sig=sP92FKWFnmYgFRmjxxYKEW%2FPcszr9bn4HZZfP3uPh%2F0%3D";
        //         break;

        //     default:
        //         $sas = "";
        //         break;
        // }

        $url = $endpoint . $path . $sas_token;
        $response = Http::get($url);

        if ($response->successful()) {
            return $response->body();
        } else {
            $error = $response->status() . ' ' . $response->body();
            return $error;
        }
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
