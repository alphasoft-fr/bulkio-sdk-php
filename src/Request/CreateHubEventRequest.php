<?php

namespace AlphaSoft\BulkIo\Request;

final readonly class CreateHubEventRequest
{
    public function __construct(
        private string  $organizationId,
        private string $name,
        public string  $data
    )
    {
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getData(): string
    {
        return $this->data;
    }
}
