<?php

namespace Apiato\Http\Middleware;

use Apiato\Core\Middleware\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

final class ProcessETag extends Middleware
{
    /**
     * This middleware adds the "ETag" HTTP Header to a Response.
     *
     * The ETag, in turn, is a
     * hash of the content that will be returned.
     * The client may request an endpoint and provide an ETag in the
     * "If-None-Match" HTTP Header.
     * If the calculated ETag and submitted ETag matches, the response is manipulated accordingly:
     * - the HTTP Status Code is set to 304 (not modified)
     * - the body content (i.e., the content that was supposed to be delivered) is removed and the client receives an empty body
     *
     * @param \Closure(Request): (Response) $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        if (!config('apiato.requests.use-etag', false)) {
            return $next($request);
        }

        if ($request->hasHeader('if-none-match') && (!$request->isMethod('get') && !$request->isMethod('head'))) {
            throw new PreconditionFailedHttpException('HTTP Header IF-None-Match is only allowed for GET and HEAD Requests.');
        }

        $response = $next($request);

        $content = $response->getContent();
        $etag = md5((string) $content);
        $response->headers->set('Etag', $etag);

        if ($request->hasHeader('if-none-match') && $request->header('if-none-match') === $etag) {
            $response->setStatusCode(304);
        }

        return $response;
    }
}
