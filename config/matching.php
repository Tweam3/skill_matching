<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recommender Weights
    |--------------------------------------------------------------------------
    |
    | Weights for the four recommendation dimensions. The values must sum to
    | 1.0 (100%). Adjust these to tune how aggressively the recommender
    | prioritises exact skill overlap versus provider reputation.
    |
    */

    'weights' => [
        'skill_overlap' => 0.40,
        'category_coverage' => 0.25,
        'rating' => 0.20,
        'profile_quality' => 0.15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rating & Experience Scaling
    |--------------------------------------------------------------------------
    |
    | How many completed transactions are needed before ratings and profile
    | quality reach their maximum confidence.
    |
    */

    'max_rating' => 5,
    'max_completed_for_confidence' => 5,
    'max_completed_for_experience' => 10,

    /*
    |--------------------------------------------------------------------------
    | Match Thresholds
    |--------------------------------------------------------------------------
    |
    | Minimum score (0-100) for a provider to be considered and stored as a
    | match. Candidates below this are silently excluded.
    |
    */

    'min_match_score' => 15.0,

    /*
    |--------------------------------------------------------------------------
    | Default Result Limit
    |--------------------------------------------------------------------------
    |
    | How many matches to create / display per request by default.
    |
    */

    'default_limit' => 20,
];
