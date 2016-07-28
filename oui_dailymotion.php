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
        'api'         => '',
        'autoplay'    => '0',
        'chromeless'  => '0',
        'highlight'   => 'ffcc33',
        'html'        => '0',
        'playerid'    => '',
        'info'        => '1',
        'logo'        => '1',
        'network'     => '',
        'origin'      => '',
        'quality'     => '',
        'related'     => '1',
        'start'       => '0',
        'startscreen' => '',
        'syndication' => '',
        'wmode'       => 'transparent',
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

    $qString = array();

    /*
     * Optional attributes.
     */
    $ifAtts = array(
        'highlight'   => $highlight, // HTML color of the controls elements' highlights.
        'id'          => $playerid, // Id of the player unique to the page to be passed back with all API messages.
        'origin'      => $origin, // The domain of the page hosting the Dailymotion player.
        'start'       => $start, // Specifies when the video should start, value is in seconds.
        'syndication' => $syndication // Passes your syndication key to the player, the value to set is your key.
    );

    foreach ($ifAtts as $att => $value) {
        $value ? $qString[] = $att . '=' . $value : '';
    };

    /*
     * Attributes with boolean values.
     */
    $boolAtts = array(
        'autoplay'   => $autoplay, // Starts the playback of the video automatically after the player load.
        'chromeless' => $chromeless, // Determines if the player should display controls or not during video playback.
        'html'       => $html, // Forces the HTML5 mode.
        'info'       => $info, // Allows to hide or show the Dailymotion logo.
        'logo'       => $logo, // Allows to hide or show the Dailymotion logo.
        'related'    => $related // Shows related videos at the end of the video.
    );

    foreach ($boolAtts as $att => $value) {
        if (in_list($value, '1, 0')) {
            $qString[] = $att . '=' . $value;
        } else {
            trigger_error(
                "unknown attribute value;
                oui_dailymotion " . $att . " attribute accepts the following values:
                1 or 0"
            );
        }
    };

    /*
     * Attributes with restricted values.
     */
    $validAtts = array(
        'api'         => array($api => 'postMessage, fragment, location'), // Enables the Player API.
        'network'     => array($network => 'dsl, cellular'), // Hints the player about the host network type.
        'quality'     => array($quality => '240, 380, 480, 720, 1080, 1440, 2160'), // Suggested quality.
        'startscreen' => array($startscreen => 'flash, html'), // Forces the startscreen to use flash or html mode.)
        'wmode'       => array($wmode => 'transparent, opaque') // Sets the wmode value for the Flash player.
    );

    foreach ($validAtts as $att => $values) {
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
        };
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
