/*
 * NetGIS WebGIS Client
 * 
 * (c) Sebastian Pauli, NetGIS, 2017-2019
 */

/**
 * The NetGIS layers module.
 * @namespace
 */
netgis.layers =
(
	function()
	{
		"use strict";
		
		// Private Variables
		var singleLayerRequest = null;
		
		// Private Methods
		var init = function()
		{			
			// Events
			netgis.events.on( netgis.events.LAYER_TOGGLE, onLayerToggle );
			netgis.events.on( netgis.events.LAYER_REMOVE, onLayerRemove );
			netgis.events.on( netgis.events.LAYERS_LOADING, onLayersLoading );
						
			// Request WMC
			if ( netgis.params.get( "wmc_id" ) )
			{
				requestWmc( netgis.params.get( "wmc_id" ) );
			}
			else
			{
				netgis.events.call( netgis.events.LAYERS_LOADING, { loading: true } );
				
				// Request Layer
				if ( netgis.params.get( "layerid" ) )
				{
					singleLayerRequest = parseInt( netgis.params.get( "layerid" ) );
					requestLayers( [ netgis.params.get( "layerid" ) ] );
				}
				else			
					netgis.events.call( netgis.events.LAYERS_LOADING, { loading: false } );
			}
		};
		
		var requestWmc = function( id )
		{
			//TODO: clear all layers from memory
			netgis.events.call( netgis.events.LAYERS_LOADING, { loading: true } );
			
			var url = netgis.config.URL_WMC_REQUEST;
			var lastChar = url.charAt( url.length - 1 );
			if ( lastChar !== "?" && lastChar !== "&" ) url += "?";
			url += "wmc_id=" + id + "&confFileName=" + netgis.config.CONF_FILE_NAME + "&epsg=" + netgis.config.MAP_PROJECTION.split( ":" )[ 1 ] + "&withHierarchy=1";
			
			if ( netgis.config.URL_WMC_PROXY && netgis.config.URL_WMC_PROXY.length > 0 )
			{
				$.getJSON
				(
					netgis.config.URL_WMC_PROXY,
					{
						q: encodeURIComponent( url )
					},
					onWmcResponse
				);
			}
			else
			{
				$.getJSON
				(
					url,
					{
					},
					onWmcResponse
				);
			}
		};
		
		var requestLayers = function( ids )
		{
			var url = netgis.config.URL_LAYERS_REQUEST + "?languageCode=de&resultTarget=web&maxResults=40&resourceIds=" + ids.join( "," );
			
			if ( netgis.config.URL_LAYERS_PROXY && netgis.config.URL_LAYERS_PROXY.length > 0 )
			{
				$.getJSON
				(
					netgis.config.URL_LAYERS_PROXY,
					{
						q: encodeURIComponent( url )
					},
					onLayersResponse
				);
			}
			else
			{
				$.getJSON
				(
					url,
					{
					},
					onLayersResponse
				);
			}
		};
		
		var createLayer = function( layerData, parentEntity, prepend )
		{			
			// Check if layer entity with this id already exists
			var id = parseInt( layerData.id ); //NOTE: assuming layer id as integer
			var entity = netgis.entities.find( netgis.component.Layer, "id", id )[ 0 ];

			if ( ! entity )
			{
				entity = netgis.entities.create
				(
					[
						new netgis.component.Layer( id )
					],
					prepend
				);
			}

			entity.set( new netgis.component.Title( layerData.title ) );
			entity.set( new netgis.component.Name( layerData.name ) );
			entity.set( new netgis.component.Parent( parentEntity ) );
			
			if ( layerData.getLegendGraphicUrl && layerData.getLegendGraphicUrlFormat )
				entity.set( new netgis.component.Legend( layerData.getLegendGraphicUrl, layerData.getLegendGraphicUrlFormat ) );
			
			if ( layerData.legendUrl )
				entity.set( new netgis.component.Legend( decodeURIComponent( layerData.legendUrl ), layerData.getLegendGraphicUrlFormat ) );
			
			if ( layerData.layerQueryable === 1 || layerData.queryable === 1 ) //NOTE: these two props should have the same name!
				entity.set( new netgis.component.Queryable() );
			
			if ( layerData.bbox )
			{
				var bbox = layerData.bbox.split( "," );
				
				for ( var i = 0; i < bbox.length; i++ )
					bbox[ i ] = parseFloat( bbox[ i ] );
				
				entity.set( new netgis.component.Extent( bbox[ 0 ], bbox[ 1 ], bbox[ 2 ], bbox[ 3 ] ) );
			}
			
			return entity;
		};
		
		var createService = function( serviceData, prepend )
		{
			var serviceEntity = netgis.entities.create
			(
				[
					new netgis.component.Service( serviceData.id ),
					new netgis.component.Title( serviceData.title ),
					new netgis.component.Url( serviceData.getMapUrl )
				],
				prepend
			);
	
			return serviceEntity;
		};
		
		// Event Handlers
		$( document ).ready( init );
		
		var onWmcResponse = function( data )
		{
			// WMC Extent
			var bbox = data.wmc.bbox;
			
			if ( bbox )
			{
				bbox = bbox.split( "," );
				
				netgis.map.viewExtent( bbox[ 0 ], bbox[ 1 ], bbox[ 2 ], bbox[ 3 ] );
			}
			
			// KML Overlay
			var kml = data.wmc.kmloverlay;
			
			if ( kml && kml.length > 0 )
			{
				netgis.entities.create
				(
					[
						new netgis.component.Layer( -1 ),
						new netgis.component.Title( "KML" ),
						new netgis.component.KML( kml ),
						new netgis.component.Active()
					]
				);
			}
			
			// Map Layers
			var ids = [];

			for ( var l = 0; l < data.layerList.length; l++ )
			{
				var layer = data.layerList[ l ];
				
				ids.push( layer.layerId );
				
				// Layer Entity
				var entity = netgis.entities.create
				(
					[
						new netgis.component.Layer( parseInt( layer.layerId ) ), //NOTE: assuming layer id as integer
						new netgis.component.Position( layer.layerPos )
					]
				);
		
				// Set active from WMC
				if ( layer.active === true )
					entity.set( new netgis.component.Active() );
				
				if ( layer.opacity )
					entity.set( new netgis.component.Opacity( parseFloat( layer.opacity ) * 0.01 ) );
			}
			
			requestLayers( ids );
		};
		
		var onLayersResponse = function( data )
		{
			var services = data.wms.srv;
			
			// Services
			for ( var s = 0; s < services.length; s++ )
			{
				var service = services[ s ];
				
				// Service Group Layer
				var serviceEntity = createService( service );
		
				// Service Layers
				for ( var i = 0; i < service.layer.length; i++ )
				{
					var layer = service.layer[ i ];
					
					var layerEntity = createLayer( layer, serviceEntity );
					
					//TODO: recursive layer adding
			
					// Child Layers
					if ( layer.layer )
					{
						for ( var j = 0; j < layer.layer.length; j++ )
						{
							var child = layer.layer[ j ];

							var childEntity = createLayer( child, layerEntity );

							if ( child.layer )
							{
								for ( var k = 0; k < child.layer.length; k++ )
								{
									var child2 = child.layer[ k ];

									var child2Entity = netgis.layers.createLayer( child2, childEntity, true );

									if ( child2.layer )
									{
										for ( var m = 0; m < child2.layer.length; m++ )
										{
											var child3 = child2.layer[ m ];

											var child3Entity = netgis.layers.createLayer( child3, child2Entity, true );
										}
									}
								}
							}
						}
					}
					
				}
			}
			
			// Set order
			var layers = netgis.entities.get( [ netgis.component.Layer, netgis.component.Active ] );
			
			for ( var l = 0; l < layers.length; l++ )
			{
				layers[ l ].set( new netgis.component.Order( layers.length - l ) );
			}
			
			netgis.events.call( netgis.events.LAYERS_LOADING, { loading: false } );
			
			// Single Layer Request
			if ( singleLayerRequest )
			{
				var results = netgis.entities.find( netgis.component.Layer, "id", singleLayerRequest );
				
				if ( results.length > 0 )
				{
					var layer = results[ 0 ];
					
					layer.toggle( netgis.component.Active );
			
					netgis.events.call( netgis.events.LAYER_TOGGLE, { id: layer.id } );
					netgis.events.call( netgis.events.LAYER_ZOOM, { id: layer.id } );
				}
			}
		};
		
		var onLayerToggle = function( params )
		{
			//params.layer.active = params.active;
		};
		
		var onLayerRemove = function( event )
		{			
			// Remove parent if empty
			var entity = netgis.entities.get( event.id );
			var parent = entity.get( netgis.component.Parent );
			
			if ( parent )
			{
				// Check if last one
				if ( netgis.entities.find( netgis.component.Parent, "value", parent.value ).length <= 1 )
				{
					netgis.events.call( netgis.events.LAYER_REMOVE, { id: parent.value.id } );
				}
			}
			
			netgis.entities.destroy( event.id );
		};
		
		var onLayersLoading = function( event )
		{
			//TODO: clear layer entities on loading?
			
			if ( event.loading === false )
			{
				// Init active layers
				var layers = netgis.entities.get( [ netgis.component.Layer, netgis.component.Active ] );

				for ( var l = 0; l < layers.length; l++ )
				{
					var layer = layers[ l ];
					
					var children = netgis.entities.find( netgis.component.Parent, "value", layer );
					var hasChildren = children.length > 0;
					
					if ( hasChildren === false )
						netgis.events.call( netgis.events.LAYER_TOGGLE, { id: layer.id } );
				}
			}
		};
		
		// Public Interface
		var iface =
		{
			createService:	createService,
			createLayer:	createLayer
		};
		
		return iface;
	}
)();