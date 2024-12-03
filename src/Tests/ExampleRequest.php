<?php

namespace AjCastro\ScribeTdd\Tests;

use AjCastro\ScribeTdd\Tests\ParamParsers\BodyParamParser;
use AjCastro\ScribeTdd\Tests\ParamParsers\QueryParamParser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExampleRequest
{
    public $id;
    public $request;
    public $response;
    private $exampleCreator;

    public function __construct(Request $request, $response, ExampleCreator $exampleCreator)
    {
        $this->id = (string) Str::orderedUuid();
        $this->request = $request;
        $this->response = $response;
        $this->exampleCreator = $exampleCreator;
    }

    public function getUrlParams()
    {
        return collect($this->request->route()->parameters())
            ->map(function ($value, $key) {
                $value = is_object($value) && method_exists($value, 'getKey') ? $value->getKey() : $value;
                return [
                    'type' => gettype($value),
                    'description' => '',
                    'example' => $value,
                    'required' => $this->isUrlParamRequired($key),
                ];
            })
            ->all();
    }

    private function isUrlParamRequired($key)
    {
        return Str::contains($this->request->route()->uri, '{' . $key . '}');
    }

    public function getQueryParams()
    {
        return collect()
            ->wrap($this->request->query->all())
            ->map([QueryParamParser::class, 'parse'])
            ->filter() // currently removing null values, for example, nested objects is not yet supported that's why it returns null
            ->all();
    }

    public function getBodyParams()
    {
        return BodyParamParser::parse($this->request->request->all());
    }

    public function getResponse()
    {
        return [
            'status' => ($statusCode = $this->response->getStatusCode()),
            'headers' => $this->response->headers->all(),
            'description' => $statusCode . ', ' . $this->exampleCreator->description,
            'content' => (string) $this->response->getContent(),
        ];
    }
}
