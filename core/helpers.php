<?php

function movie_url(int $tmdb_id): string
{
    if ($tmdb_id < 0) {
        return '/movie/view/m' . abs($tmdb_id);
    }
    return '/movie/view/' . $tmdb_id;
}

function movie_id_for_url(int $tmdb_id): string
{
    return $tmdb_id < 0 ? 'm' . abs($tmdb_id) : (string) $tmdb_id;
}