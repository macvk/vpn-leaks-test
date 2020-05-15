
var vpn_test_started = false;

jQuery(document).ready(function(){
	jQuery('.vlt-test .vlt-start').click(function(){
		var o = jQuery(this);
		var test_type = o.attr('data-type');
		
		if (vpn_test_started) {
			return;
		}
		
		if (typeof test_type == 'undefined' || !test_type) {
			return;
		}
		
		if (test_type.localeCompare('dns') != 0 && test_type.localeCompare('email') && test_type.localeCompare('webrtc')) {
			return;
		}
		
		vpn_test_started = true;
		
		var sav_button = o.html();
		
		image = new Image();
		image.src = vlt_settings.vlt_progress_image;
		
		image.onload = function() {
			vlt_test(o,sav_button,test_type);
		};
	
		image.onerror = function() {
			vlt_test(o,sav_button,test_type);
		};
		            
		o.html(image);

	});
});


function vlt_test(o,sav_button,test_type) {
	if (test_type.localeCompare('dns') == 0) {
		vlt_test_dns(o,sav_button,test_type);
	}

	if (test_type.localeCompare('email') == 0) {
		o.parent().append('<div class="vlt-info">'+vlt_settings.vlt_email_message+'</div>');
		vlt_test_email_check(o,sav_button,test_type);
	}

	if (test_type.localeCompare('webrtc') == 0) {
		vlt_test_webrtc(o,sav_button,test_type);
	}
}

function vlt_test_email_check(o,sav_button,test_type) {

        var data = {
		action: 'vlt_test_email_check',
		vlt_test_id: vlt_settings.vlt_test_id
        };

	jQuery.ajax({url:vlt_settings.vlt_ajax_url, type:"POST", data:data, complete:function(xhr) {
		var done = false;
		try {
			j = JSON.parse(xhr.responseText);
			done = parseInt(j.done);
		}
		catch (e) {
		}
		
		if (done) {
			document.location = vlt_settings.vlt_url;
		}
		else {
			setTimeout(function(){
				vlt_test_email_check(o,sav_button,test_type);
			},1000);
		}
	}});
}

function vlt_test_dns(o,sav_button,test_type) {

	var image_count = 20;
	var images = [];
	var i;
	var leak_id = vlt_settings.vlt_test_id;

	for (i=0;i<image_count;i++)
	{
		images.push(new Image());
	}
	
	div = jQuery('<div id="vlt-image-test"></div>');
	jQuery('body').append(div);
	
	for (i=0;i<image_count;i++) {
		div.append(images[i]);
		images[i].src = 'https://'+(i+1)+'.' +leak_id+'.bash.ws/img.png';
	}
	
	images[image_count-1].onload = function() {
		document.location = vlt_settings.vlt_url;
	};
	
	images[image_count-1].onerror = function() {
		document.location = vlt_settings.vlt_url;
	};
	
}

function vlt_test_webrtc(o,sav_button,test_type) {

	var leak_id;
	var ips = new Array();
	leak_id = vlt_settings.vlt_test_id;
	
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
			action: 'vlt_test_webrtc',
			vlt_test_id: vlt_settings.vlt_test_id,
			ips: ips
	        };
	
		jQuery.ajax({url:vlt_settings.vlt_ajax_url, type:"POST", data:data, complete:function(xhr) {
			var done = false;
			try {
				j = JSON.parse(xhr.responseText);
				done = parseInt(j.done);
			}
			catch (e) {
			}
		
			document.location = vlt_settings.vlt_url;
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
