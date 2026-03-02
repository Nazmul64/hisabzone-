<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Test</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- MetisMenu -->
    <link href="https://cdn.jsdelivr.net/npm/metismenu/dist/metisMenu.min.css" rel="stylesheet">

    <style>
        body { margin: 0; background: #f5f5f5; }

        .sidebar-wrapper {
            width: 260px;
            height: 100vh;
            background: #fff;
            box-shadow: 2px 0 10px rgba(0,0,0,0.08);
            position: fixed;
            left: 0; top: 0;
            overflow-y: auto;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 20px 16px;
            border-bottom: 1px solid #eee;
        }

        .logo-text {
            font-size: 18px;
            font-weight: 700;
            color: #0d6efd;
            margin: 0;
        }

        .metismenu {
            padding: 10px 0;
            list-style: none;
            margin: 0;
        }

        .metismenu li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: #444;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.2s;
        }

        .metismenu li a:hover,
        .metismenu li.mm-active > a {
            background: #e8f0fe;
            color: #0d6efd;
        }

        .metismenu li ul {
            list-style: none;
            padding: 0;
            background: #f9f9f9;
        }

        .metismenu li ul li a {
            padding-left: 50px;
            font-size: 13px;
        }

        .parent-icon { font-size: 20px; }
        .menu-title { flex: 1; }

        /* MetisMenu arrow */
        .has-arrow::after {
            content: '\ea4e';
            font-family: 'boxicons';
            font-size: 16px;
            margin-left: auto;
            transition: transform 0.3s;
        }

        .mm-active > .has-arrow::after {
            transform: rotate(90deg);
        }
    </style>
</head>
<body>

<div class="sidebar-wrapper">

    <div class="sidebar-header">
        <i class='bx bxs-chevrons-left' style="font-size:28px; color:#e74c3c;"></i>
        <h4 class="logo-text">HisabZone</h4>
    </div>

    <ul class="metismenu" id="menu">

        <li>
            <a href="javascript:void(0);" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-home-alt'></i></div>
                <div class="menu-title">Slider</div>
            </a>
            <ul>
                <li><a href="{{ route('slider.index') }}"><i class='bx bx-radio-circle'></i>Slider List</a></li>

            </ul>
        </li>



        <li>
            <a href="javascript:void(0);" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-category"></i></div>
                <div class="menu-title">Ad Setting</div>
            </a>
            <ul>
                <li><a href="{{ route('adsetting.index') }}"><i class='bx bx-radio-circle'></i>Ad Setting List</a></li>

            </ul>
        </li>

    </ul>

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- MetisMenu JS -->
<script src="https://cdn.jsdelivr.net/npm/metismenu/dist/metisMenu.min.js"></script>

<script>
    $(document).ready(function () {
        $("#menu").metisMenu({
            toggle: true
        });
    });
</script>

</body>
</html>
