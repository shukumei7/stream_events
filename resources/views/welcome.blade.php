<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Stream Events</title>

        <?php $react = !empty($live) ? 'production.min' : 'development' ?>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://unpkg.com/react@18/umd/react.<?php echo $react ?>.js" crossorigin></script>
        <script src="https://unpkg.com/react-dom@18/umd/react-dom.<?php echo $react ?>.js" crossorigin></script>
        <script src="{{ asset('/js/maho.js') }}"></script>
        <script src="{{ asset('/js/fb.js') }}"></script>

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <style type="text/css">
            html, body {
                margin: 0;
                padding: 0;
            }
            #main {
                height: 100vh;
                width: 100vh;
                background: lightgrey;
            }
            .debug-login {
                position: relative;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 200px;
                background: blue;
                color: white;
                font-weight: bold;
                padding: 20px;
                cursor: pointer;
                border-radius: 5px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div id="main">

            </div>
        </div>
        <script type="text/javascript">
            $(() => {
                SEFB.root = ReactDOM.createRoot(document.getElementById('main'));
                SEFB.root.render( React.createElement(Account));
            })
        </script>
    </body>
</html>
