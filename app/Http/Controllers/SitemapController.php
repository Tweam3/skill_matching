<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = collect(Route::getRoutes())->filter(function ($route) {
            return $route->getName() && !str_starts_with($route->getPrefix(), 'api');
        })->map(function ($route) {
            return [
                'url' => route($route->getName(), [], true),
                'priority' => in_array($route->getName(), ['home', 'login', 'register']) ? '1.0' : '0.8',
            ];
        })->values();

        return response()->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
