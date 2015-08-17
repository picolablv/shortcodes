<?php
// ------------------------------------------------------
// TODO: real phpunit example
// ------------------------------------------------------

require_once __DIR__ . '/../vendor/autoload.php';

use Shortcodes\Shortcodes;

$shortcodes = new Shortcodes();


$shortcodes->add_shortcode('gmap', 'gmap_in_text');


$demo_text = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Animi aspernatur at blanditiis culpa, cupiditate, error fugiat laborum magni officia officiis quia
rem reprehenderit sunt totam voluptates. [gmap lng="24.1216591" lat="56.9507167" width="100%" height="321px"] Debitis deleniti distinctio dolorum.';

$output = $shortcodes->parse_shortcodes($demo_text);

echo $output;
/*
Lorem ipsum dolor sit amet, consectetur adipisicing elit. Animi aspernatur at blanditiis culpa, cupiditate, error fugiat laborum magni officia officiis quia
rem reprehenderit sunt totam voluptates. <script src="//maps.google.com/maps/api/js?sensor=true"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/gmaps.js/0.4.19/gmaps.js"></script>
<style type="text/css"> #map { width: 100%; height: 321px; } </style>
<script> $(function() {

         var map = new GMaps({
              div: '#map',
              lat: 56.9507167,
              lng: 24.1216591
            });

         map.addMarker({
         lat: 56.9507167,
         lng: 24.1216591,
         infoWindow: {
              content: ""
            }
        });
        });</script>
<div id="map" class="inline_gmap">
</div>
 */