<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    private const FILE_NOT_FOUND = 'File not found';
    private const UPLOAD_DIRECTORY = 'uploads/';
    private const STORAGE_DIRECTORY = 'storage/';

    public function upload(Request $request)
    {
        $ownerId = $request->query('ownerId');
        $tenantId = $request->query('tenantId');

        if (!$tenantId || !$ownerId) {
            return response()->json(
                [
                    'message' => 'Tenant id and owner id are required',
                    'status' => 400
                ],
                400
            );
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid file upload',
                'errors' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        $file = $request->file('file');
        $id = Str::uuid();
        $filename = $id . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $filename, 'public');
        $url = asset(self::STORAGE_DIRECTORY . $path);

        $fileCreated = File::create([
            'id' => $id,
            'fileName' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'size' => round($file->getSize() / 1024, 2),
            'url' => $url,
            'ownerId' => $ownerId,
            'tenantId' => $tenantId,
            'isActive' => true,
            'created' => now()
        ]);

        $data = [
            'id' => $fileCreated['id'],
            'url' => $fileCreated['url']
        ];

        return response()->json([
            'message' => 'File uploaded successfully',
            'data' => $data
        ], 201);
    }

    public function download($id, Request $request)
    {
        $tenantId = $request->query('tenantId');

        if (!$tenantId) {
            return response()->json(
                [
                    'status' => 400,
                    'message' => 'Tenant id is required'
                ],
                400
            );
        }

        $file = File::where('tenantId', $tenantId)
            ->where('id', $id)
            ->first();

        $storedName = $file->id . '.' . $file->extension;
        $storagePath = 'uploads/' . $storedName;

        if (!Storage::disk('public')->exists($storagePath)) {
            return response()->json([
                'status' => 404,
                'message' => 'File content not found'
            ], 404);
        }

        $downloadName = $file->fileName;

        $absolutePath = Storage::disk('public')->path($storagePath);
        return response()->download($absolutePath, $downloadName);
    }

    public function show($id, Request $request)
    {
        $tenantId = $request->query('tenantId');

        if (!$tenantId) {
            return response()->json(
                [
                    'status' => 400,
                    'message' => 'Tenant id is required'
                ],
                400
            );
        }

        $file = File::where('tenantId', $tenantId)
            ->where('id', $id)
            ->first();

        if (!$file) {
            return response()->json(
                [
                    'status' => 404,
                    'message' => self::FILE_NOT_FOUND
                ],
                404
            );
        }

        return response()->json(
            [
                'status' => 200,
                'message' => 'File found successfully',
                'data' => $file
            ],
            200
        );
    }

    public function delete($id, Request $request)
    {
        $tenantId = $request->query('tenantId');

        if (!$tenantId) {
            return response()->json(
                [
                    'status' => 400,
                    'message' => 'Tenant id is required'
                ],
                400
            );
        }

        $file = File::where('tenantId', $tenantId)
            ->where('id', $id)
            ->first();

        $filename = $id . "." . $file['extension'];

        $path = self::UPLOAD_DIRECTORY . $filename;

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['error' => self::FILE_NOT_FOUND], 404);
        }

        Storage::disk('public')->delete($path);

        File::where('tenantId', $tenantId)
            ->where('id', $id)
            ->delete();

        return response()->json(['message' => 'Deleted file successfully'], 200);
    }
}
