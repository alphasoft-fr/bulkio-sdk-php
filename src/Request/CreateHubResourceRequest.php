<?php

namespace AlphaSoft\BulkIo\Request;
final readonly class CreateHubResourceRequest
{
    public function __construct(
        private string   $organizationId,
        private string   $filename,
        private string   $dataType,
        private ?string $description,
    )
    {
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getDataType(): string
    {
        return $this->dataType;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
}
