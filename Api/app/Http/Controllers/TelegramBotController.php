<?php

namespace App\Http\Controllers;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller {
    public function updatedActivity() {
        $activity = Telegram::getUpdates();
        dd($activity);
    }
}
