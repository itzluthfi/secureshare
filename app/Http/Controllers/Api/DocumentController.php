<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Project;
use App\Services\FileEncryptionService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    use AuthorizesRequests;
    
    protected $encryption;
    protected $auditLog;

    public function __construct(FileEncryptionService $encryption, AuditLogService $auditLog)
    {
        $this->encryption = $encryption;
        $this->auditLog = $auditLog;
    }

    /**
     * List project documents
     * 
     * @OA\Get(
     *     path="/projects/{projectId}/documents",
     *     tags={"Documents"},
     *     summary="List project documents",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="projectId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Documents list")
     * )
     */
    public function index(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorize('view', $project);

        $documents = Document::where('project_id', $projectId)
            ->with(['uploader', 'versions'])
            ->paginate(15);

        return response()->json($documents);
    }

    /**
     * Upload and encrypt a document
     */
    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorize('view', $project);

        $request->validate([
            'file' => 'required|file|max:51200', // Max 50MB
            'name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        
        // Encrypt and store file using AES-256
        $encryptionData = $this->encryption->encryptAndStore($file, "documents/{$projectId}");

        // Create document record
        $document = Document::create([
            'project_id' => $projectId,
            'name' => $request->name ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $encryptionData['original_name'],
            'file_path' => $encryptionData['encrypted_path'], // Encrypted path
            'file_type' => $encryptionData['mime_type'],
            'file_size' => $encryptionData['size'],
            'current_version' => 1,
            'uploaded_by' => $request->user()->id,
        ]);

        // Create first version
        DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => 1,
            'file_path' => $encryptionData['encrypted_path'],
            'file_size' => $encryptionData['size'],
            'change_notes' => 'Initial version (encrypted with AES-256)',
            'uploaded_by' => $request->user()->id,
        ]);

        $this->auditLog->logDocumentUpload($document);

        return response()->json([
            'message' => 'Document uploaded and encrypted successfully (AES-256)',
            'document' => $document->load('uploader'),
        ], 201);
    }

    /**
     * Display the specified document
     */
    public function show(Request $request, $id)
    {
        $document = Document::with(['uploader', 'versions', 'comments.user'])->findOrFail($id);
        $this->authorize('view', $document);

        return response()->json($document);
    }

    /**
     * Download and decrypt document
     */
    public function download(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        
        $this->authorize('view', $document->project);

        $this->auditLog->logDocumentDownload($document);

        // Decrypt and stream download
        return $this->encryption->downloadDecrypted(
            $document->file_path,
            $document->original_name
        );
    }

    /**
     * Upload new version of document
     */
    public function uploadVersion(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        $this->authorize('update', $document);

        $request->validate([
            'file' => 'required|file|max:51200', // Max 50MB
            'change_notes' => 'nullable|string',
        ]);

        $file = $request->file('file');
        
        // Encrypt and store new version
        $encryptionData = $this->encryption->encryptAndStore($file, "documents/{$document->project_id}");

        // Increment version
        $newVersionNumber = $document->current_version + 1;

        // Create version record
        DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => $newVersionNumber,
            'file_path' => $encryptionData['file_path'],
            'file_size' => $file->getSize(),
            'change_notes' => $request->change_notes,
            'uploaded_by' => $request->user()->id,
        ]);

        // Update document
        $document->update([
            'current_version' => $newVersionNumber,
            'file_size' => $file->getSize(),
        ]);

        $this->auditLog->log(
            'version_upload',
            "New version (v{$newVersionNumber}) uploaded for document: {$document->name}",
            'App\Models\Document',
            $document->id
        );

        return response()->json([
            'message' => 'New version uploaded successfully',
            'document' => $document->load('versions'),
        ]);
    }

    /**
     * Get all versions of a document
     */
    public function versions(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        $this->authorize('view', $document);

        $versions = $document->versions()->with('uploader')->get();

        return response()->json($versions);
    }

    /**
     * Download specific version
     */
    public function downloadVersion(Request $request, $documentId, $versionNumber)
    {
        $document = Document::findOrFail($documentId);
        $this->authorize('download', $document);

        $version = DocumentVersion::where('document_id', $documentId)
            ->where('version_number', $versionNumber)
            ->firstOrFail();

        // Decrypt file (using document's encryption keys)
        $decryptedContent = $this->encryption->decryptFile(
            $version->file_path,
            $document->encryption_key,
            $document->encryption_iv
        );

        if ($decryptedContent === false) {
            return response()->json(['message' => 'File not found or decryption failed'], 404);
        }

        $this->auditLog->log(
            'version_download',
            "Version {$versionNumber} of document '{$document->name}' downloaded",
            'App\Models\Document',
            $document->id
        );

        $filename = pathinfo($document->original_name, PATHINFO_FILENAME) . "_v{$versionNumber}." . pathinfo($document->original_name, PATHINFO_EXTENSION);

        return response($decryptedContent)
            ->header('Content-Type', $document->file_type)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Update document metadata
     */
    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        $this->authorize('update', $document);

        $oldValues = $document->toArray();

        $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);

        $document->update($request->only(['name']));

        $this->auditLog->logUpdate($document, $oldValues, "Document metadata updated: {$document->name}");

        return response()->json([
            'message' => 'Document updated successfully',
            'document' => $document,
        ]);
    }

    /**
     * Delete document and all its versions
     */
    public function destroy(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        $this->authorize('delete', $document);

        // Delete all version files
        foreach ($document->versions as $version) {
            $this->encryption->delete($version->file_path);
        }

        // Delete main file if different
        $this->encryption->delete($document->file_path);

        $documentName = $document->name;
        
        $this->auditLog->logDelete($document, "Document deleted: {$documentName}");
        
        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully',
        ]);
    }
}
