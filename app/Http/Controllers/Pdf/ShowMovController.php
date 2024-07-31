<?php

namespace App\Http\Controllers\Pdf;

use App\Models\Event;
use App\Actions\Azure;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShowMovController extends Controller
{
    public function __invoke(Event $event, Azure $azure)
    {
        $mov = $event->mov;
        if ($mov) {
            $response = $azure->get($mov);
            if ($response == 404) {
                abort(404);
            }

            $name = explode('/', $mov);

            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $name[1] . '"',
            ];
            // dd($request->all());
            return response(base64_decode($response), 200, $headers);
        }
    }
}
