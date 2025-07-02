<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    private const FILE_NOT_FOUND = 'File not found';
    private const UPLOAD_DIRECTORY = 'uploads/';
    private const STORAGE_DIRECTORY = 'storage/';

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:51200',
        ]);

        if ($validator->fails()) {
            Log::error('File validation failed.', ['errors' => $validator->errors()]);
            return response()->json([
                'message' => 'Invalid file upload',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $filename, 'public');

        return response()->json([
            'message' => 'File uploaded successfully',
            'path' => $path,
            'url' => asset(self::STORAGE_DIRECTORY . $path)
        ], 201);
    }

    public function list()
    {
        $files = Storage::disk('public')->files('uploads');
        $data = array_map(function ($path) {
            return [
                'path' => $path,
                'url' => asset(self::STORAGE_DIRECTORY . $path)
            ];
        }, $files);

        return response()->json($data);
    }

    public function download($filename)
    {
        $path = self::UPLOAD_DIRECTORY . $filename;

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['error' => self::FILE_NOT_FOUND], 404);
        }

        $fullPath = Storage::disk('public')->path($path);
        return response()->download($fullPath);
    }

    public function view($filename)
    {
        $path = self::UPLOAD_DIRECTORY . $filename;

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['error' => self::FILE_NOT_FOUND], 404);
        }

        $fullPath = Storage::disk('public')->path($path);
        $mime = mime_content_type($fullPath);
        return response(Storage::disk('public')->get($path))->header('Content-Type', $mime);
    }

    public function delete($filename)
    {
        $path = self::UPLOAD_DIRECTORY . $filename;

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['error' => self::FILE_NOT_FOUND], 404);
        }

        Storage::disk('public')->delete($path);
        return response()->json(['message' => 'Deleted file successfully'], 200);
    }
}
