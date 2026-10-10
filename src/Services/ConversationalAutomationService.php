<?php

namespace Sejator\WabaSdk\Services;

class ConversationalAutomationService
{
    public function __construct(
        protected Client $client,
    ) {}

    /**
     * "conversational_automation" adalah FIELD pada node phone number, bukan
     * edge terpisah - GET ke "{id}/conversational_automation" langsung
     * memicu error Graph API #100 "nonexisting field".
     */
    public function get(string $phoneNumberId): array
    {
        $response = $this->client->get(
            $phoneNumberId,
            [
                'fields' => 'conversational_automation',
            ],
        );

        return data_get(
            $response,
            'conversational_automation',
            [],
        );
    }

    /**
     * Meta menerima ketiga field ini sebagai query param pada body POST
     * (bukan field terpisah) - commands/prompts tetap dikirim berupa array,
     * client yang menangani encoding-nya.
     */
    public function update(string $phoneNumberId, bool $enableWelcomeMessage, array $prompts = [], array $commands = []): array
    {
        return $this->client->post(
            "{$phoneNumberId}/conversational_automation",
            [
                'enable_welcome_message' => $enableWelcomeMessage,
                'prompts' => $prompts,
                'commands' => $commands,
            ],
        );
    }
}
