<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\DocumentLink;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Invoice / receipt print endpoints must not be readable by guessing an id:
 * only a logged-in user or a signed link (sent to the customer) gets through.
 */
class PrintRoutesAuthTest extends TestCase
{
    private const PRINT_ROUTES = [
        'sale_pdf', 'sale_print_html', 'quote_pdf', 'quote_print_html', 'booking_pdf',
        'service_job_pdf', 'service_quote_pdf', 'purchase_pdf', 'purchase_print_html',
        'return_sale_pdf', 'return_purchase_pdf', 'payment_purchase_pdf', 'payment_return_sale_pdf',
        'payment_return_purchase_pdf', 'payment_sale_pdf', 'sales_print_invoice', 'transfer_pdf',
        'adjustment_pdf', 'damage_pdf',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        // Same middleware as the real print routes, with a body that needs no database.
        Route::middleware(['api', 'auth.or_signed'])
            ->get('/api/_print_probe/{id}', fn ($id) => "document $id")
            ->name('print._probe');
        Route::getRoutes()->refreshNameLookups();
    }

    public function test_every_print_route_rejects_an_unauthenticated_request(): void
    {
        foreach (self::PRINT_ROUTES as $path) {
            $this->getJson("/api/$path/1")->assertStatus(401);
        }
        $this->postJson('/api/direct_network_print/1')->assertStatus(401);
    }

    public function test_an_unsigned_or_fake_signature_is_rejected(): void
    {
        $this->getJson('/api/_print_probe/7')->assertStatus(401);
        $this->getJson('/api/_print_probe/7?signature=deadbeef')->assertStatus(401);
    }

    public function test_a_signed_document_link_opens_only_that_document(): void
    {
        $link = DocumentLink::make('print._probe', 7);
        $this->assertStringStartsWith(url('/api/_print_probe/7?signature='), $link);

        $this->get($link)->assertOk()->assertSeeText('document 7');

        // Swapping the id in a valid link must not open another document.
        $this->getJson(str_replace('/_print_probe/7?', '/_print_probe/8?', $link))->assertStatus(401);
    }

    public function test_customer_links_point_at_real_signed_print_routes(): void
    {
        foreach (['sale_pdf', 'purchase_pdf', 'quote_pdf', 'booking_pdf', 'payment_return_sale_pdf', 'payment_return_purchase_pdf'] as $path) {
            $this->assertStringContainsString("/api/$path/5?signature=", DocumentLink::make("print.$path", 5));
        }
    }
}
