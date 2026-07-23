var auflösung = 500;
var url_dgm = "https://geoportal.saarland.de/http_auth/46159?";
var layer_dgm = "sl_dgm1_2016";
var url_dop = "https://geoportal.saarland.de/freewms/dop2023?";
var layer_dop = "sl_dop20_rgb";
//https://geoportal.saarland.de/freewms/truedop?
//https://geoportal.saarland.de/freewms/dop2023?

var min_ = 0;
var max_ = 695.24;
var range = max_ - min_;
var ein_px_gleich_wieviel_meter = (max_ - min_) / 255.0;
var pixel_to_add = min_ / ein_px_gleich_wieviel_meter;