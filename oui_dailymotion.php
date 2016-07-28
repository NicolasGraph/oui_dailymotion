<?php

# --- BEGIN PLUGIN CODE ---
if (class_exists('\Textpattern\Tag\Registry')) {
    // Register Textpattern tags for TXP 4.6+.
    Txp::get('\Textpattern\Tag\Registry')
        ->register('oui_dailymotion')
        ->register('oui_if_dailymotion');
}


function oui_dailymotion($atts, $thing)
{
    global $thisarticle;

    extract(lAtts(array(
        'video'       => '',
        'custom'      => 'dailymotion ID',
        'width'       => '0',
        'height'      => '0',
        'ratio'       => '4:3',
        'api'         => '', // Enables the Player API.
        'autoplay'    => '0', // Starts the playback of the video automatically after the player load.
        'chromeless'  => '0', // If the player should display controls or not during video playback.
        'highlight'   => 'ffcc33', // HTML color of the controls elements' highlights.
        'html'        => '0', // Forces the HTML5 mode.
        'playerid'    => '',  // Id of the player unique to the page to be passed back with all API messages.
        'info'        => '1', // Allows to hide or show the Dailymotion logo.
        'logo'        => '1', // Allows to hide or show the Dailymotion logo.
        'network'     => '', // Hints the player about the host network type.
        'origin'      => '', // The domain of the page hosting the Dailymotion player.
        'quality'     => '', // Suggested quality.
        'related'     => '1', // Shows related videos at the end of the video.
        'start'       => '0', // Specifies when the video should start, value is in seconds.
        'startscreen' => '', // Forces the startscreen to use flash or html mode.)
        'syndication' => '', // Passes your syndication key to the player, the value to set is your key.
        'wmode'       => 'transparent', // Sets the wmode value for the Flash player.
        'label'       => '',
        'labeltag'    => '',
        'wraptag'     => '',
        'class'       => __FUNCTION__
    ), $atts));

    $custom = strtolower($custom);
    if (!$video && isset($thisarticle[$custom])) {
        $video = $thisarticle[$custom];
    }

    /*
     * Check for dailymotion video ID or dailymotion URL to extract ID from
     */
    $match = _oui_dailymotion($video);
    if ($match) {
        $video = $match;
    } elseif (empty($video)) {
        return '';
    }

    $src = '//www.dailymotion.com/embed/video/' . $video;

    /*
     * Attributes.
     */
    $qAtts = array(
        'highlight'   => $highlight,
        'id'          => $playerid,
        'origin'      => $origin,
        'start'       => $start,
        'syndication' => $syndication,
        'autoplay'    => array($autoplay => '0, 1'),
        'chromeless'  => array($chromeless => '0, 1'),
        'html'        => array($html => '0, 1'),
        'info'        => array($info => '0, 1'),
        'logo'        => array($logo => '0, 1'),
        'related'     => array($related => '0, 1'),
        'api'         => array($api => 'postMessage, fragment, location'),
        'network'     => array($network => 'dsl, cellular'),
        'quality'     => array($quality => '240, 380, 480, 720, 1080, 1440, 2160'),
        'startscreen' => array($startscreen => 'flash, html'),
        'wmode'       => array($wmode => 'transparent, opaque')
    );

    $qString = array();

    foreach ($qAtts as $att => $values) {
        if ($values) {
            if (!is_array($values)) {
                $qString[] = $att . '=' . $values;
            } else {
                foreach ($values as $value => $valid) {
                    if ($value) {
                        if (in_list($value, $valid)) {
                            $qString[] = $att . '=' . $value;
                        } else {
                            trigger_error(
                                "unknown attribute value;
                                oui_dailymotion " . $att . " attribute accepts the following values: "
                                . $valid
                            );
                            return;
                        }
                    }
                }
            }
        }
    };

    /*
     * Check if we need to append a query string to the video src.
     */
    if (!empty($qString)) {
        $src .= '?' . implode('&amp;', $qString);
    }

    /*
     * If the width and/or height has not been set we want to calculate new
     * ones using the aspect ratio.
     */
    if (!$width || !$height) {
        $toolbarHeight = 25;

        // Work out the aspect ratio.
        preg_match("/(\d+):(\d+)/", $ratio, $matches);
        if ($matches[0] && $matches[1]!=0 && $matches[2]!=0) {
            $aspect = $matches[1]/$matches[2];
        } else {
            $aspect = 1.333;
        }

        // Calcuate the new width/height.
        if ($width) {
            $height = $width/$aspect + $toolbarHeight;
        } elseif ($height) {
            $width = ($height-$toolbarHeight)*$aspect;
        } else {
            $width = 425;
            $height = 344;
        }
    }

    $out = '<iframe width="'.$width.'" height="'.$height
      .'" src="'.$src.'" frameborder="0" allowfullscreen></iframe>';

    return doLabel($label, $labeltag).(($wraptag) ? doTag($out, $wraptag, $class) : $out);
}


function oui_if_dailymotion($atts, $thing)
{
    global $thisarticle;

    extract(lAtts(array(
        'custom' => null,
        'video' => null
    ), $atts));

    $result = $video ? _oui_dailymotion($video) : _oui_dailymotion($thisarticle[strtolower($custom)]);

    return defined('PREF_PLUGIN') ? parse($thing, $result) : parse(EvalElse($thing, $result));
}


function _oui_dailymotion($video)
{
    if (preg_match('#^(http|https)?://www\.dailymotion\.com(/video)?/([A-Za-z0-9]+)#i', $video, $matches)) {
        return $matches[3];
    } elseif (preg_match('#^(http|https)?://dai\.ly(/video)?/([A-Za-z0-9]+)#i', $video, $matches)) {
        return $matches[3];
    }

    return false;
}
