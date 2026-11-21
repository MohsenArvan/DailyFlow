<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\User\ExportUserDataAction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController
{
    public function __invoke(Request $request, ExportUserDataAction $export): StreamedResponse
    {
        $input = $request->validate(['format' => ['sometimes', 'in:csv,json']]);

        return $export->execute($request->user(), $input['format'] ?? 'json');
    }
}
