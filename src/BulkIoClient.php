<?php

namespace AlphaSoft\BulkIo;

use AlphaSoft\BulkIo\Request\CreateHubEventRequest;
use AlphaSoft\BulkIo\Request\CreateHubResourceRequest;
use Exception;
use SplFileObject;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class BulkIoClient
{
    const API_HUBS_RESOURCES_JSON_LINE_GZ_POST = '/api/hubs/%s/resources/jsonl.gz';
    const API_HUBS_EVENTS_POST = '/api/hubs/%s/events';

    const API_HUBS_RESOURCES_GET = '/api/hubs/%s/resources/%s';
    const API_HUBS_EVENTS_GET = '/api/hubs/%s/events/%s';
    const API_HUBS_EVENT_CONSUME = '/api/events/consume/%s';
    const API_DOWNLOAD_RESOURCES_GET = '/api/resources/%s/download';
    private ?HttpClientInterface $client = null;

    public function __construct(string $baseUri, string $token, string $applicationId)
    {
        if (empty($baseUri) || empty($token) || empty($applicationId)) {
            return;
        }

        $this->client = HttpClient::create([
            'verify_peer' => false, 'verify_host' => false,
            'base_uri' => $baseUri,
            'headers' => [
                'Accept' => 'application/json',
                'X-API-KEY' => $token,
                'x-application-id' => $applicationId,
            ]
        ]);

    }
    public function isActive(): bool
    {
        return $this->client instanceof HttpClientInterface;
    }

    private function getClient(): HttpClientInterface
    {
        if (!$this->isActive()) {
            throw new \LogicException('BulkIo is not active');
        }
        return $this->client;
    }

    /**
     * Creates a new resource in the specified hub.
     *
     * @param string $hubName The name of the hub.
     * @param CreateHubResourceRequest $hubRequest The request object containing the resource details.
     * @return ResponseInterface The response from the server.
     * @throws TransportExceptionInterface
     */
    public function createHubResource(string $hubName, CreateHubResourceRequest $hubRequest): ResponseInterface
    {
        return $this->getClient()->request('POST', sprintf(self::API_HUBS_RESOURCES_JSON_LINE_GZ_POST, $hubName), [
                'body' => file_get_contents($hubRequest->getFilename()),
                'headers' => [
                    'X-Organization-Id' => $hubRequest->getOrganizationId(),
                    'X-Filename' => $hubRequest->getFilename(),
                    'X-Description' => $hubRequest->getDescription(),
                    'X-DataType' => $hubRequest->getDataType(),
                ],
            ]
        );
    }

    /**
     * Creates a new event in the specified hub.
     *
     * @param string $hubName The name of the hub.
     * @param CreateHubEventRequest $hubEventRequest
     * @return ResponseInterface The response from the server.
     * @throws TransportExceptionInterface
     */
    public function createHubEvent(string $hubName, CreateHubEventRequest $hubEventRequest): ResponseInterface
    {
        return $this->getClient()->request('POST', sprintf(self::API_HUBS_EVENTS_POST, $hubName), [
                'json' => [
                    'organizationId' => $hubEventRequest->getOrganizationId(),
                    'name' => $hubEventRequest->getName(),
                    'data' => $hubEventRequest->getData(),
                ],
            ]
        );
    }

    /**
     * Retrieves the resources in the specified hub.
     *
     * @param string $hubName The name of the hub.
     * @param string $organizationId The ID of the organization.
     * @return ResponseInterface The response from the server.
     * @throws TransportExceptionInterface
     */
    public function getHubResources(string $hubName, string $organizationId): ResponseInterface
    {
        return $this->getClient()->request('GET', sprintf(self::API_HUBS_RESOURCES_GET, $hubName, $organizationId), []);
    }

    /**
     * Retrieves the events in the specified hub.
     *
     * @param string $hubName The name of the hub.
     * @param string $organizationId The ID of the organization.
     * @return ResponseInterface The response from the server.
     * @throws TransportExceptionInterface
     */
    public function getHubEvents(string $hubName, string $organizationId): ResponseInterface
    {
        return $this->getClient()->request('GET', sprintf(self::API_HUBS_EVENTS_GET, $hubName, $organizationId), []);
    }

    /**
     * Consume the specified event.
     *
     * @param string $eventId The ID of the event to consume.
     * @return ResponseInterface The response from the server.
     * @throws TransportExceptionInterface
     */
    public function consumeEvent( string $eventId): ResponseInterface
    {
        return $this->getClient()->request('GET', sprintf(self::API_HUBS_EVENT_CONSUME, $eventId), []);
    }

    /**
     * Downloads a resource from BulkIo and returns a SplFileObject representing the downloaded file.
     *
     * @param string $resourceId The ID of the resource to download.
     * @return SplFileObject A SplFileObject representing the downloaded file.
     * @throws Exception|TransportExceptionInterface If the resource cannot be downloaded.
     */
    public function downloadHubResource(string $resourceId): SplFileObject
    {
        $response = $this->getClient()->request('GET', sprintf(self::API_DOWNLOAD_RESOURCES_GET, $resourceId), []);

        if (200 !== $response->getStatusCode()) {
            throw new Exception('Unable to download resource from BulkIo : ' . $response->getContent());
        }

        $contentDisposition = $response->getHeaders(false)['content-disposition'][0];
        $filename = sys_get_temp_dir() . '/' . str_replace('"', '', explode('filename=', $contentDisposition)[1]);
        $fileHandler = fopen($filename, 'w');
        foreach ($this->getClient()->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }
        fclose($fileHandler);
        return new SplFileObject($filename, 'r');
    }
}
