<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Task API Backend') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('imgs/favicon.png') }}">
</head>

<body>
    <h2
        style="
  display:flex;
  justify-content:center;
  align-items:center;
  height:100vh;
  margin:0;
  background:#f3f4f6;
  color:#16a34a;
  font-size:28px;
  font-weight:bold;
">
        ✅ Server is working
    </h2>
</body>

</html>
