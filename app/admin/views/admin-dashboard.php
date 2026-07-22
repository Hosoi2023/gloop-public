<main class="admin-page">

    <div class="admin-header">
        <div>
            <a href="index.php?page=movies" class="return-link">← Return</a>
            <h1>Admin Dashboard</h1>
            <p>Manage movies, users, reviews and site activity.</p>
        </div>

        <a href="index.php?page=admin-add-movie" class="admin-add-btn">
            + Add Movie
        </a>
    </div>

    <section class="admin-stats">
        <div class="stat-card">
            <span>Total Movies</span>
            <strong>128</strong>
        </div>

        <div class="stat-card">
            <span>Total Users</span>
            <strong>2,481</strong>
        </div>

        <div class="stat-card">
            <span>Reviews</span>
            <strong>9,742</strong>
        </div>

        <div class="stat-card">
            <span>Lists</span>
            <strong>612</strong>
        </div>
    </section>

    <section class="admin-panel">

        <div class="panel-header">
            <h2>Movie Management</h2>
            <input type="text" placeholder="Search movies...">
        </div>

        <table class="admin-table">

            <thead>
                <tr>
                    <th>Movie</th>
                    <th>Genre</th>
                    <th>Rating</th>
                    <th>Ratings</th>
                    <th>Comments</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td>Dune</td>
                    <td>Sci-Fi</td>
                    <td>★ 4.8</td>
                    <td>12,482</td>
                    <td>3,291</td>
                    <td><span class="status live">Live</span></td>

                    <td>
                        <a href="#">Edit</a>
                        <a href="#" class="danger">Delete</a>
                    </td>
                </tr>

                <tr>
                    <td>Interstellar</td>
                    <td>Sci-Fi</td>
                    <td>★ 4.7</td>
                    <td>10,204</td>
                    <td>2,887</td>
                    <td><span class="status live">Live</span></td>

                    <td>
                        <a href="#">Edit</a>
                        <a href="#" class="danger">Delete</a>
                    </td>
                </tr>

            </tbody>

        </table>

    </section>

    <section class="admin-panel users-panel">

        <div class="panel-header">
            <h2>User Account Management</h2>
            <input type="text" placeholder="Search users...">
        </div>

        <table class="admin-table">

            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Lists</th>
                    <th>Ratings</th>
                    <th>Comments</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td>Andreas</td>
                    <td>andreas@example.com</td>
                    <td>12</td>
                    <td>248</td>
                    <td>89</td>

                    <td>
                        <span class="status admin-status">
                            Admin
                        </span>
                    </td>

                    <td>
                        <a href="#">View</a>
                        <a href="#" class="danger">Suspend</a>
                    </td>
                </tr>

                <tr>
                    <td>Emma</td>
                    <td>emma@example.com</td>
                    <td>6</td>
                    <td>142</td>
                    <td>37</td>

                    <td>
                        <span class="status user-status">
                            User
                        </span>
                    </td>

                    <td>
                        <a href="#">View</a>
                        <a href="#" class="danger">Suspend</a>
                    </td>
                </tr>

            </tbody>

        </table>

    </section>

    <section class="admin-panel lists-panel">

        <div class="panel-header">
            <h2>List Management</h2>
            <input type="text" placeholder="Search lists...">
        </div>

        <table class="admin-table">

            <thead>
                <tr>
                    <th>List Name</th>
                    <th>Created By</th>
                    <th>Movies</th>
                    <th>Followers</th>
                    <th>Likes</th>
                    <th>Visibility</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td>Top Sci-Fi Movies</td>
                    <td>Andreas</td>
                    <td>24</td>
                    <td>182</td>
                    <td>421</td>

                    <td>
                        <span class="status public-status">
                            Public
                        </span>
                    </td>

                    <td>
                        <a href="#">View</a>
                        <a href="#">Edit</a>
                        <a href="#" class="danger">Delete</a>
                    </td>
                </tr>

                <tr>
                    <td>Feel Good Films</td>
                    <td>Emma</td>
                    <td>12</td>
                    <td>64</td>
                    <td>118</td>

                    <td>
                        <span class="status private-status">
                            Private
                        </span>
                    </td>

                    <td>
                        <a href="#">View</a>
                        <a href="#">Edit</a>
                        <a href="#" class="danger">Delete</a>
                    </td>
                </tr>

            </tbody>

        </table>

    </section>

</main>