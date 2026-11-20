<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

/**
 * Link to a printable document that is sent to a customer or supplier
 * (email / SMS / WhatsApp). The print routes need a login, so the link carries
 * a signature instead: it opens exactly this document, and changing the id
 * breaks it. Signed relative to the site root so an http→https or proxy host
 * change does not invalidate links already sent.
 */
final class DocumentLink
{
    public static function make(string $routeName, int|string $id): string
    {
        return url(URL::signedRoute($routeName, ['id' => $id], null, false));
    }
}
