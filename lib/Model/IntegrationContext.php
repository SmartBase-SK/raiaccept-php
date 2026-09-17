<?php

namespace Raiaccept\RaiacceptApiClient\Model;

class IntegrationContext
{
    public string $type;
    /** @var array<string, string> */
    public array $data;

    /**
     * @param array<string, string> $data
     */
    public function __construct(string $type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * @return array{type: string, data: array<string, string>}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
        ];
    }
}
