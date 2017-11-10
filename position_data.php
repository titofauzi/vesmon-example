<?php

include 'db.php';

$sql = "SELECT * FROM vessel_info WHERE last_posdate > sysdate - 24/24 and lon < 140 and lon > 90 and lat > -10 and lat < 10";

$rs = oci_parse($conn, $sql) or die(oci_error());
oci_execute($rs);

$geojson = array(
   'type'      => 'FeatureCollection',
   'crs' => array(
          'type' => 'name',
          'properties'=> array(
            'name' => 'EPSG:4326'
            )
          ),
   'features'  => array()
);

while($row = oci_fetch_assoc($rs)){
	$feature = array(
        'type' => 'Feature', 
        'properties'=> array(
            'name' => $row['NAME']
         ),
        'geometry' => array(
            'type' => 'Point',
            # Pass Longitude and Latitude Columns here
            'coordinates' => array($row['LON'], $row['LAT'])
        )
     );

	 # Add feature arrays to feature collection array
    array_push($geojson['features'], $feature);
}

header('Content-type: application/json');
echo json_encode($geojson, JSON_NUMERIC_CHECK);
$conn = NULL;