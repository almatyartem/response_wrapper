<?php

namespace ResponseWrapper;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class Response implements \RpContracts\Response
{
    /**
     * @var ResponseInterface|null
     */
    protected ?ResponseInterface $response = null;

    /**
     * @var string|null
     */
    protected ?string $responseContent;

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
        $this->response = $response;
        $this->responseContent = ($response ? $response->getBody()->getContents() : null);
        $this->errorsBag = $errorsBag;
    }

    /**
     * @return string|null
     */
    public function getRawContents() : ?string
    {
        return $this->responseContent;
    }

    /**
     * @return array|null
     */
    public function getContents() : ?array
    {
        if(!$this->errorsBag and $this->responseContent)
        {
            return @json_decode($this->responseContent, true);
        }

        return null;
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
        return ($this->getStatusCode() == 200);
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

    /**
     * @return int
     */
    public function getStatusCode() : int
    {
        if($this->response)
        {
            return $this->response->getStatusCode();
        }


        if($exception = $this->getLastException())
        {
            try {
                return $exception->getResponse()->getStatusCode();
            }
            catch(Throwable $exception){}
        }

        return 500;
    }
}
