<?php


/**
 * Add some google maps, dirty example
 * [gmap lng="" lat="" id="map" width="300px" height="250px" info=""]
 *
 * @param $attr
 * @return string
 */
function gmap_in_text($attr)
{
    $result = '';
    // nolasām attribūtus
    $default_attr = array("id" => "map", "width" => "100%", "height" => "250px", "lat" => 0, "lng" => 0, "info" => "");
    $available_attr = \Shortcodes\Shortcodes::shortcode_atts($default_attr, $attr);
    $result .= '<script src="//maps.google.com/maps/api/js?sensor=true"></script>'. PHP_EOL;
    $result .= '<script src="//cdnjs.cloudflare.com/ajax/libs/gmaps.js/0.4.19/gmaps.js"></script>'. PHP_EOL;
    $result .= '<style type="text/css"> #' . $available_attr['id'] . ' { width: ' . $available_attr['width'] . '; height: ' . $available_attr['height'] . '; } </style>' . PHP_EOL;
    $result .= '<script> $(function() {' . PHP_EOL;

    $result .= '
         var map = new GMaps({
              div: \'#' . $available_attr['id'] . '\',
              lat: ' . $available_attr['lat'] . ',
              lng: ' . $available_attr['lng'] . '
            });

         map.addMarker({
         lat: ' . $available_attr['lat'] . ',
         lng: ' . $available_attr['lng'] . ',
         infoWindow: {
              content: "' . $available_attr['info'] . '"
            }
        });
        ';

    $result .= '});</script>' . PHP_EOL;

    $result .= '<div id="' . $available_attr['id'] . '" class="inline_gmap">'. PHP_EOL;
    $result .= '</div>'. PHP_EOL;

    return $result;
}
