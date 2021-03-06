<?php

namespace Laratube\Http\Controllers;

use Laratube\Channel;
use Laratube\Jobs\Videos\ConvertForStreaming;
use Laratube\Jobs\Videos\CreateVideoThumbnail;

class UploadVideoController extends Controller
{
    public function index(Channel $channel) // 404 will be thrown directly if channel does not exists
    {
        return view('channels.upload', compact('channel'));
    }

    public function store(Channel $channel)
    {
        // 1st step : save in db
        $video = $channel->videos()->create([
            'title' => request()->title,

            // store video in storage and save path to db
            'path' => request()->video->store("channels/{$channel->id}")
        ]);

        // 2nd step : create thumbnail
        $this->dispatch(new CreateVideoThumbnail($video));

        // 3rd step : ready for streaming
        $this->dispatch(new ConvertForStreaming($video));
        return $video;
    }
}
