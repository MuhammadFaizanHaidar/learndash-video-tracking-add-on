
if ( ld_video_tracking_video_data.videos_found_provider == 'vimeo' || ld_video_tracking_video_data.videos_found_provider == 'vimeo.com' || ld_video_tracking_video_data.videos_found_provider == 'www.vimeo.com' ) {
    var post_id       = ld_video_tracking_video_data.post_id;
    var user_ID       = ld_video_tracking_video_data.user_id;
    var post_type     = ld_video_tracking_video_data.post_type;
    var parent_course =  ld_video_tracking_video_data.parent_course;
    var video_title   = "";
    var pre_progress  = 0;
   
    jQuery( document ).ready( function() {
        // var ld_video_players = new Vimeo.Player('https://vimeo.com/381126025');
        // player = ld_video_players;
        // player.getVideoTitle().then(function(title) {
        //     console.log('title:', title);
        //     video_title = title;
        // });
        //console.log('ld_video_tracking_video_data[%o]', ld_video_tracking_video_data.videos_found_provider);
        var ld_video_count   = 0;
        var player;
        var ld_video_players = Array;
        jQuery('.ld-tab-content iframe').each( function(index, element) {
            ld_video_count += 1;
            
            var element_id = jQuery(element).prop('id');
            if ( ( typeof element_id === 'undefined' ) || ( element_id == '' ) ) {
                jQuery(element).prop('id', 'ld-video-player-'+ld_video_count);
                element_id = 'ld-video-player-'+ld_video_count;
            }
            console.log(ld_video_tracking_video_data.user_id);
            console.log(ld_video_tracking_video_data.post_id);
            console.log(ld_video_tracking_video_data.post_type);
            if ( typeof element_id !== 'undefined' ) {
                //console.log('element[%o]', element);
    
                ld_video_players[element_id] = new Vimeo.Player(element);
                player = ld_video_players[element_id];
                player.getVideoTitle().then(function(title) {
                    console.log('title:', title);
                    video_title = title;

                });
                if ( typeof ld_video_players[element_id] !== 'undefined' ) {
                    //console.log('player[%o]', ld_video_players[element_id]);

                    ld_video_players[element_id].ready().then(function() {
                        console.log('ready  video awesome!');
                    
                        //LearnDash_disable_assets(true);
                    
                        if (ld_video_tracking_video_data.videos_auto_start == true) {
                            ld_video_players[element_id].play();
                        }
                    });

                    ld_video_players[element_id].on('play', function(something) {
                        console.log('something[%o]', something);
                        console.log(something.seconds);
                        console.log('playing the video!');
                        //jQuery('#player-status').html('Video is playing');
                    });


                    ld_video_players[element_id].on('progress', function(something) {
                        // console.log('something[%o]', something);
                        // console.log(something);
                        // console.log('progress!');
                        if (  ( something.seconds - pre_progress > 320 ) || ( pre_progress == 0 && something.seconds > 320 ) ) {
                            jQuery.ajax({
                                url : ld_video_tracking_video_data.ajax_url,
                                type : 'post',
                                data : {
                                    action : 'video_tracking_ajax_action',
                                    pid    : post_id,
                                    uid    : user_ID,
                                    p_type : post_type,
                                    parent_course: parent_course,
                                    video_ended: 0,
                                    video_title: video_title,
                                    v_progress: something.seconds,
                                    v_percent : something.percent,
                                    v_length   :  something.duration

                                },
                                success : function( response ) {
                                    console.log(response);
                                    pre_progress = something.seconds;
                                }
                            });
                        }
                        //jQuery('#player-status').html('Video is playing');
                    });

                    ld_video_players[element_id].on('timeupdate', function(something) {
                        // console.log('something[%o]', something);
                        // console.log(something);
                        // console.log('time update!');
                        //jQuery('#player-status').html('Video is playing');
                    });
                   
                    ld_video_players[element_id].on('pause', function(something) {
                        console.log('something[%o]', something);
                        console.log(something.seconds);
                        console.log('paused the video!');
                    	//jQuery('#player-status').html('Video is paused');
                    });

                    ld_video_players[element_id].on('ended', function(something) {
                        //console.log('something[%o]', something);
                        //console.log('ended the video!');
                        //jQuery('#player-status').html('Video has ended');
                        //LearnDash_disable_assets(false);
                        console.log(something.seconds);
                        console.log("it ended");
                        jQuery.ajax({
                            url  : ld_video_tracking_video_data.ajax_url,
                            type : 'post',
                            data : {
                                action : 'video_tracking_ajax_action',
                                pid    : post_id,
                                uid    : user_ID,
                                parent_course: parent_course,
                                p_type : post_type,
                                video_title: video_title,
                                v_progress: something.duration,
                                v_percent : 1,
                                v_length   :  something.duration,
                                video_ended : 1,

                            },
                            success : function( response ) {
                                console.log(response);
                            }
                        });
                    });

                    ld_video_players[element_id].on('seeked', function( something ) {
                        // console.log('something[%o]', something);
                        // console.log(something.seconds);
                        // console.log('seeked the video!');
                        //jQuery('#player-status').html('Video has seeked');
                    });


                    player.getPlayed().then(function(played) {
                        console.log(played);
                        // played = array values of the played video time ranges.
                    }).catch(function(error) {
                        console.log("error while being played");
                        // an error occurred
                    });

                } //else {
                    //console.log('player undefined');
                    //}
            }
        });
    });
}