<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SelfieController extends Controller
{
    public function show(Request $request, AttendanceEvent $event): StreamedResponse
    {
        abort_unless($request->user()->can('viewSelfie', $event->attendanceDay), 403);

        $path = $event->selfie_path;

        abort_if($path === null, 404);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($path), 404);

        return $disk->response($path, 'selfie.jpg', [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
