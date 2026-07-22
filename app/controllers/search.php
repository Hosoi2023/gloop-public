<?php

function search_index(PDO $pdo): string
{
    $query = trim($_GET['q'] ?? '');
    $movies = [];

    if ($query !== '') {
        $url = 'https://api.themoviedb.org/3/search/movie?api_key=' . TMDB_API_KEY . '&query=' . urlencode($query);
        $response = file_get_contents($url);

        if ($response !== false) {
            $data = json_decode($response, true);
            $movies = array_map(fn($m) => [
                'tmdb_id' => $m['id'],
                'title'   => $m['title'],
                'year'    => substr($m['release_date'] ?? '', 0, 4),
                'poster'  => $m['poster_path']
                    ? 'https://image.tmdb.org/t/p/w200' . $m['poster_path']
                    : null,
            ], $data['results'] ?? []);
        }
    }

    return render('app/views/search.php', [
        'page_title' => $query !== '' ? 'Results for "' . $query . '"' : 'Search Movies',
        'query'      => $query,
        'movies'     => $movies,
    ]);
}