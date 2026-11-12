# Gloop

A film journaling web application with a catalogue, reviews, community tagging and a full admin back office. Built from scratch in PHP, with a hand-written MVC architecture and URL router. No framework.

---

## Why no framework

The point of this project was to understand what a framework actually does before relying on a framework such as Laravel or Symfony. I wanted to learn how a request becomes a response, how routing resolves, why prepared statements matter, what session handling involves.

---

## Features

**Catalogue and films**

- Film data pulled from the TMDB REST API: metadata, posters and credits
- Filterable catalogue with dynamic SQL queries built across multiple joined tables
- Community tagging: users tag films by emotional reaction, target audience and content warnings, with aggregate vote counts displayed per film

**Users and access**

- Registration and login with password hashing (password_hash / password_verify)
- Role-based access control (user / admin), enforced server side
- Session management

**Admin dashboard**

- User moderation: ban, mute, promote
- Management of categories and film entries

**Interface**

- Responsive, mobile-first, with a hamburger menu
- Light and dark mode using CSS custom properties, persisted in localStorage

---

## Stack

| Layer | Technology |
|---|---|
| Language | PHP |
| Database | MySQL |
| Front end | HTML5, CSS3, vanilla JavaScript |
| Server | Apache (LAMP) |
| External API | TMDB REST API |

---

## Architecture

### Request lifecycle

1. Apache rewrites any request that isn't a real file to `index.php`, the front controller (`.htaccess`, `mod_rewrite`)
2. The front controller loads the router
3. The router sanitises the URL, then splits it into segments: the first is the controller, the second the action, anything after that is passed as parameters
4. The matching controller is instantiated and the action called
5. The controller asks the model for data; the model queries MySQL using prepared statements
6. The controller passes the result to a view, which renders the HTML returned in the response
7. If no controller matches, the request falls through to a 404

### Database

Ten tables with foreign key constraints. Cascade rules are applied deliberately rather than by default: `ON DELETE CASCADE` where child data is meaningless without its parent (comments on a deleted film), and left restrictive elsewhere. Users are never hard-deleted, admins ban or mute instead, so their content stays attached to a row that still exists.

### Access control

Two helpers, `is_logged()` and `is_admin()`, check the session. Every protected route is checked server side **before any content is generated**, so typing an admin URL directly redirects rather than rendering. Hiding a link is not access control.

---

## Setup

```bash
git clone https://github.com/Hosoi2023/gloop-public.git
```

1. Copy `credentials.example.php` to `credentials.php` and fill in your own database credentials and TMDB API key
2. Create the database and import the schema
3. Point an Apache virtual host at the project root, with `mod_rewrite` enabled
4. Make sure `.htaccess` overrides are allowed (`AllowOverride All`)

`credentials.php` is gitignored and is never committed.

---

## What I would do differently

Writing this down because the limitations are as interesting as the features.

- **The user role is read from the session.** If an admin demotes or bans someone mid-session, it only takes effect at their next login. Re-checking the role against the database on sensitive actions would be the fix.
- **No session ID regeneration after login**, which leaves the door open to session fixation. A one-line fix I didn't know about at the time.
- **The login path returns early when an email isn't found**, so response timing differs from a wrong password and leaks whether an account exists.
- **Tag vote counts are aggregated on read.** Fine at this scale, but it would want caching or a counter column before it grew.

---

## Competitive analysis

Before building, I looked at existing film journaling and review platforms to work out what was worth building and what wasn't.

### IMDb

**Weaknesses**
- Unclear homepage content: films are shown without explanation, which feels random enough that I questioned whether it was advertising
- Lacks human-driven content
- Poor content prioritisation: "Top 10 on IMDb" is buried too low, so important content isn't surfaced early

**Strengths**
- Strong visual design: a black background highlights posters, yellow accents emphasise key information, good contrast and readability

### Rotten Tomatoes

**Weaknesses**
- Inefficient use of space: doesn't use the full screen width and feels constrained
- Distracting homepage elements: a slideshow of gossip and irrelevant content reduces focus on the core purpose
- Confusing rating system: Tomatometer versus Popcornmeter, with no clear explanation of the difference

**Strengths**
- Gave me the idea of a page for events and awards (the Oscars, the Festival de Cannes), though I would keep it factual and list-like rather than adding fluff

### Letterboxd

**Weaknesses**
- Narrow layout
- The five-star rating lacks granularity and doesn't distinguish between close ratings. I preferred a score out of 100

### Criticker

**Strengths**
- Clear, consistent, modern design that makes full use of the screen
- Strong value proposition: the homepage explains what the platform is for and how the user-driven recommendations work
- Good onboarding clarity: shows examples of recommendations and collections using familiar films, which builds trust
- Trending section
- Efficient logged-in experience: a grid of films with quick actions to rate or add to a list, with no forced elements
- Gave me the idea of showing which streaming platform a film is available on

### TasteDive

**Weaknesses**
- Very poor visual design
- Lacks clarity and feels inauthentic
- No explanation for why certain films are shown; homepage content lacks logic

### MovieLens

**Weaknesses**
- No trust or credibility; the page feels empty and unfinished
- Poor design quality: narrow page, unattractive colours
- Lack of polish, including missing capital letters, so it doesn't feel professional

### What I took from this

- Clarity of purpose is critical
- Content should be human-centred
- Design builds trust
- Ratings must be simple and precise
- Remove noise, focus on action

### Product direction

A clean, modern, human-driven film platform that prioritises clarity, trust and instant interaction. Users log, rate and discover films without noise, confusion or artificial recommendations.

**Key features**
- Watch journal: log, rate, comment, recommend or avoid
- Where to stream
- Trending content
- Grid-based, action-first homepage

---

## Note

This repository is published for portfolio purposes. No licence is granted for reuse.
