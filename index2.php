<html lang="en">
  <head>
    <link rel="stylesheet" href="https://openlayers.org/en/v4.0.1/css/ol.css" type="text/css">

    <style>
      .map {
        height: 100%;
        width: 100%;
        
      }
    </style>
     <!-- The line below is only needed for old environments like Internet Explorer and Android 4.x -->
    <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList,URL"></script>
    <script src="https://openlayers.org/en/v4.0.1/build/ol.js" type="text/javascript"></script>
    <title>OpenLayers example</title>
  </head>
  <body style="margin: 0">
    <div id="map" class="map"></div>
    <script type="text/javascript">
       var earthquakeFill = new ol.style.Fill({
        color: 'rgba(255, 153, 0, 0.8)'
      });
      var earthquakeStroke = new ol.style.Stroke({
        color: 'rgba(255, 204, 0, 0.2)',
        width: 1
      });
      var textFill = new ol.style.Fill({
        color: '#fff'
      });
      var textStroke = new ol.style.Stroke({
        color: 'rgba(0, 0, 0, 0.6)',
        width: 3
      });
      var invisibleFill = new ol.style.Fill({
        color: 'rgba(255, 255, 255, 0.01)'
      });

    var maxFeatureCount, vectorLayer;
      function calculateClusterInfo(resolution) {
        maxFeatureCount = 0;
        var features = vectorLayer.getSource().getFeatures();
        var feature, radius;
        for (var i = features.length - 1; i >= 0; --i) {
          feature = features[i];
          var originalFeatures = feature.get('features');
          var extent = ol.extent.createEmpty();
          var j, jj;
          for (j = 0, jj = originalFeatures.length; j < jj; ++j) {
            ol.extent.extend(extent, originalFeatures[j].getGeometry().getExtent());
          }
          maxFeatureCount = Math.max(maxFeatureCount, jj);
          radius = 0.25 * (ol.extent.getWidth(extent) + ol.extent.getHeight(extent)) /
              resolution;
          feature.set('radius', radius);
        }
      }

      function createEarthquakeStyle(feature) {
        // 2012_Earthquakes_Mag5.kml stores the magnitude of each earthquake in a
        // standards-violating <magnitude> tag in each Placemark.  We extract it
        // from the Placemark's name instead.
        //var name = feature.get('name');
        //var magnitude = parseFloat(name.substr(2));
        var magnitude = 5;
        var radius = 5;

        return new ol.style.Style({
          geometry: feature.getGeometry(),
          image: new ol.style.RegularShape({
            radius1: radius,
            radius2: 3,
            points: 3,
            angle: Math.PI,
            fill: earthquakeFill,
            stroke: earthquakeStroke
          })
        });
      }

      var currentResolution;
      function styleFunction(feature, resolution) {
        if (resolution != currentResolution) {
          calculateClusterInfo(resolution);
          currentResolution = resolution;
        }
        var style;
        var size = feature.get('features').length;
        if (size > 1) {
          style = new ol.style.Style({
            image: new ol.style.Circle({
              radius: feature.get('radius'),
              fill: new ol.style.Fill({
                color: [255, 153, 0, Math.min(0.8, 0.4 + (size / maxFeatureCount))]
              })
            }),
            text: new ol.style.Text({
              text: size.toString(),
              fill: textFill,
              stroke: textStroke
            })
          });
        } else {
          var originalFeature = feature.get('features')[0];
          style = createEarthquakeStyle(originalFeature);
        }
        return style;
      }

      var vectorLayer = new ol.layer.Vector({
        //source: new ol.source.Cluster({
          //distance: 40,
          source: new ol.source.Vector({
            url: "position_data.php",
            format: new ol.format.GeoJSON(),
            projection: 'EPSG:3857'
          })
        //}),
        //style: styleFunction
      });

      var map = new ol.Map({
        target: 'map',
        layers: [
          new ol.layer.Tile({
            source: new ol.source.OSM()
          })
          ,vectorLayer
        ],
        view: new ol.View({
          projection: 'EPSG:4326',
          center: [127, 0],
          zoom: 5
        })
      });

      map.on('click', function(evt){
        var feature = map.forEachFeatureAtPixel(evt.pixel,
        function(feature, layer) {
          return feature;
        });
        if (feature) {
          var geometry = feature.getGeometry();
          var coord = geometry.getCoordinates();
          console.info(feature.get('name'));
          console.info(feature.getProperties());
        }
});
    </script>
  </body>
</html>