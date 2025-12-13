<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        Log::info('DocumentController@index accessed', [
            'ip' => $request->ip(),
            'url' => $request->fullUrl()
        ]);
        
        // Auth will be checked via JavaScript
        return view('documents');
    }

    public function show(Request $request, $id)
    {
        Log::info('DocumentController@show accessed', [
            'document_id' => $id,
            'ip' => $request->ip()
        ]);
        
        // Auth will be checked via JavaScript
        $document = Document::with(['project', 'uploader', 'versions'])->findOrFail($id);
        return view('documents.show', compact('document'));
    }
}
