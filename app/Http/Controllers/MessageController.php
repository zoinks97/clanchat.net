<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMessage;
use App\Models\ClanMessage;
use App\Models\ClanSecretKey;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MessageController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Pick the default clan
        $clan = \App\Models\Clan::firstOrFail(); 
        // Or: Clan::where('name', 'MyClanName')->firstOrFail();
    
        if ($clan->status === "INACTIVE") {
            return response()->json(['status' => 'success', 'data' => 'Clan is not setup yet.']);
        }
    
        $requestMessage = json_decode($request->data);
    
        $message = new \App\Models\ClanMessage;
        $message->username = $requestMessage->author;
        $message->content = $requestMessage->content;
        $message->accountType = $requestMessage->accountType;
        $message->systemMessageType = $requestMessage->systemMessageType;
        $message->clanId = $clan->id;
    
        if (isset($requestMessage->clanTitle)) {
            $message->clanTitle = $requestMessage->clanTitle;
        }
    
        $message->timestamp = $requestMessage->timestamp;
    
        \App\Jobs\ProcessMessage::dispatch($message, $clan);
    
        return response()->json(['status' => 'success', 'data' => 'Message has been processed.']);
    }

            $message = new ClanMessage;
            $message->username = $requestMessage->author;
            $message->content = $requestMessage->content;
            $message->accountType = $requestMessage->accountType;
            $message->systemMessageType = $requestMessage->systemMessageType;
            $message->clanId = $clan->id;

            if (isset($requestMessage->clanTitle)) {
                $message->clanTitle = $requestMessage->clanTitle;
            }

            $message->timestamp = $requestMessage->timestamp;

            ProcessMessage::dispatch($message, $clan);
        }

        return response()->json(array('status' => 'success', 'data' => 'Message has been processed.'));
    }
}
