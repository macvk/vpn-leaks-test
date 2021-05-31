
var vpn_test_started = false;

jQuery(document).ready(function(){
	jQuery('.vltp-test .vltp-start').click(function(){
		var o = jQuery(this);
		var test_type = o.attr('data-type');
		var vltp_id = parseInt(o.attr('data-id'));
		
		if (vpn_test_started) {
			return;
		}
		
		if (typeof test_type == 'undefined' || !test_type) {
			return;
		}
		
		if (test_type.localeCompare('dns') != 0 && test_type.localeCompare('email') != 0 && test_type.localeCompare('webrtc') != 0 && test_type.localeCompare('torrent') != 0) {
			return;
		}

		if (!vltp_id) {
			return;
		}
		
		vpn_test_started = true;
		
		var vltp_settings = vltp_get_settings(vltp_id);
		var sav_button = o.html();

		if (!vltp_settings) {
			return;
		}

		image = new Image();
		image.src = vltp_settings.vltp_progress_image;
		
		image.onload = function() {
			vltp_test(o,sav_button,test_type,vltp_id);
		};
	
		image.onerror = function() {
			vltp_test(o,sav_button,test_type,vltp_id);
		};
		            
		o.html(image);

	});
});

function vltp_get_settings(vltp_id) {
	var settings = window["vltp_settings_"+vltp_id];
	
	if (typeof settings == 'undefined' || !settings) {
		return false;
	}

	return settings;
}

function vltp_test(o,sav_button,test_type,vltp_id) {


	if (test_type.localeCompare('dns') == 0) {
		vltp_test_dns(o,sav_button,test_type,vltp_id);
	}

	if (test_type.localeCompare('email') == 0) {

		var vltp_settings = vltp_get_settings(vltp_id);

		if (!vltp_settings) {
			return;
		}

		o.parent().append('<div class="vltp-info">'+vltp_settings.vltp_email_message+'</div>');
		vltp_test_email_check(o,sav_button,test_type,vltp_id);
	}

	if (test_type.localeCompare('torrent') == 0) {

		var vltp_settings = vltp_get_settings(vltp_id);

		if (!vltp_settings) {
			return;
		}

		o.parent().append('<div class="vltp-info">'+vltp_settings.vltp_torrent_message+'</div>');
		vltp_test_torrent_check(o,sav_button,test_type,vltp_id);
	}

	if (test_type.localeCompare('webrtc') == 0) {
		vltp_test_webrtc(o,sav_button,test_type,vltp_id);
	}
}

function vltp_test_email_check(o,sav_button,test_type,vltp_id) {

	var vltp_settings = vltp_get_settings(vltp_id);

	if (!vltp_settings) {
		return;
	}

        var data = {
		action: 'vltp_test_email_check',
		vltp_test_id: vltp_settings.vltp_test_id
        };
        
	jQuery.ajax({url:vltp_settings.vltp_ajax_url, type:"POST", data:data, complete:function(xhr) {

		var done = false;
		try {
			j = JSON.parse(xhr.responseText);
			done = parseInt(j.done);
		}
		catch (e) {
		}
		
		if (done) {
			document.location = vltp_settings.vltp_url;
		}
		else {
			setTimeout(function(){
				vltp_test_email_check(o,sav_button,test_type,vltp_id);
			},1000);
		}
	}});
}

function vltp_test_torrent_check(o,sav_button,test_type,vltp_id) {

	var vltp_settings = vltp_get_settings(vltp_id);

	if (!vltp_settings) {
		return;
	}

        var data = {
		action: 'vltp_test_torrent_check',
		vltp_test_id: vltp_settings.vltp_test_id
        };
        
	jQuery.ajax({url:vltp_settings.vltp_ajax_url, type:"POST", data:data, complete:function(xhr) {

		var done = false;
		try {
			j = JSON.parse(xhr.responseText);
			done = parseInt(j.done);
		}
		catch (e) {
		}
		
		if (done) {
			document.location = vltp_settings.vltp_url;
		}
		else {
			setTimeout(function(){
				vltp_test_torrent_check(o,sav_button,test_type,vltp_id);
			},1000);
		}
	}});
}

function vltp_test_dns(o,sav_button,test_type,vltp_id) {

	var image_count = 20;
	var images = [];
	var i;


	var vltp_settings = vltp_get_settings(vltp_id);

	if (!vltp_settings) {
		return;
	}

	var test_id = vltp_settings.vltp_test_id;
	
	for (i=0;i<image_count;i++)
	{
		images.push(new Image());
	}
	
	div = jQuery('<div id="vltp-image-test"></div>');
	jQuery('body').append(div);
	
	for (i=0;i<image_count;i++) {
		div.append(images[i]);
		images[i].src = 'https://'+(i+1)+'.' +test_id+'.bash.ws/img.png';
	}
	
	images[image_count-1].onload = function() {
		document.location = vltp_settings.vltp_url;
	};
	
	images[image_count-1].onerror = function() {
		document.location = vltp_settings.vltp_url;
	};
	
}

function vltp_test_webrtc(o,sav_button,test_type,vltp_id) {

	var ips = new Array();
	var vltp_settings = vltp_get_settings(vltp_id);

	if (!vltp_settings) {
		return;
	}

        //insert IP addresses into the page
        try {
            getIPs(function(ip){
            
		if (ips.indexOf(ip) !=-1)
			return;

		ips.push(ip);

            });
        }
        catch (error)
        {
        }

	setTimeout(function(){

	        var data = {
			action: 'vltp_test_webrtc',
			vltp_test_id: vltp_settings.vltp_test_id,
			ips: ips
	        };
	
		jQuery.ajax({url:vltp_settings.vltp_ajax_url, type:"POST", data:data, complete:function(xhr) {
			var done = false;
			try {
				j = JSON.parse(xhr.responseText);
				done = parseInt(j.done);
			}
			catch (e) {
			}
		
			document.location = vltp_settings.vltp_url;
		}});

	}, 1000);
	
}

function getIPs(callback){
        var ip_dups = {};

        //compatibility for firefox and chrome
        var RTCPeerConnection = window.RTCPeerConnection
            || window.mozRTCPeerConnection
            || window.webkitRTCPeerConnection;
        var useWebKit = !!window.webkitRTCPeerConnection;

        //bypass naive webrtc blocking using an iframe
        if(!RTCPeerConnection){
            //NOTE: you need to have an iframe in the page right above the script tag
            //
            //<iframe id="iframe" sandbox="allow-same-origin" style="display: none"></iframe>
            //<script>...getIPs called in here...
            //
            var win = iframe.contentWindow;
            RTCPeerConnection = win.RTCPeerConnection
                || win.mozRTCPeerConnection
                || win.webkitRTCPeerConnection;
            useWebKit = !!win.webkitRTCPeerConnection;
        }

	if(!RTCPeerConnection){
		return;
	}                

        //minimal requirements for data connection
        var mediaConstraints = {
            optional: [{RtpDataChannels: true}]
        };

        var servers = {iceServers: [{urls: "stun:stun.l.google.com:19302?transport=udp"}]};

        //construct a new RTCPeerConnection
        var pc = new RTCPeerConnection(servers, mediaConstraints);

        function handleCandidate(candidate){
            //match just the IP address
            var ip_regex = /([0-9]{1,3}(\.[0-9]{1,3}){3}|(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])))($|\s)/;
            var ip = ip_regex.exec(candidate);

            if(ip === null || ip.length<1) {
                return;
            }

            var ip_addr = ip[1];

            //remove duplicates
            if(ip_dups[ip_addr] === undefined)
                callback(ip_addr);

            ip_dups[ip_addr] = true;
        }

        //listen for candidate events
        pc.onicecandidate = function(ice){

            //skip non-candidate events
            if(ice.candidate)
                handleCandidate(ice.candidate.candidate);
        };

        //create a bogus data channel
        pc.createDataChannel("rtc");

        //create an offer sdp
        pc.createOffer(function(result){

            //trigger the stun server request
            pc.setLocalDescription(result, function(){}, function(){});

        }, function(){});

        //wait for a while to let everything done
        setTimeout(function(){
            //read candidate info from local description
            var lines = pc.localDescription.sdp.split('\n');

            lines.forEach(function(line){
                if(line.indexOf('a=candidate:') === 0)
                    handleCandidate(line);
            });
        }, 1000);
}
