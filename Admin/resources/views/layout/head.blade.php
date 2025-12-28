<head>
    <meta charset="utf-8">
    <title>BCP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description">
    <meta content="Coderthemes" name="author">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('template/assets/images/logo.png') }}">


    <link href="{{ asset('template/assets/css/app.min.css') }}" rel="stylesheet" type="text/css">



    <!-- App css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href=" {{ asset('template\assets\css\bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href=" {{ asset('template\assets\css\icons.min.css') }}" rel="stylesheet" type="text/css">
    <link href=" {{ asset('template\assets\css\app.min.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">


    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #venueMap {
            height: 300px;
            width: 100%;
            border-radius: 8px;
            z-index: 1;
        }
    </style>
</head>
