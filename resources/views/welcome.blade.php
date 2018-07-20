<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>{{ config('app.name') }}</title>

	<!-- Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

	<!-- Styles -->
	<style>
		html, body {
			background-color: #fff;
			color: #636b6f;
			font-family: 'Raleway', sans-serif;
			font-weight: 100;
			height: 100vh;
			margin: 0;
		}

		.full-height {
			height: 100vh;
		}

		.flex-center {
			align-items: center;
			display: flex;
			justify-content: center;
			flex-direction: column;
		}

		.position-ref {
			position: relative;
		}

		.content {
			text-align: center;
		}

		.title {
			font-size: 84px;
		}

		.m-b-md {
			margin-bottom: 30px;
		}

		.green {
			color: darkgreen;
			font-weight: bold;
		}
		.red {
			color: darkred;
			font-weight: bold;
		}

		footer a, footer a:hover {
			text-decoration: none;
			color: inherit;
		}
	</style>
</head>
<body>
<div class="flex-center position-ref full-height">
	<div class="content">
		<div class="title m-b-md">
			{{ config('app.name') }}
		</div>

		<div class="m-b-md">
			@if ($status === 0)
				<p class="green">Solr OK</p>
			@else
				<p class="red">Solr NOT OK</p>
			@endif
		</div>
	</div>

	<footer>
		<a href="https://www.ipunkt.biz?utm_source={{ str_slug(config('app.name')) }}" target="_blank">&copy; ipunkt Business Solutions</a>
	</footer>
</div>
</body>
</html>
