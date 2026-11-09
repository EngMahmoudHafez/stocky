@extends('saas.layout')
@section('title', 'Landing Page CMS')
@section('topbar_title', 'CMS — Landing Page')
@section('content')
@include('saas.cms._styles')

<div class="cms-header">
    <div>
        <h1>Landing Page CMS</h1>
        <p>Manage every section of your public homepage from one place.</p>
    </div>
    <a href="{{ url('/') }}" target="_blank" class="cms-btn cms-btn-outline">↗ Open live site</a>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px,1fr)); gap:16px;">
    @php
        $cards = [
            ['slug'=>'landing-pages', 'title'=>'Landing layouts', 'desc'=>'Choose which homepage design is live.'],
            ['slug'=>'hero',          'title'=>'Hero Section',    'desc'=>'Headline, subtitle, and CTA buttons.'],
            ['slug'=>'features',      'title'=>'Features',        'desc'=>'Feature cards and descriptions.'],
            ['slug'=>'pricing',       'title'=>'Pricing Section', 'desc'=>'Pricing section intro and note.'],
            ['slug'=>'stats',         'title'=>'Stats / Trust Bar','desc'=>'Numbers and trust signals.'],
            ['slug'=>'how-it-works',  'title'=>'How It Works',    'desc'=>'Step-by-step process.'],
            ['slug'=>'testimonials',  'title'=>'Testimonials',    'desc'=>'Customer quotes and names.'],
            ['slug'=>'faq',           'title'=>'FAQ',             'desc'=>'Frequently asked questions.'],
            ['slug'=>'cta',           'title'=>'CTA Section',     'desc'=>'Bottom call-to-action banner.'],
            ['slug'=>'footer',        'title'=>'Footer',          'desc'=>'Columns, links, social, copyright.'],
            ['slug'=>'seo',           'title'=>'SEO Settings',    'desc'=>'Meta tags and Open Graph.'],
            ['slug'=>'privacy-policy','title'=>'Privacy Policy',  'desc'=>'Public privacy page content.'],
            ['slug'=>'terms-conditions','title'=>'Terms & Conditions','desc'=>'Public terms page content.'],
            ['slug'=>'translations',  'title'=>'Translations',    'desc'=>'Multi-language text overrides.'],
        ];
    @endphp
    @foreach($cards as $c)
        <a href="{{ url('/super/cms/'.$c['slug']) }}" class="cms-card" style="text-decoration:none;">
            <h2 style="border:0; padding:0; margin:0 0 8px;">{{ $c['title'] }}</h2>
            <p style="margin:0; color:#6b7280; font-size:13px;">{{ $c['desc'] }}</p>
        </a>
    @endforeach
</div>
@endsection
