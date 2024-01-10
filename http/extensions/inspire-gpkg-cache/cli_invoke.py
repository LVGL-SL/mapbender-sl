import json
import sys
import os
import subprocess

from inspire_gpkg_cache.spatial_data_cache import SpatialDataCache, get_env_variable_from_geoportal_sl
#from builtins import False

print(sys.argv[1])

#os.environ["HTTP_PROXY"] = "http://{proxyhost}:{proxyport}"
#os.environ["HTTPS_PROXY"] = "http://{proxyhost}:{proxyport}"

# https://stackoverflow.com/questions/50607908/how-to-send-mail-in-python-on-linux-server-via-mail
def send_mail(subject: str, body: str, mail_address:str):
    sender_mail_adress = get_env_variable_from_geoportal_sl("ROOT_EMAIL_ADDRESS","geoportal.saarland@lvgl.saarland.de")
    body_str_encoded_to_byte = body.encode()
    return_stat = subprocess.run([f"mail", f"-s {subject}", f"-aFrom:{sender_mail_adress}", mail_address], input=body_str_encoded_to_byte)
    print(return_stat) 

configuration = json.loads(sys.argv[1])

if sys.argv[2] == 'checkOptions':
    # might be that the gdi catalogue must be en instead of ger for us -> Save old for now in comment: https://gdk.gdi-de.org/gdi-de/srv/eng/csw
    cache = SpatialDataCache(configuration['dataset_configuration'], json.dumps(configuration['area_of_interest']), ["https://gdk.gdi-de.org/gdi-de/srv/ger/csw", "https://vocabulary.geoportal.rlp.de/geonetwork/srv/ger/csw", "https://inspire-geoportal.ec.europa.eu/GeoportalProxyWebServices/resources/OGCCSW202"])
    json_result = cache.check_options()
    # give back json with download options
    print(json_result)
    sys.exit()
if sys.argv[2] == 'generateCache':
    output_filename = None
    output_folder = None
    if configuration['output_folder']:
        output_folder = str(configuration['output_folder'])
    if configuration['output_filename']:
        output_filename = str(configuration['output_filename']) #https://geoportal.saarland.de/gdi-sl/csw
    cache = SpatialDataCache(configuration['dataset_configuration'], json.dumps(configuration['area_of_interest']), ["https://gdk.gdi-de.org/gdi-de/srv/ger/csw", "https://vocabulary.geoportal.rlp.de/geonetwork/srv/ger/csw", "https://inspire-geoportal.ec.europa.eu/GeoportalProxyWebServices/resources/OGCCSW202"], output_filename=output_filename, output_folder=output_folder)
    print('start generate cache')
    cache.generate_cache()
    # send downloadlink via email
    send_mail(configuration['notification']['subject'], configuration['notification']['text'], configuration['notification']['email_address'])   
    sys.exit()



