<?php
$page = 'movies';
$page_title = $page_title ?? 'Search for a movie';
?>

<main class="movie-search-page">

    <section class="movie-search-box">

        <p class="eyebrow">Gloop search</p>

        <h1>Search for a movie</h1>

        <form action="/search" method="GET" class="movie-search-form">

            <input
                type="text"
                name="q"
                class="movie-search-input"
                autofocus
                placeholder="Type a movie title..."
                autocomplete="off"
            >

            <button type="submit" class="movie-search-button">Search</button>

        </form>

    </section>

</main>