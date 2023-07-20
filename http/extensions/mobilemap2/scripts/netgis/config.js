/*
 * NetGIS WebGIS Client
 * 
 * (c) Sebastian Pauli, NetGIS, 2017
 */

/**
 * Configuration namespace.
 * @namespace
 * @name config
 * @memberof netgis
 */
netgis.config = 
{
	MAP_CONTAINER_ID:		"map-container",
	CONF_FILE_NAME:			"mobilemap2",
	
	/** Initial map center coordinate x in main map projection. */
	INITIAL_CENTER_X:		342000,
	/** Initial map center coordinate y in main map projection. */
	INITIAL_CENTER_Y:		5470000,
	
	/** Initial map zoom scale (e.g. 10000 = 1:10000). */
	INITIAL_SCALE:			250000,
	MAP_SCALES:				[ 500, 1000, 3000, 5000, 8000, 10000, 15000, 25000, 50000, 100000, 150000, 250000, 500000, 1000000, 1500000, 2000000 ],
	
	/** Minimum scale to zoom to place search result.  */
	MIN_SEARCH_SCALE:		3000,
	
	/** List of available map projections (identifier, proj4 definition). */
	MAP_PROJECTIONS:		[
								[ "EPSG:31466", "+proj=tmerc +lat_0=0 +lon_0=6 +k=1 +x_0=2500000 +y_0=0 +ellps=bessel +towgs84=598.1,73.7,418.2,0.202,0.045,-2.455,6.7 +units=m +no_defs" ],
								[ "EPSG:31467", "+proj=tmerc +lat_0=0 +lon_0=9 +k=1 +x_0=3500000 +y_0=0 +ellps=bessel +datum=potsdam +units=m +no_defs" ],
								[ "EPSG:25832", "+proj=utm +zone=32 +ellps=GRS80 +units=m +no_defs" ],
								[ "EPSG:32632", "+proj=utm +zone=32 +ellps=WGS84 +datum=WGS84 +units=m +no_defs" ]
							],
							
	/** Main projection used by the map view. */
	MAP_PROJECTION:			"EPSG:25832",
	
	/** Map extent (min x, min y, max x, max y). */
	MAP_EXTENT:				[ 296000, 5440000, 387000, 5500000 ],
	
	/** if reset Modern Client and call Mobile Client no BBox is given */
	MAP_EXTENT_STRING: "273299.735097,5440730.0440917,419078.264903,5502339.9559083",
	
	/** Default map layer opacity (0.0 - 1.0). */
	MAP_DEFAULT_OPACITY:	0.8,
	
	/** Maximum number of map view history entries. */
	MAX_HISTORY:			10,

	/** Default style for GeoRSS points. */
	GEORSS_POINT_RADIUS:		8,
	GEORSS_POINT_FILL_COLOR:	"#861d31",
	GEORSS_POINT_STROKE_COLOR:	"white",
	GEORSS_POINT_STROKE_WIDTH:	2,
	
	/** Default style for marker points. */
	MARKER_POINT_RADIUS:		8,
	MARKER_POINT_FILL_COLOR:	"#861d31",
	MARKER_POINT_STROKE_COLOR:	"white",
	MARKER_POINT_STROKE_WIDTH:	2,

	/** Service URLs (avoid proxies by setting to null or empty string). */
	URL_WMC_PROXY:			null,
	URL_WMC_REQUEST:		"../../php/mod_exportWmc2Json.php",
	
	URL_LAYERS_PROXY:		null,
	URL_LAYERS_REQUEST:		"../../php/mod_callMetadata.php",

	URL_SEARCH_PROXY:		null,
	URL_SEARCH_REQUEST:		"../../geoportal/gaz_geom_mobile.php",
	SEARCH_REQUEST_EPSG:    "25832",
	
	URL_BACKGROUND_HYBRID:	"https://geoportal.saarland.de/mapcache/tms/1.0.0/karte_sl@UTM32N",
	HYBRID_RESOLUTIONS: 	[529.16666666670005270134,396.87500000000000000000,264.58333333330000414207,132.29166666669999585793,66.14583333330000414207,39.68750000000000000000,26.45833333330000058936,13.22916666669999941064,6.61458333329999970118,3.96875000000000000000,2.64583333330000014527,2.11666666670000003236,1.32291666670000007677,0.79375000000000000000,0.26458333330000001204,0.13229166670000001016],
	URL_BACKGROUND_AERIAL:	"https://geoportal.saarland.de/freewms/dop2016?",

	URL_FEATURE_INFO_PROXY:	null,

	URL_HEIGHT_PROXY:		null,
	URL_HEIGHT_REQUEST:		"scripts/heightRequest.php?&lang=de",
	URL_USAGE_TERMS:		"https://www.saarland.de/SharedDocs/Downloads/DE/LVGL/Datenschutz/datenschutz_geoportal.html"
};
