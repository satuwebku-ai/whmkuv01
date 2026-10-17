<?php

namespace App\Lib;

use Illuminate\Support\Facades\Http;

class ClientInfo{

    /**
    * Get requestor IP information
    *
    * @return array
    */
	public static function ipInfo()
	{
	    $ip = (string) getRealIP();
        $country = $city = $area = $code = $long = $lat = null;

        // Do not send invalid, private, or reserved IP addresses to a third-party geolocation service.
        $validPublicIp = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        if ($validPublicIp) {
            try {
                $response = Http::accept('application/xml')
                    ->connectTimeout(1)
                    ->timeout(2)
                    ->get('https://www.geoplugin.net/xml.gp', ['ip' => $ip]);

                if ($response->successful()) {
                    $previous = libxml_use_internal_errors(true);
                    $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
                    libxml_clear_errors();
                    libxml_use_internal_errors($previous);
                    if ($xml !== false) {
                        $country = (string) ($xml->geoplugin_countryName ?? '');
                        $city = (string) ($xml->geoplugin_city ?? '');
                        $area = (string) ($xml->geoplugin_areaCode ?? '');
                        $code = (string) ($xml->geoplugin_countryCode ?? '');
                        $long = (string) ($xml->geoplugin_longitude ?? '');
                        $lat = (string) ($xml->geoplugin_latitude ?? '');
                    }
                }
            } catch (\Throwable $e) {
                // Geolocation is optional; continue logging without location if the service fails.
            }
        }

        return [
            'country' => $country,
            'city' => $city,
            'area' => $area,
            'code' => $code,
            'long' => $long,
            'lat' => $lat,
            'ip' => $ip,
            'time' => date('Y-m-d h:i:s A'),
        ];
	}

    /**
    * Get requestor operating system information
    *
    * @return array
    */
	public static function osBrowser(){
	    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
	    $osPlatform = "Unknown OS Platform";
	    $osArray = array(
	        '/windows nt 10/i' => 'Windows 10',
	        '/windows nt 6.3/i' => 'Windows 8.1',
	        '/windows nt 6.2/i' => 'Windows 8',
	        '/windows nt 6.1/i' => 'Windows 7',
	        '/windows nt 6.0/i' => 'Windows Vista',
	        '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
	        '/windows nt 5.1/i' => 'Windows XP',
	        '/windows xp/i' => 'Windows XP',
	        '/windows nt 5.0/i' => 'Windows 2000',
	        '/windows me/i' => 'Windows ME',
	        '/win98/i' => 'Windows 98',
	        '/win95/i' => 'Windows 95',
	        '/win16/i' => 'Windows 3.11',
	        '/macintosh|mac os x/i' => 'Mac OS X',
	        '/mac_powerpc/i' => 'Mac OS 9',
	        '/linux/i' => 'Linux',
	        '/ubuntu/i' => 'Ubuntu',
	        '/iphone/i' => 'iPhone',
	        '/ipod/i' => 'iPod',
	        '/ipad/i' => 'iPad',
	        '/android/i' => 'Android',
	        '/blackberry/i' => 'BlackBerry',
	        '/webos/i' => 'Mobile'
	    );
	    foreach ($osArray as $regex => $value) {
	        if (preg_match($regex, $userAgent)) {
	            $osPlatform = $value;
	        }
	    }
	    $browser = "Unknown Browser";
	    $browserArray = array(
	        '/msie/i' => 'Internet Explorer',
	        '/firefox/i' => 'Firefox',
	        '/safari/i' => 'Safari',
	        '/chrome/i' => 'Chrome',
	        '/edge/i' => 'Edge',
	        '/opera/i' => 'Opera',
	        '/netscape/i' => 'Netscape',
	        '/maxthon/i' => 'Maxthon',
	        '/konqueror/i' => 'Konqueror',
	        '/mobile/i' => 'Handheld Browser'
	    );
	    foreach ($browserArray as $regex => $value) {
	        if (preg_match($regex, $userAgent)) {
	            $browser = $value;
	        }
	    }

	    $data['os_platform'] = $osPlatform;
	    $data['browser'] = $browser;

	    return $data;
	}

}
