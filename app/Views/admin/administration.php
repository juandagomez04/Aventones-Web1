<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AVENTONES - ADMINISTRATION</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/base.css">
    <link rel="stylesheet" href="../../../public/assets/css/admin.css">
</head>

<body>

    <!-- Header -->
    <header class="header">
        <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
        <h1 class="title">AVENTONES</h1>
    </header>

    <!-- Nav -->
    <div class="menu-container">
        <div class="menu">
            <nav class="left-menu">
                <a href="../rides/searchrides.php">Home</a>
                <a href="../myrides/myrides.php">Rides</a>
                <a href="../bookings/bookings.php">Bookings</a>
                <a href="../admin/admin.php" class="active">Admin</a>
            </nav>

            <div class="center-search">
                <input type="text" placeholder="Search..." class="search-bar" aria-label="Search">
            </div>

            <div class="right-menu">
                <div class="user-btn">
                    <img src="../../../public/assets/img/avatar.png" alt="User" class="user-icon">
                    <div class="dropdown-menu">
                        <a href="../auth/login.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <!-- Main -->
    <main class="admin-wrap">

        <h2 class="subtitle">Administration</h2>

        <!-- Quick actions -->
        <div class="admin-actions">
            <a href="#create-admin" class="btn primary">Create Admin User</a>
            <form class="inline" action="../admin/users_export.php" method="get">
                <button type="submit" class="btn soft">Export CSV</button>
            </form>
        </div>

        <!-- Filters -->
        <form class="filters" action="../admin/users.php" method="get">
            <div class="field">
                <label for="q">Search</label>
                <input id="q" name="q" type="text" placeholder="Name, email, ID…">
            </div>

            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="">All</option>
                    <option value="admin">Admin</option>
                    <option value="driver">Driver</option>
                    <option value="passenger">Passenger</option>
                </select>
            </div>

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="field actions">
                <button type="submit" class="btn primary">Filter</button>
                <a href="../admin/admin.php" class="btn ghost">Clear</a>
            </div>
        </form>

        <!-- Users table -->
        <div class="table-card">
            <div class="table-head">
                <strong>Users</strong>
                <!-- bulk action -->
                <form class="inline" action="../admin/users_bulk.php" method="post">
                    <input type="hidden" name="action" value="deactivate">
                    <button type="submit" class="btn danger soft">Deactivate selected</button>
                </form>
            </div>

            <form action="../admin/users_bulk.php" method="post">
                <input type="hidden" name="action" value="bulk">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox"
                                    onclick="document.querySelectorAll('.check').forEach(c=>c.checked=this.checked)">
                            </th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Ejemplo; reemplaza con loop PHP -->
                        <tr>
                            <td><input type="checkbox" class="check" name="ids[]" value="1"></td>
                            <td>Juanda Gómez</td>
                            <td>juanda@example.com</td>
                            <td><span class="badge role admin">Admin</span></td>
                            <td><span class="badge status active">Active</span></td>
                            <td>2025-10-30</td>
                            <td class="text-right actions">
                                <form action="../admin/users_status.php" method="post" class="inline">
                                    <input type="hidden" name="id" value="1">
                                    <input type="hidden" name="status" value="inactive">
                                    <button class="link warn" type="submit">Deactivate</button>
                                </form>
                                <a class="link" href="../admin/user_edit.php?id=1">Edit</a>
                                <form action="../admin/users_reset_password.php" method="post" class="inline">
                                    <input type="hidden" name="id" value="1">
                                    <button class="link soft" type="submit">Reset password</button>
                                </form>
                            </td>
                        </tr>

                        <tr>
                            <td><input type="checkbox" class="check" name="ids[]" value="2"></td>
                            <td>Pepe Guardiola</td>
                            <td>Pepe@example.com</td>
                            <td><span class="badge role driver">Driver</span></td>
                            <td><span class="badge status pending">Pending</span></td>
                            <td>2025-10-28</td>
                            <td class="text-right actions">
                                <form action="../admin/users_status.php" method="post" class="inline">
                                    <input type="hidden" name="id" value="2">
                                    <input type="hidden" name="status" value="active">
                                    <button class="link ok" type="submit">Activate</button>
                                </form>
                                <form action="../admin/users_role.php" method="post" class="inline">
                                    <input type="hidden" name="id" value="2">
                                    <input type="hidden" name="role" value="admin">
                                    <button class="link" type="submit">Make admin</button>
                                </form>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </form>

            <!-- paginación simple -->
            <div class="pagination">
                <a href="?page=1" class="page current">1</a>
                <a href="?page=2" class="page">2</a>
                <a href="?page=3" class="page">3</a>
                <a href="?page=2" class="page next">Next »</a>
            </div>
        </div>

        <!-- Create Admin panel -->
        <section id="create-admin" class="panel">
            <h3>Create Admin User</h3>
            <form action="../admin/users_create.php" method="post" enctype="multipart/form-data" class="create-form">
                <div class="grid">
                    <div class="field">
                        <label for="first_name">First name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="field">
                        <label for="last_name">Last name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                    <div class="field">
                        <label for="photo">Photo</label>
                        <input type="file" id="photo" name="photo" accept="image/*">
                    </div>
                    <div class="field">
                        <label for="national_id">National ID</label>
                        <input type="text" id="national_id" name="national_id" required>
                    </div>
                    <div class="field">
                        <label for="birth_date">Birth date</label>
                        <input type="date" id="birth_date" name="birth_date" required>
                    </div>
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" minlength="6" required>
                    </div>
                    <div class="field">
                        <label for="password2">Repeat password</label>
                        <input type="password" id="password2" name="password2" minlength="6" required>
                    </div>
                    <input type="hidden" name="role" value="admin">
                    <input type="hidden" name="status" value="active">
                </div>

                <div class="buttons">
                    <a href="#top" class="btn ghost">Cancel</a>
                    <button type="submit" class="btn primary">Save</button>
                </div>
            </form>
            <p class="hint">El administrador creado podrá gestionar estados (Active / Pending / Inactive) y roles de
                todos los usuarios.</p>
        </section>

    </main>

    <!-- Footer -->
    <footer>
        <hr>
        <nav>
            <a href="../rides/searchrides.php">Home</a> |
            <a href="../myrides/myrides.php">Rides</a> |
            <a href="../bookings/bookings.php">Bookings</a> |
            <a href="../profile/configuration.php">Settings</a> |
            <a href="../../index.php">Login</a> |
            <a href="../auth/register.php">Register</a>
        </nav>
        <p>&copy; Aventones.com</p>
    </footer>
</body>

</html>