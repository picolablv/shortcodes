<?php

namespace Shortcodes;

/**
 * Shortcodes API for creating bbcode like tags or what wordpress community calls
 * "shortcodes." The tag and attribute parsing or regular expression code is
 * based on the Textpattern tag parser.
 *
 * Heavy based on WordPress
 *
 * A few examples are below:
 *
 * [shortcode /]
 * [shortcode foo="bar" baz="bing" /]
 * [shortcode foo="bar"/]
 *
 */

class Shortcodes {

    public $shortcode_tags = array();

    /**
     * @param $tag
     * @param $func
     */
    public function add_shortcode($tag, $func)
    {
        if ( is_callable($func) )
        {
            $this->shortcode_tags[$tag] = $func;
        }
    }


    /**
     * @param $tag
     */
    public function remove_shortcode($tag)
    {
        unset($this->shortcode_tags[$tag]);
    }


    /**
     *
     */
    public function remove_all_shortcodes()
    {
        $this->shortcode_tags = array();
    }


    /**
     * @param $tag
     * @return bool
     */
    public function shortcode_exists( $tag )
    {
        return array_key_exists( $tag, $this->shortcode_tags );
    }


    /**
     * @param $content
     * @param $tag
     * @return bool
     */
    public function has_shortcode( $content, $tag )
    {
        if ( false === strpos( $content, '[' ) ) {
            return false;
        }
        if ( $this->shortcode_exists( $tag ) ) {
            preg_match_all( '/' . $this->get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER );
            if ( empty( $matches ) )
                return false;
            foreach ( $matches as $shortcode ) {
                if ( $tag === $shortcode[2] ) {
                    return true;
                } elseif ( ! empty( $shortcode[5] ) && $this->has_shortcode( $shortcode[5], $tag ) ) {
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * @param $content
     * @return mixed
     */
    public function do_shortcode($content)
    {
        if ( false === strpos( $content, '[' ) ) {
            return $content;
        }
        if (empty($this->shortcode_tags) || !is_array($this->shortcode_tags))
            return $content;
        $pattern = $this->get_shortcode_regex();
        return preg_replace_callback( "/$pattern/s",array(&$this, 'do_shortcode_tag'), $content );
    }


    /**
     * @return string
     */
    public function get_shortcode_regex()
    {
        $tagnames = array_keys($this->shortcode_tags);
        $tagregexp = join( '|', array_map('preg_quote', $tagnames) );
        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        return
            '\\[' // Opening bracket
            . '(\\[?)' // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)" // 2: Shortcode name
            . '(?![\\w-])' // Not followed by word character or hyphen
            . '(' // 3: Unroll the loop: Inside the opening shortcode tag
            . '[^\\]\\/]*' // Not a closing bracket or forward slash
            . '(?:'
            . '\\/(?!\\])' // A forward slash not followed by a closing bracket
            . '[^\\]\\/]*' // Not a closing bracket or forward slash
            . ')*?'
            . ')'
            . '(?:'
            . '(\\/)' // 4: Self closing tag ...
            . '\\]' // ... and closing bracket
            . '|'
            . '\\]' // Closing bracket
            . '(?:'
            . '(' // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            . '[^\\[]*+' // Not an opening bracket
            . '(?:'
            . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            . '[^\\[]*+' // Not an opening bracket
            . ')*+'
            . ')'
            . '\\[\\/\\2\\]' // Closing shortcode tag
            . ')?'
            . ')'
            . '(\\]?)'; // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }


    /**
     * @param $m
     * @return string
     */
    public function do_shortcode_tag( $m )
    {
        // allow [[foo]] syntax for escaping a tag
        if ( $m[1] == '[' && $m[6] == ']' ) {
            return substr($m[0], 1, -1);
        }
        $tag = $m[2];
        $attr = $this->shortcode_parse_atts( $m[3] );
        if ( isset( $m[5] ) ) {
            // enclosing tag - extra parameter
            return $m[1] . call_user_func( $this->shortcode_tags[$tag], $attr, $m[5], $tag ) . $m[6];
        } else {
            // self-closing tag
            return $m[1] . call_user_func( $this->shortcode_tags[$tag], $attr, null, $tag ) . $m[6];
        }
    }


    /**
     * @param $text
     * @return array|string
     */
    public function shortcode_parse_atts($text)
    {
        $atts = array();
        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
            foreach ($match as $m) {
                if (!empty($m[1]))
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3]))
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                elseif (!empty($m[5]))
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) and strlen($m[7]))
                    $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]))
                    $atts[] = stripcslashes($m[8]);
            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
    }


    /**
     * @param $pairs
     * @param $atts
     * @return array
     */
    public static function shortcode_atts( $pairs, $atts) {
        $atts = (array)$atts;
        $out = array();
        foreach($pairs as $name => $default) {
            if ( array_key_exists($name, $atts) )
                $out[$name] = $atts[$name];
            else
                $out[$name] = $default;
        }

        return $out;
    }


    /**
     * @param $content
     * @return mixed
     */
    public function strip_shortcodes( $content )
    {
        if ( false === strpos( $content, '[' ) ) {
            return $content;
        }
        if (empty($this->shortcode_tags) || !is_array($this->shortcode_tags))
            return $content;
        $pattern = $this->get_shortcode_regex();
        return preg_replace_callback( "/$pattern/s", array(&$this, 'strip_shortcode_tag'), $content );
    }


    /**
     * @param $m
     * @return string
     */
    public function strip_shortcode_tag( $m ) {
        // allow [[foo]] syntax for escaping a tag
        if ( $m[1] == '[' && $m[6] == ']' ) {
            return substr($m[0], 1, -1);
        }
        return $m[1] . $m[6];
    }


    /**
     * Parsing...
     *
     * @param $content
     * @return mixed
     */
    public function parse_shortcodes($content)
    {
        $content = $this->do_shortcode($content);
        return $content;
    }
}
