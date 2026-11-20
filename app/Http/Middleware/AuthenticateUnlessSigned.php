<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;

/**
 * Printable documents (invoice / quotation / receipt PDFs) are opened two ways:
 * - inside the app, by a logged-in user — normal API auth applies;
 * - from a link sent to a customer or supplier by email / SMS / WhatsApp —
 *   that link is signed (App\Support\DocumentLink) and opens that one
 *   document without a login.
 * Anything else — e.g. guessing /api/sale_pdf/{id} — is rejected with 401.
 */
class AuthenticateUnlessSigned
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('GET') && $request->hasValidRelativeSignature()) {
            return $next($request);
        }

        return (new Pipeline(app()))
            ->send($request)
            ->through([Authenticate::class.':api', Is_Active::class])
            ->then($next);
    }
}
