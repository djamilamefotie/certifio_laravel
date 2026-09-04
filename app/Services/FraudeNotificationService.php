<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FraudeNotificationService
{
    public function __construct(private Messaging $messaging) {}

    public function alerterFraude(User $user, string $referenceId, string $message)
    {
        if (!$user->fcm_token) {
            return; // pas de token, on ne peut rien envoyer
        }

        $notification = Notification::create(
            'Résultat de vérification',
            $message
        );

        // CloudMessage::withTarget() est déprécié depuis la version
        // 7.16 du SDK Kreait/Firebase. On utilise maintenant
        // CloudMessage::new() + withToken() (l'équivalent actuel
        // de l'ancien toToken()).
        $cloudMessage = CloudMessage::new()
            ->withNotification($notification)
            ->withToken($user->fcm_token)
            ->withData(['verification_id' => $referenceId]);

        $this->messaging->send($cloudMessage);
    }
}