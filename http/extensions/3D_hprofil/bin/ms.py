#!/usr/bin/env python3
import cgi
import mapscript
import sys

# 1. CORS-Header setzen (Erlaubt Anfragen von überall)
#sys.stdout.buffer.write(b"Access-Control-Allow-Origin: https://<host>\n")
# Optional: Erlaubt bestimmte Methoden
sys.stdout.buffer.write(b"Access-Control-Allow-Methods: GET, POST, OPTIONS\n")
# Optional: Erlaubt bestimmte Header (z.B. für JSON-POST)
sys.stdout.buffer.write(b"Access-Control-Allow-Headers: Content-Type\n")
# WICHTIG: Die leere Zeile zwischen Headern und Inhalt
# CGI-Parameter auslesen
form = cgi.FieldStorage()
points_param = form.getvalue("points")
point_param = form.getvalue("point")
epsg_param   = "25832" #form.getvalue("epsg")
bbox_param   = form.getvalue("bbox")   # Format: minx,miny,maxx,maxy
width_param  = form.getvalue("width")
height_param = form.getvalue("height")

# Punkte parsen
punkte = []
punkt = []
if points_param:
    for pair in points_param.split(";"):
        x, y = pair.split(",")
        punkte.append((float(x), float(y)))
		
		
if point_param:
    x, y = point_param.split(",")
    punkt.append((float(x), float(y)))
# Mapfile laden
mapObj = mapscript.mapObj("../static/line.map")

layer2 = mapObj.getLayerByName("inline_points_gk2")
shape2 = mapscript.shapeObj(mapscript.MS_SHAPE_POINT)
for x, y in punkte:
    
    line2 = mapscript.lineObj()
    line2.add(mapscript.pointObj(x, y))
    shape2.add(line2)
layer2.addFeature(shape2)


layer3 = mapObj.getLayerByName("inline_point_gk2")
shape3 = mapscript.shapeObj(mapscript.MS_SHAPE_POINT)
for x, y in punkt:
    
    line3 = mapscript.lineObj()
    line3.add(mapscript.pointObj(x, y))
    shape3.add(line3)
layer3.addFeature(shape3)


layer4 = mapObj.getLayerByName("inline_startpoint_gk2")
shape4 = mapscript.shapeObj(mapscript.MS_SHAPE_POINT)
for x, y in punkte:
    
    line4 = mapscript.lineObj()
    line4.add(mapscript.pointObj(x, y))
    shape4.add(line4)
    break
layer4.addFeature(shape4)



layer = mapObj.getLayerByName("inline_line_gk2")
shape = mapscript.shapeObj(mapscript.MS_SHAPE_LINE)
line = mapscript.lineObj()
for x, y in punkte:
    line.add(mapscript.pointObj(x, y))
shape.add(line)
layer.addFeature(shape)






# EPSG setzen
if epsg_param:
    mapObj.setProjection(f"init=epsg:{epsg_param}")
    layer.setProjection(f"init=epsg:{epsg_param}")
    layer2.setProjection(f"init=epsg:{epsg_param}")
    layer3.setProjection(f"init=epsg:{epsg_param}")
    layer4.setProjection(f"init=epsg:{epsg_param}")


# BoundingBox setzen
if bbox_param:
    minx, miny, maxx, maxy = map(float, bbox_param.split(","))
    mapObj.setExtent(minx, miny, maxx, maxy)
    layer.setExtent(minx, miny, maxx, maxy)
    layer2.setExtent(minx, miny, maxx, maxy)
    layer3.setExtent(minx, miny, maxx, maxy)
    layer4.setExtent(minx, miny, maxx, maxy)
# Bildgröße setzen
if width_param and height_param:
    mapObj.setSize(int(width_param), int(height_param))
# Karte rendern
#img = mapObj.prepareImage()
#layer2.draw(mapObj,img)
#img.save("/var/www/tmp/output.png")

#shape.draw(mapObj, layer, img)
img = mapObj.draw()
#img.save("/var/www/tmp/output.png")


sys.stdout.buffer.write(b"Content-type: image/png\n\n")
sys.stdout.buffer.write(img.getBytes())
#sys.stdout.buffer.write(img.getBytes())



