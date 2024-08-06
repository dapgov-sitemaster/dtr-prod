<?php

namespace App\Http\Controllers\Pdf;

use App\Models\Event;
use App\Actions\Azure;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mov;

class ShowMovController extends Controller
{
    public function __invoke(Mov $mov, Azure $azure)
    {
        if ($mov) {
            $filename = $mov->filename;
            $response = $azure->get($filename);
            if ($response == 404) {
                abort(404);
            }

            $name = explode('/', $filename);

            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $name[1] . '"',
            ];
            // dd($request->all());
            return response(base64_decode($response), 200, $headers);
        } else {
            abort(422);
        }
    }
}
