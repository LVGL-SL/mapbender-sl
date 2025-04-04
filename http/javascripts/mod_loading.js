/**
INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('Geoportal-SL-2020','sandclock',2,1,'displays a sand clock while waiting for requests','','div','','',0,0,NULL ,NULL ,NULL ,'','','div','mod_loading.js','','mapframe1','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL-2020', 'sandclock', 'css', '.loader-line {
  width: 200px;
  height: 2px;
  position: relative;
  overflow: hidden;
  -webkit-border-radius: 20px;
  -moz-border-radius: 20px;
  border-radius: 20px;
}

.loader-line:before {
  content: "";
  position: absolute;
  left: -50%;
  height: 2px;
  width: 40%;
  background-color:#002966;
-webkit-animation: lineAnim 1s linear infinite;
            -moz-animation: lineAnim 1s linear infinite;
            animation: lineAnim 1s linear infinite;
            -webkit-border-radius: 20px;
            -moz-border-radius: 20px;
            border-radius: 20px;
        }

        @keyframes lineAnim {
            0% {
                left: -40%;
            }
            50% {
                left: 20%;
                width: 40%;
            }
            100% {
                left: 100%;
                width: 100%;
            }
        }
}
', '' ,'text/css');
**/
 
var Sandclock = function (options) {
	var that = this;

	//
	// check if target is set correctly
	for (var i in options.target) {
		if (!Mapbender.modules[options.target[i]]) {
			new Mb_exception(
				"Target " + options.target[i] + " not found by " + options.id
			);
		}
	}

	//
	// sandclock image
	//
	if (!options.mod_sandclock_image) {
		options.mod_sandclock_image = "../img/sandclock.gif";
	}

	var mod_sandclock_img = new Image();
	mod_sandclock_img.src = options.mod_sandclock_image;
	
	//
	// block the html element with a css shadow
	//
	if (typeof options.blockElement === "undefined") {
		options.blockElement = 0;
	}
	
	//
	// if target objects are maps, bind sandclock to its repaint event
	//
	for (var i in options.target) {
		(function () {
			var nodeId = options.target[i];
			if (typeof Mapbender.modules[nodeId].afterMapRequest === "object") {
				Mapbender.modules[nodeId].afterMapRequest.register(function (obj) {
					that.show(nodeId, {
						mapId: obj.myMapId,
						blockElement: options.blockElement
					});
				});
			}
		})();
	}

	var aktiv;
	
	/** 
	 * Method: show
	 *
	 * Displays the sandclock
	 */
	this.show = function (nodeId, arg) {
		var mapId = arg.mapId;

		var $clock = $("#" + nodeId + "_sandclock");
		
		if ($clock.size() === 0) {
			//create Box Elements
			$(
				"<div class='loader-line' id='" + nodeId + "_sandclock'></div>"
			).css({
				position: "absolute",
				top: "0px",
				left: "0px",
				width: "100%",
				overflow: "hidden",
				zIndex: 1000,
				visibility: "visible"
			}).appendTo($("#" + nodeId));
		}
		$clock.css({
			zIndex: 1000,
			visibility: "visible"
		}).html(
			"<div style='position:absolute;'></div>"
		);

		if (arg.blockElement) {
			$clock.addClass("ui-widget-overlay");
		}
	
		//
		// if mapId is not given, the sandclock has to be turned off manually
		// by calling hide(). Usually this is done in a callback 
		// function.
		//
		if (typeof mapId !== "undefined") {
			aktiv = setTimeout(function () {
				that.show(nodeId, {
					mapId: mapId,
					blockElement: arg.blockElement
				});
			},10);
			var mapIdArray = mapId.split(",");
			var complete = true;
			var map = Mapbender.modules[nodeId];
			
			for (var i = 0; i < mapIdArray.length && complete; i++) {
				var currentMapId = mapIdArray[i];
				var myDoc = map.getDomElement().ownerDocument;
				if(myDoc.getElementById(currentMapId) && 
					!myDoc.getElementById(currentMapId).complete) {
					complete = false;
				}
			}
			if (complete) {
				clearTimeout(aktiv);
				that.hide(nodeId, map);
			} 
		}
	};
	
	/**
	 * Method: hide
	 *
	 * Hides the sandclock
	 */
	this.hide = function (nodeId) {
		$("#" + nodeId + "_sandclock").css({
			visibility: "hidden"
		}).removeClass("ui-widget-overlay").empty();
	};
};

Mapbender.events.init.register(function () {
	$.extend(Mapbender.modules[options.id], new Sandclock(options));
});