<?php


namespace Framework\SwServer\Rpc;

/**
 * Class Code
 *
 *
 */
class Code
{
    /**
     * Parser error
     */
    const PARSER_ERROR = -32700;

    /**
     * Invalid Request
     */
    const INVALID_REQUEST = -32600;

    /**
     * Method not found
     */
    const METHOD_NOT_FOUND = -32601;

    /**
     * Invalid params
     */
    const INVALID_PARAMS = -32602;

    /**
     * Internal error
     */
    const INTERNAL_ERROR = -32603;
}