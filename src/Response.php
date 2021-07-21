<?php

namespace ResponseWrapper;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class Response implements \RpContracts\Response
{
    /**
     * @var string|null
     */
    protected ?string $response;

    /**
     * @var Throwable[]
     */
    protected ?array $errorsBag;

    /**
     * ResultWrapper constructor.
     * @param ResponseInterface|null $response
     * @param array|null $errorsBag
     */
    public function __construct(ResponseInterface $response = null, array $errorsBag = null)
    {
        $this->response = ($response ? $response->getBody()->getContents() : null);
        $this->errorsBag = $errorsBag;
    }

    /**
     * @return string|null
     */
    public function getRawContents() : ?string
    {
        return $this->response;
    }

    /**
     * @return array|null
     */
    public function getContents() : ?array
    {
        return $this->response ? @json_decode($this->response, true) : null;
    }

    /**
     * @return array|Throwable[]|null
     */
    public function getErrorsBag() : ?array
    {
        return $this->errorsBag;
    }

    /**
     * @return Throwable|null
     */
    public function getLastException() : ?Throwable
    {
        return ($this->errorsBag ? $this->errorsBag[count($this->errorsBag)-1] : null);
    }

    /**
     * @return string|null
     */
    public function getExceptionMessage() : ?string
    {
        if($exception = $this->getLastException())
        {
            return $exception->getMessage();
        }

        return null;
    }

    /**
     * @return array|null
     */
    public function getExceptionBody() : ?string
    {
        if($exception = $this->getLastException())
        {
            try {
                return $exception->getResponse()->getBody();
            }
            catch(Throwable $exception){}
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isSuccess() : bool
    {
        return (bool)$this->response;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getFromDataByKey(string $key)
    {
        if($data = $this->getContents()){
            return $data[$key] ?? null;
        }

        return null;
    }
}
