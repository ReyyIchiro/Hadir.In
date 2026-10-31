<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * Halaman publik yang boleh diindeks. Daftar ini sengaja eksplisit supaya
     * menambah rute publik baru tidak otomatis membocorkan area aplikasi ke
     * mesin pencari.
     */
    private const PUBLIC_ROUTES = [
        'home',
        'public.how',
        'public.who',
        'public.faq',
        'public.privacy',
        'public.terms',
    ];

    public function robots(): Response
    {
        $disallow = ['/app', '/platform', '/undangan', '/atur-ulang-kata-sandi',
            '/masuk', '/daftar', '/status', '/selfie'];

        $lines = ['User-agent: *', 'Allow: /', ''];

        foreach ($disallow as $path) {
            $lines[] = 'Disallow: '.$path;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): Response
    {
        $urls = array_map(fn (string $name) => [
            'loc' => route($name),
            'lastmod' => null,
        ], self::PUBLIC_ROUTES);

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
