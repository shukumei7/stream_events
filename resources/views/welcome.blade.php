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
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v17.0&appId=339346735092648&autoLogAppEvents=1" nonce="sIHGKRPP"></script>
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
            #main > .debug-login {
                top: 50%;
                transform: translate(-50%,-50%);
            }
            .debug-login {
                position: relative;
                left: 50%;
                transform: translateX(-50%);
                width: 200px;
                background: blue;
                color: white;
                font-weight: bold;
                padding: 20px;
                cursor: pointer;
                border-radius: 5px;
                text-align: center;
            }
            .top-info {
                display: inline-block;
                position: relative;
                width: 33%;
                height: 100px;
                vertical-align: top;
            }
            .event-info {
                position: relative;
                width:100%;
                height: calc(100vh - 165px);
                overflow: auto;
                background: gray;
            }
            .event-info > div {
                border-top: 1px solid black;
                border-left: 1px solid black;
                border-right: 1px solid black;
                padding: 10px 5px 10px 5px;
                cursor: pointer;
                color: white;
                font-weight: bold;
            }
            .event-info > div > span {
                float: right;
            }
            .event-info > div.event-read {
                background: black;
                color: gray;
                font-weight: normal;
            }
            .event-info > div:hover {
                background: green;
            }
            .event-info > div.event-read:hover {
                background: darkgreen;
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
