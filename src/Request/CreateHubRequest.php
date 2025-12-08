<?php

namespace AlphaSoft\BulkIo\Request;

final readonly class CreateHubRequest
{
    public function __construct(
        private ?string  $organizationId,
        private string $name,
        public string $contentType,
        public string $body
    )
    {
    }
    public function getOrganizationId(): ?string
    {
        return $this->organizationId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
