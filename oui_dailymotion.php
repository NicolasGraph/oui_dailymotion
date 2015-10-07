<?php
$plugin['name'] = 'oui_dailymotion';

$plugin['version'] = '1.1.1';
$plugin['author'] = 'Nicolas Morand, Andy Carter';
$plugin['author_uri'] = '';
$plugin['description'] = 'Embed Dailymotion videos with customised player';
$plugin['type'] = 0;

@include_once('zem_tpl.php');

if (0) {
	?>
# --- BEGIN PLUGIN HELP ---

h1. oui_dailymotion

Easily embed Dailymotion videos in articles and customise the appearance of the player.

h2. Table of contents

# "Plugin requirements":#help-section01
# "Installation":#help-section02
# "Tags":#help-section03
# "Examples":#help-section04
# "Author":#help-section05
# "License":#help-section06

h2(#help-section01). Plugin requirements

oui_dailymotion's minimum requirements:

* Textpattern 4.5+


h2(#help-section02). Installation

To install go to the 'plugins' tab under 'admin' and paste the plugin code into the 'Install plugin' box, 'upload' and then 'install'. Please note that you will need to set-up a custom field to use for associating videos with articles, unless you choose to directly embed the new tag in the article text.


h2(#help-section03). Tags

h3. oui_dailymotion

Embeds a Dailymotion video in the page using an iframe.

bc. <txp:oui_dailymotion />

h4. Video attributes

* _video_ - Dailymotion url or video ID for the video you want to embed
* _custom_ - Name of the custom field containing video IDs/urls associated with article
* _sart_ - Start position (in seconds) of the video as an integer
* _autoplay_ - '1' to autoplay the video, '0' to turn off autoplay (default)

h4. Basic attributes

* _label_ - Label for the video
* _labeltag_ - Independent wraptag for label
* _wraptag_ - HTML tag to be used as the wraptag, without brackets
* _class_ - CSS class attribute for wraptag

h3. Customising the Dailymotion player

You can customise the appearance of the Dailymotion player using this plugin to define things like colours and size.

* _width_ - Width of video
* _height_ - Height of video
* _ratio_ - Aspect ratio (defaults 4:3)
* _chromless_ - '1' to hide controls, '0' to show them (default)
* _highlight_ - Apply 'ffcc33' (default) or any hexadecimal color value to the the controls. Dark or light theme will be automatically adjusted.
* _info_ - '0' to hide controls, '1' to show them (default)
* _logo_ - '0' to hide controls, '1' to show them (default)
* _quality_ - Use either "240", "380", "480", "720", "1080", "1440" or "2160" to adjust the video quality
* _related_ - '0' to hide controls, '1' to show them (default)

h2. oui_if_dailymotion

In addition to oui_dailymotion this plugin also comes with oui_if_dailymotion, a conditional tag for checking if the video URL is a Dailymotion one.

bc. <txp:oui_if_dailymotion video="[URL]"></txp:oui_if_dailymotion>

h4. Attributes

Use one or the other of the following:

* _custom_ - Name of the custom field containing video IDs/urls associated with article
* _video_ - A URL to check if it is a valid Dailymotion URL

h2(#help-section04). Examples

h3. Example 1: Use custom field to associate video with an article

bc. <txp:oui_dailymotion custom="Dailymotion" />

h3. Example 2: Set the size of the player

bc. <txp:oui_dailymotion video="https://Dailymotion.com/86295452" width="500" ratio="16:9" />

h3. Example 3: Using the conditional tag

bc.. <txp:oui_if_dailymotion video="https://Dailymotion.com/86295452">
	Yes
<txp:else />
	No
</txp:oui_if_dailymotion>

h2(#help-section05). Author

Made Dailymotion compatible by Nicolas Morand form "Andy Carter":http://andy-carter.com's plugins "arc_youtube":http://andy-carter.com/txp/arc_youtube and "arc_vimeo":http://andy-carter.com/txp/arc_vimeo.

h2(#help-section06). License

The MIT License (MIT)

Copyright (c) 2014 Andy Carter, (c) 2015 Nicolas Morand

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

function oui_dailymotion($atts, $thing)
{
	global $thisarticle;

    extract(lAtts(array(
        'video'       => '',
        'custom'      => 'dailymotion ID',
        'width'       => '0',
        'height'      => '0',
        'ratio'       => '4:3',
        'autoplay'    => '0',
		'chromeless'  => '0',
		'highlight'   => 'ffcc33',
		'info'        => null,
		'logo'        => null,
		'quality'     => null, // 240, 380, 480, 720, 1080, 1440 or 2160
        'related'     => '1',
        'start'     => '0',
        'label'       => '',
        'labeltag'    => '',
        'wraptag'     => '',
        'class'       => __FUNCTION__
    ),$atts));

    $custom = strtolower($custom);
    if (!$video && isset($thisarticle[$custom])) {
        $video = $thisarticle[$custom];
    }

    $v = null;
    $p = null;

    // Check for dailymotion video ID or dailymotion URL to extract ID from
    $match = _oui_dailymotion($video);
    if (!empty($match['v'])) {
        $v = $match['v'];
    } elseif (!empty($match['p'])) {
        $p = $match['p'];
    } elseif (!empty($video)) {
        $v = $video;
    } else {
        return '';
    }

    $src = '//www.dailymotion' . '.com/embed/video/';

    $src .= !empty($v) ? $v : null;

    $qString = array();

    // Enable autoplay.
    if ($autoplay) {
        $qString[] = 'autoPlay=1';
    }

     // Enable controls during video playback.
    if ($chromeless) {
        $qString[] = 'chromeless=1';
    }
 
    // Set the player UI's color.
    $qString[] = 'highlight='.$highlight.'';

    // Show or hide the video information display by default.
    if ($info!==null) {
        $qString[] = 'info=' . ($info ? '1' : '0');
    }

    // Show or hide the video logo display by default.
    if ($logo!==null) {
        $qString[] = 'logo=' . ($logo ? '1' : '0');
    }

    // Change quality.
    if ($quality!==null && in_array($quality, array(240, 380, 480, 720, 1080, 1440, 2160))) {
        $qString[] = 'quality=' . $quality;
    }

    // Disable related videos.
    if (!$related) {
        $qString[] = 'related=0';
    }

    // Set the start position of the video.
    if ($start) {
        $qString[] = 'start=' . $start;
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
    if (preg_match("/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.dailymotion\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i", $video)) {

        $urlc = parse_url($video);
        $qstr = $urlc['query'];
        parse_str($qstr, $qarr);

        if (isset($qarr['v'])) {

            return array('v' => $qarr['v']);

        } elseif (isset($qarr['p'])) {

            return array('p' => $qarr['p']);

        } else {

            return false;

        }

    }  elseif (preg_match("/^[a-zA-Z]+[:\/\/]+dai\.ly\/([A-Za-z0-9]+)/i", $video, $matches)) {

        return array('v' => $matches[1]);

    }

    return false;
}


# --- END PLUGIN CODE ---

?>
