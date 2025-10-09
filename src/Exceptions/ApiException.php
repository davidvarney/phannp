<?php

namespace Phannp\Exceptions;

use Psr\Http\Message\ResponseInterface;

class ApiException extends PhannpException
{
	private ?int $statusCode = null;
	private ?string $responseBody = null;

	public function __construct(string $message = '', int $code = 0, \Throwable $previous = null, ?int $statusCode = null, ?string $responseBody = null)
	{
		parent::__construct($message, $code, $previous);
		$this->statusCode = $statusCode;
		$this->responseBody = $responseBody;
	}

	public function getStatusCode(): ?int
	{
		return $this->statusCode;
	}

	public function getResponseBody(): ?string
	{
		return $this->responseBody;
	}

	public static function fromResponse(string $message, \Throwable $previous = null, ?ResponseInterface $response = null): self
	{
		$status = null;
		$body = null;
		if ($response !== null) {
			$status = $response->getStatusCode();
			$body = (string) $response->getBody();
		}

		return new self($message, $status ?? 0, $previous, $status, $body);
	}

	/**
	 * Attempt to decode the response body as JSON and return the resulting
	 * associative array, or null if decoding fails.
	 *
	 * @return array|null
	 */
	public function getResponseJson(): ?array
	{
		if ($this->responseBody === null) {
			return null;
		}

		$decoded = json_decode($this->responseBody, true);
		if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
			return null;
		}

		return $decoded;
	}
}

