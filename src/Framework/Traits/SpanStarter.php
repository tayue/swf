<?php
namespace Framework\Traits;

use OpenTracing\Span;
use const OpenTracing\Formats\TEXT_MAP;
use const OpenTracing\Tags\SPAN_KIND;
use const OpenTracing\Tags\SPAN_KIND_RPC_SERVER;
use Framework\SwServer\Coroutine\CoroutineManager;
use Swoole\Http\Request;
trait SpanStarter
{
    /**
     * Helper method to start a span while setting context.
     */
    protected function startSpan(
        string $name,
        array $option = [],
        string $kind = SPAN_KIND_RPC_SERVER
    ): Span {
        $root = CoroutineManager::getInstance()->get('tracer.root');
        if (! $root instanceof Span) {
            $request=CoroutineManager::getInstance()->get('tracer.request');
            if (! $request instanceof Request) {
                throw new \RuntimeException('ServerRequest object missing.');
            }
            $carrier = array_map(function ($header) {
                return $header[0];
            }, $request->header);
            // Extracts the context from the HTTP headers.
            $spanContext = $this->tracer->extract(TEXT_MAP, $carrier);
            if ($spanContext) {
                $option['child_of'] = $spanContext;
            }
            $root = $this->tracer->startSpan($name, $option);
            $root->setTag(SPAN_KIND, $kind);
            CoroutineManager::getInstance()->set('tracer.root', $root);
            return $root;
        }
        $option['child_of'] = $root->getContext();
        $child = $this->tracer->startSpan($name, $option);
        $child->setTag(SPAN_KIND, $kind);
        return $child;
    }
}
