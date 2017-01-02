<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\InvalidUrlStringException;
use LizardsAndPumpkins\Http\Exception\QueryParameterDoesNotExistException;
use LizardsAndPumpkins\Http\Exception\UnknownProtocolException;

class HttpUrl
{
    /**
     * @var string
     */
    private $schema;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string[]
     */
    private $query;

    /**
     * @param string $schema
     * @param string $host
     * @param string $port
     * @param string $path
     * @param string[] $query
     */
    private function __construct(string $schema, string $host, string $port, string $path, array $query)
    {
        $this->schema = $schema;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->query = $query;
    }

    public static function fromString(string $urlString) : HttpUrl
    {
        $components = parse_url($urlString);

        if (false === $components || !isset($components['host'])) {
            throw new InvalidUrlStringException(sprintf('Host name can not be parsed from "%s" URL.', $urlString));
        }

        $host = idn_to_utf8($components['host']);

        $schema = $components['scheme'] ?? '';
        self::validateSchema($schema);

        $port = (string) ($components['port'] ?? '');
        $path = $components['path'] ?? '';

        $queryString = $components['query'] ?? '';
        parse_str($queryString, $query);

        return new self($schema, $host, $port, $path, $query);
    }

    public function __toString() : string
    {
        $schema = $this->schema . ($this->schema !== '' ? ':' : '');
        $port = '' === $this->port ? '' : ':' . $this->port;

        $queryString = http_build_query($this->query);
        $query = ('' !== $queryString ? '?' : '') . $queryString;

        return $schema . '//' . $this->host . $port . $this->path . $query;
    }

    public function getPathWithoutWebsitePrefix() : string
    {
        $websitePrefix = $this->getAppEntryPointPath();
        return ltrim(preg_replace('/^' . preg_quote($websitePrefix, '/') . '/', '', $this->path), '/');
    }

    private function getAppEntryPointPath() : string
    {
        return preg_replace('#/[^/]*$#', '', $_SERVER['SCRIPT_NAME']);
    }

    public function hasQueryParameter(string $queryParameter) : bool
    {
        return isset($this->query[$queryParameter]);
    }

    public function getQueryParameter(string $parameterName)
    {
        if (! $this->hasQueryParameter($parameterName)) {
            throw new QueryParameterDoesNotExistException(
                sprintf('Query parameter "%s" does not exist', $parameterName)
            );
        }

        return $this->query[$parameterName];
    }

    public function hasQueryParameters() : bool
    {
        return count($this->query) > 0;
    }

    public function getHost() : string
    {
        return $this->host;
    }

    private static function validateSchema(string $schema)
    {
        if (! in_array($schema, ['http', 'https', ''])) {
            throw new UnknownProtocolException(sprintf('Protocol can not be handled "%s"', $schema));
        }
    }
}
