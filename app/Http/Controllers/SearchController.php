<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\SkillRequest;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    private const VALID_SERVICE_MODES = ['Remote', 'Face-to-Face', 'Hybrid'];

    private const VALID_SEARCH_IN = ['requests', 'providers', 'both'];

    public function index(Request $request)
    {
        $searchIn = 'both';

        $serviceMode = $request->query('service_mode', '');
        $categories = $request->query('categories', []);
        $keyword = $request->query('keyword', '');

        if (! is_array($categories)) {
            $categories = [];
        }

        $request->validate([
            'service_mode' => ['nullable', 'string', 'in:'.implode(',', self::VALID_SERVICE_MODES)],
            'categories.*' => ['string', 'distinct'],
            'subcategories.*' => ['string', 'distinct'],
            'keyword' => ['nullable', 'string', 'max:255'],
        ]);

        $serviceMode = $request->input('service_mode', '');
        $categories = $request->input('categories', []);
        $subcategories = $request->input('subcategories', []);
        $keyword = $request->input('keyword', '');

        if (! is_array($categories)) {
            $categories = [];
        }

        $allCategories = Skill::whereNotNull('Category')
            ->where('Category', '!=', '')
            ->pluck('Category')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $allSubcategories = Skill::whereNotNull('Subcategory')
            ->where('Subcategory', '!=', '')
            ->whereNotNull('Category')
            ->get()
            ->groupBy('Category')
            ->map(function ($group) {
                return $group->pluck('Subcategory')
                    ->unique()
                    ->sort()
                    ->values()
                    ->toArray();
            })
            ->toArray();

        $subcategories = $request->input('subcategories', []);
        if (! is_array($subcategories)) {
            $subcategories = [];
        }

        $results = collect();
        $providers = collect();

        if (in_array($searchIn, ['requests', 'both'])) {
            $query = SkillRequest::where('Status', 'Open')
                ->with(['skill', 'skills', 'user']);

            if ($serviceMode) {
                $query->where('Service_Mode', $serviceMode);
            }

            if (! empty($categories)) {
                $query->where(function ($q) use ($categories) {
                    $q->whereIn('Skill_ID', Skill::whereIn('Category', $categories)->pluck('Skill_ID'))
                        ->orWhereHas('skills', function ($sq) use ($categories) {
                            $sq->whereIn('Category', $categories);
                        });
                });
            }

            if (! empty($subcategories)) {
                $query->where(function ($q) use ($subcategories) {
                    $q->whereIn('Skill_ID', Skill::whereIn('Subcategory', $subcategories)->pluck('Skill_ID'))
                        ->orWhereHas('skills', function ($sq) use ($subcategories) {
                            $sq->whereIn('Subcategory', $subcategories);
                        });
                });
            }

            if ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('Title', 'like', '%'.$keyword.'%')
                        ->orWhere('Description', 'like', '%'.$keyword.'%')
                        ->orWhereHas('skill', function ($sq) use ($keyword) {
                            $sq->where('Skill_Title', 'like', '%'.$keyword.'%');
                        })
                        ->orWhereHas('user', function ($sq) use ($keyword) {
                            $sq->where('Full_Name', 'like', '%'.$keyword.'%')
                                ->orWhere('Email', 'like', '%'.$keyword.'%');
                        });
                });
            }

            $results = $query->orderBy('Created_At', 'desc')->paginate(20)->withQueryString();
        }

        if (in_array($searchIn, ['providers', 'both'])) {
            $providerQuery = User::where('Role', '!=', 'Admin')
                ->where('Account_Status', 'Active');

            if ($keyword) {
                $providerQuery->where(function ($q) use ($keyword) {
                    $q->where('Full_Name', 'like', '%'.$keyword.'%')
                        ->orWhere('Email', 'like', '%'.$keyword.'%')
                        ->orWhereHas('skills', function ($sq) use ($keyword) {
                            $sq->where('Skill_Title', 'like', '%'.$keyword.'%');
                        });
                });
            }

            if (! empty($categories)) {
                $providerQuery->whereHas('skills', function ($sq) use ($categories) {
                    $sq->whereIn('Category', $categories);
                });
            }

            if (! empty($subcategories)) {
                $providerQuery->whereHas('skills', function ($sq) use ($subcategories) {
                    $sq->whereIn('Subcategory', $subcategories);
                });
            }

            $providers = $providerQuery->with('skills')
                ->orderByDesc('Avg_Rating')
                ->paginate(20, ['*'], 'page_providers')
                ->withQueryString();
        }

        return view('search.index', compact(
            'serviceMode',
            'categories',
            'subcategories',
            'keyword',
            'allCategories',
            'allSubcategories',
            'results',
            'providers'
        ));
    }

    public function addCategory(Request $request)
    {
        $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'subcategory' => ['nullable', 'string', 'max:100'],
        ]);

        Skill::create([
            'Skill_Title' => $request->input('subcategory') ?? 'General',
            'Category' => $request->input('category'),
            'Subcategory' => $request->input('subcategory'),
        ]);

        return back();
    }
}
