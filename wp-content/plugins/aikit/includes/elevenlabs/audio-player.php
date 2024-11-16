<?php

class AIKIT_Audio_Player {

    private static $instance = null;

    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new AIKIT_Audio_Player();
        }
        return self::$instance;
    }

    public function render ($audio_files) {

        $primary_color = get_option('aikit_setting_audio_player_primary_color');
        $secondary_color = get_option('aikit_setting_audio_player_secondary_color');
        $post = get_post($_GET['aikit-tts-post-id']);

        if (isset($_GET['aikit-primary-color'])) {
            $primary_color = $_GET['aikit-primary-color'];
        }

        if (isset($_GET['aikit-secondary-color'])) {
            $secondary_color = $_GET['aikit-secondary-color'];
        }

        ?>

        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="user-scalable=no">
            <title><?php echo esc_html($post->post_title); ?></title>
            <link rel="stylesheet" href="<?php echo plugins_url('aikit/includes/css/audio-player.css'); ?>">
            <style>
                body {
                    background: linear-gradient(135deg, <?php echo $primary_color ?> 0%, <?php echo $secondary_color ?> 100%) !important;
                }
            </style>
        </head>
        <body>
        <!-- Top Info -->
        <div id="title">
            <span id="track"></span>
            <div id="timer">0:00</div>
            <div id="duration">0:00</div>
        </div>

        <!-- Controls -->
        <div class="controlsOuter">
            <div class="controlsInner">
                <div id="loading"></div>
                <div class="btn" id="playBtn"></div>
                <div class="btn" id="pauseBtn"></div>
                <div class="btn" id="prevBtn"></div>
                <div class="btn" id="nextBtn"></div>
            </div>
            <div class="btn" id="playlistBtn"></div>
            <div class="btn" id="volumeBtn"></div>
        </div>

        <!-- Progress -->
        <div id="waveform"></div>
        <div id="bar"></div>
        <div id="progress"></div>

        <!-- Playlist -->
        <div id="playlist">
            <div id="list"></div>
        </div>

        <!-- Volume -->
        <div id="volume" class="fadeout">
            <div id="barFull" class="bar"></div>
            <div id="barEmpty" class="bar"></div>
            <div id="sliderBtn"></div>
        </div>

        <!-- Scripts -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.4/howler.min.js" integrity="sha512-xi/RZRIF/S0hJ+yJJYuZ5yk6/8pCiRlEXZzoguSMl+vk2i3m6UjUO/WcZ11blRL/O+rnj94JRGwt/CHbc9+6EA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="<?php echo plugins_url('aikit/includes/js/siriwave.js'); ?>"></script>
        <script src="<?php echo plugins_url('aikit/includes/js/audio-player.js'); ?>"></script>

        <script>
            <?php
                // build the below audio_files array from the $audio_files array passed in
                foreach ($audio_files as $audio_file) {
                    $audio_files_array[] = [
                        'title' => $audio_file['title'],
                        'file' => $audio_file['url'],
                    ];
                }

                // echo that as a JS array
                echo 'let audio_files = ' . json_encode($audio_files_array) . ';' . PHP_EOL;

            ?>
            let player = new Player(audio_files);

            resize(); // On load
        </script>
        </body>
        </html>
        <?php
    }
}