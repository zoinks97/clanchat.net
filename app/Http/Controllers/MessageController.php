<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMessage;
use App\Models\ClanMessage;
use App\Models\ClanSecretKey;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Store a newly created message.
     */
    public function store(Request $request)
    {
        // Split multiple secret keys if present
        $secrets = explode(',', $request->clan_secret);

        foreach ($secrets as $secretKey) {
            // Validate the clan secret exists
            $clanSecret = ClanSecretKey::where('key', $secretKey)->firstOrFail();
            $clan = $clanSecret->clan;

            // Skip processing if clan is inactive
            if ($clan->status === "INACTIVE") {
                return response()->json([
                    'status' => 'success',
                    'data' => 'Clan is not setup yet.'
                ]);
            }

            // Decode the incoming message JSON
            $requestMessage = json_decode($request->data);

            // If the clan secret is for a guest, only process certain messages
            if ($clanSecret->guest) {
                // Only system messages other than NORMAL
                if ($requestMessage->systemMessageType === "NORMAL") {
                    continue;
                }

                $guests = $clan->guests->pluck('name')->toArray();
                $includes = false;

                foreach ($guests as $guest) {
                    $guest = strtolower($guest);
                    $messageContent = strtolower($requestMessage->content);

                    if (preg_match("/\b" . preg_quote($guest, '/') . "\b/i", $messageContent)) {
                        $includes = true;
                        break;
                    }
                }

                if (!$includes) {
                    continue;
                }
            }

            // Create new message object
            $message = new ClanMessage();
            $message->username = $requestMessage->author;
            $message->content = $requestMessage->content;
            $message->accountType = $requestMessage->accountType;
            $message->systemMessageType = $requestMessage->systemMessageType;
            $message->clanId = $clan->id;

            if (isset($requestMessage->clanTitle)) {
                $message->clanTitle = $requestMessage->clanTitle;
            }

            $message->timestamp = $requestMessage->timestamp;

            // Dispatch the job
            ProcessMessage::dispatch($message, $clan);
        }

        return response()->json([
            'status' => 'success',
            'data' => 'Message has been processed.'
        ]);
    }
}
