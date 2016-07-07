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
        'html'        => '',
        'playerid'    => '',
        'info'        => '1',
        'logo'        => '1',
        'network'     => '',
        'origin'      => '',
        'quality'     => '', // 240, 380, 480, 720, 1080, 1440 or 2160
        'related'     => '1',
        'start'       => '0',
        'startscreen' => '',
        'syndication' => '',
        'wmode'       => 'transparent',
        'label'       => '',
        'labeltag'    => '',
        'wraptag'     => '',
        'class'       => __FUNCTION__
    ),$atts));

    $custom = strtolower($custom);
    if (!$video && isset($thisarticle[$custom])) {
        $video = $thisarticle[$custom];
    }

    // Check for dailymotion video ID or dailymotion URL to extract ID from
    $match = _oui_dailymotion($video);
    if ($match) {
        $video = $match;
    } elseif (empty($video)) {
        return '';
    }

    $src = '//www.dailymotion.com/embed/video/' . $video;

    $qString = array();

    // Enables the Player API.
    if ($api) {
        if (in_array($api, array('postMessage', 'fragment', 'location'))) {
            $qString[] = 'api=' . $api;
        } else {
            trigger_error("unknown attribute value; oui_dailymotion api attribute accepts the following values: postMessage, fragment, location");
            return;
        }
    }

    // Starts the playback of the video automatically after the player load.
    if (in_array($autoplay, array('1', '0'))) {
        $qString[] = 'autoplay=' . $autoplay;
    }

    // Determines if the player should display controls or not during video playback.
    if (in_array($chromeless, array('1', '0'))) {
        $qString[] = 'chromeless=' . $chromeless;
    }

    // HTML color of the controls elements' highlights.
    $qString[] = 'highlight=' . $highlight;

    // Forces the HTML5 mode.
    if (in_array($html, array('1', '0'))) {
        $qString[] = 'html=' . $html;
    }
    // Id of the player unique to the page to be passed back with all API messages.
    if ($playerid) {
        $qString[] = 'id=' . $playerid;
    }

    // Shows videos information (title/author) on the start screen.
    if (in_array($info, array('1', '0'))) {
        $qString[] = 'info=' . $info;
    }

    // Allows to hide or show the Dailymotion logo.
    if (in_array($logo, array('1', '0'))) {
        $qString[] = 'logo=' . $logo;
    }

    // Hints the player about the host network type.
    if ($network) {
        if (in_array($network, array('dsl', 'cellular'))) {
            $qString[] = 'network=' . $network;
        } else {
            trigger_error("unknown attribute value; oui_dailymotion network attribute accepts the following values: dsl, cellular");
            return;
        }
    }

    // The domain of the page hosting the Dailymotion player.
    if ($origin) {
        $qString[] = 'origin=' . $origin;
    }

    // Specifies the suggested quality for the video to be used by default.
    if ($quality) {
        if (in_array($quality, array('240', '380', '480', '720', '1080', '1440', '2160'))) {
            $qString[] = 'quality=' . $quality;
        } else {
            trigger_error("unknown attribute value; oui_dailymotion quality attribute accepts the following values: 240, 380, 480, 720, 1080, 1440, 2160");
            return;
        }
    }


    // Shows related videos at the end of the video.
    if (in_array($related, array('1', '0'))) {
        $qString[] = 'related=' . $related;
    }

    // Specifies when the video should start, value is in seconds.
    if ($start) {
        $qString[] = 'start=' . $start;
    }

    // Forces the startscreen to use flash or html mode.
    if ($startscreen) {
        if (in_array($startscreen, array('flash', 'html'))) {
            $qString[] = 'startscreen=' . $startscreen;
        } else {
            trigger_error("unknown attribute value; oui_dailymotion startscreen attribute accepts the following values: flash, html");
            return;
        }
    }

    // Passes your syndication key to the player, the value to set is your key.
    if ($syndication) {
        $qString[] = 'syndication=' . $syndication;
    }

    // Sets the wmode value for the Flash player.
    if (in_array($wmode, array('transparent', 'opaque'))) {
        $qString[] = 'wmode=' . $wmode;
    } else {
        trigger_error("unknown attribute value; oui_dailymotion wmode attribute accepts the following values: transparent, opaque");
        return;
    }

    // Check if we need to append a query string to the video src.
    if (!empty($qString)) {
        $src .= '?' . implode('&amp;', $qString);
    }

    // If the width and/or height has not been set we want to calculate new
    // ones using the aspect ratio.
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

    return parse(EvalElse($thing, $result));
}


function _oui_dailymotion($video)
{
    if (preg_match('#^(http|https)?://www\.dailymotion\.com(/video)?/([A-Za-z0-9]+)#i', $video, $matches)) {

        return $matches[3];
    }

    elseif (preg_match('#^(http|https)?://dai\.ly(/video)?/([A-Za-z0-9]+)#i', $video, $matches)) {

        return $matches[3];

    }

    return false;
}
