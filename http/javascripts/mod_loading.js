/**
TODO: Insert statement DB 
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