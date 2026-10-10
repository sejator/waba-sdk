<?php

namespace Sejator\WabaSdk\Services;

class ConversationalAutomationService
{
    public function __construct(
        protected Client $client,
    ) {}

    public function get(string $phoneNumberId): array
    {
        return $this->client->get(
            "{$phoneNumberId}/conversational_automation",
            [
                'fields' => 'enable_welcome_message,commands,prompts',
            ],
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
