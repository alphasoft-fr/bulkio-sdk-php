<?php

namespace AlphaSoft\BulkIo\Request;

final readonly class CreateHubResponse
{
    public function __construct(
        private int $status,
        private string $contentType,
        private ?string $body = null
    )
    {
    }
    public function getStatus(): int
    {
        return $this->status;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }
}
