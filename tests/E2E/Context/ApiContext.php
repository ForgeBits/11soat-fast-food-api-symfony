<?php

declare(strict_types=1);

namespace App\Tests\E2E\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;

class ApiContext implements Context
{
    private string $baseUrl;
    private array $headers = [];
    private ?string $requestBody = null;

    /** @var array{status:int,headers:array,body:string,json:mixed}|null */
    private ?array $lastResponse = null;

    public function __construct(string $base_url = 'http://localhost:8084')
    {
        $this->baseUrl = rtrim($base_url, '/');
    }

    /**
     * @Given I set request header :name to :value
     */
    public function iSetRequestHeaderTo(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    /**
     * @Given I have the JSON payload:
     */
    public function iHaveTheJsonPayload(PyStringNode $payload): void
    {
        $this->requestBody = (string) $payload;
        // Ensure header
        if (!isset($this->headers['Content-Type'])) {
            $this->headers['Content-Type'] = 'application/json';
        }
    }

    /**
     * @When I send a :method request to :path
     */
    public function iSendRequestTo(string $method, string $path): void
    {
        $url = $this->baseUrl . $path;
        $this->lastResponse = $this->request($method, $url, $this->headers, $this->requestBody);
        // Reset body for next request
        $this->requestBody = null;
    }

    /**
     * @Then the response status code should be :code
     */
    public function theResponseStatusCodeShouldBe(int $code): void
    {
        Assert::assertNotNull($this->lastResponse, 'No response captured');
        Assert::assertSame($code, $this->lastResponse['status']);
    }

    /**
     * @Then the JSON response should have property :prop
     */
    public function theJsonResponseShouldHaveProperty(string $prop): void
    {
        $json = $this->getJson();
        Assert::assertTrue(array_key_exists($prop, $json), sprintf('Property "%s" not found in response', $prop));
    }

    /**
     * @Then the JSON response property :prop should be :expected
     */
    public function theJsonResponsePropertyShouldBe(string $prop, string $expected): void
    {
        $json = $this->getJson();
        Assert::assertArrayHasKey($prop, $json);
        Assert::assertSame($expected, (string) $json[$prop]);
    }

    /**
     * @Then print last response
     */
    public function printLastResponse(): void
    {
        if ($this->lastResponse) {
            fwrite(STDOUT, "\nStatus: {$this->lastResponse['status']}\n{$this->lastResponse['body']}\n");
        }
    }

    /**
     * Simple HTTP request using cURL to avoid extra dependencies.
     * @return array{status:int,headers:array,body:string,json:mixed}
     */
    private function request(string $method, string $url, array $headers, ?string $body): array
    {
        $ch = curl_init();
        $headerLines = [];
        foreach ($headers as $k => $v) {
            $headerLines[] = $k . ': ' . $v;
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('HTTP request failed: ' . $error);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $rawHeaders = substr($response, 0, $headerSize);
        $bodyStr = substr($response, $headerSize);

        $headersOut = $this->parseHeaders($rawHeaders);
        $json = null;
        if (isset($headersOut['content-type']) && str_contains(strtolower((string)$headersOut['content-type']), 'application/json')) {
            $decoded = json_decode($bodyStr, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $json = $decoded;
            }
        } else {
            $decoded = json_decode($bodyStr, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $json = $decoded;
            }
        }

        return [
            'status' => (int) $status,
            'headers' => $headersOut,
            'body' => (string) $bodyStr,
            'json' => $json,
        ];
    }

    /**
     * @return array<string,string>
     */
    private function parseHeaders(string $raw): array
    {
        $headers = [];
        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if (strpos($line, ':') !== false) {
                [$name, $value] = array_map('trim', explode(':', $line, 2));
                $headers[strtolower($name)] = $value;
            }
        }
        return $headers;
    }

    /**
     * @return array<string,mixed>
     */
    private function getJson(): array
    {
        Assert::assertNotNull($this->lastResponse, 'No response captured');
        Assert::assertIsArray($this->lastResponse['json'], 'Response is not a valid JSON');
        return $this->lastResponse['json'];
    }
}
