<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * URL helper class.
 *
 * $Id: U_url.php 1579 2012-08-13 06:54:36Z Tianium $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class url extends url_Core {

    /**
     * Fetches an absolute site URL based on a URI segment.
     *
     * @param string uri Site URI to convert
     * @param string protocol non-default protocol
     * @param array inherited_queries Queries to inherited from current request. Passing NULL to auto detect.
     *
     * @return string
     */
    public static function site($uri = '', $protocol = FALSE, $inherited_queries = NULL)
    {
        // Normalize $uri by remove possible domain prefix.
        $site_domain = (string) Kohana::config('core.site_domain', TRUE);
        if (strpos($uri, $site_domain) === 0) {
            $uri = substr($uri, strlen($site_domain) - 1);
        }

        // Is there any queries should be inherited by new url
        if ($inherited_queries === NULL) { // auto detect.
            // Use event to query for possible group name.
            Event::run('url::site.inherited_queries', $inherited_queries);
        }
        if (!empty($inherited_queries)) {
            $inherited = array();
            $queries = Input::instance()->get(); // only get queries can be inherited.
            foreach ($inherited_queries as $key) {
                if (array_key_exists($key, $queries)) {
                    $inherited[$key] = $queries[$key];
                }
            }

            if (!empty($inherited)) {
                $uri = self::add_queries($uri, $queries);
            }
        }

        return url_Core::site($uri, $protocol);
    }
    
    public static function home()
	{
		return config::item('config.url_home', false, '/');
	}

    /**
    * Subsite url formatter. This function will ensure the validity for both url in main site and subsite.
    * eg. [surfix]request_path in main site = request_path in subsite.
    *
    * @param string surfix
    * @param string url
    *
    * @return string auto detected url;
    */
    public static function subsite($surfix, $uri) {
        $request_uri = URI::instance()->string();

        $surfix = preg_replace('/^(.*?)\/?$/', '\1', $surfix);
        if (strpos($uri, $surfix) === 0) {
            $uri = substr($uri, strlen($surfix));
        }

        $uri = preg_replace('/^\/?(.*)$/', '\1', $uri);
        if (strpos($request_uri, $surfix) === 0) {
            return url::site("$surfix/$uri");
        }
        else {
            return url::site($uri);
        }
    }

    /**
     * Get trusted url, which is specified by $config, untrusted url will be
     * converted to homepage and return.
     */
    public static function trusted($url = '', $protocol = FALSE, $config = null)
    {
        $origin = null;
        if (!self::is_trusted($url, $origin, $config)) {
            return self::site('', $protocol);
        }
        else if (is_null($origin)) {
            return self::site($url, $protocol);
        }
        else {
            return $url;
        }
    }

    /**
     * Is specified uri trusted according to service.allow_domains settings.
     * By default, uris without domain is trusted as is site url.
     *
     * @param string $url Url to be tested.
     * @param string $origin Trusted scheme://host to be further use, null if $url is url of current website.
     */
    public static function is_trusted($url, &$origin = null, $config = null)
    {
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }
        if (!isset($parts['host']) || $parts['host'] == $_SERVER['HTTP_HOST']) {
            return true;
        }

        $scheme = isset($parts['scheme']) ? $parts['scheme'] : "http";
        $origin = $parts['scheme']."://".$parts['host'];
        return in_array($origin, !is_null($config) ? $config :
            config::item('service.allow_domains', false, array()));
    }

    public static function add_queries($url, $queries) {
        return self::build($url, array('query' => $queries));
    }

    public static function build($url, $parts) {
        // TODO: HTTP Module http_build_url function compatibility.
        if (!preg_match('/^(.+?)\/*(?:\?([^#]*))?(#.*)?$/', $url, $matches)) {
            return;
        }

        $path = $matches[1];
        if (isset($parts['path'])) {
            $path .= '/'.$parts['path'];
        }

        $query = isset($matches[2]) ? $matches[2] : "";
        if (isset($parts['query'])) {
            parse_str($query, $old_query);
            $new_query = is_array($parts['query']) ? $parts['query'] : parse_str($parts['query']);
            $query = http_build_query(array_merge($old_query, $new_query));
        }
        $query = empty($query) ? "" : "?$query";

        $fragment = isset($matches[3]) ? $matches[3] : "";
        if (isset($parts['fragment'])) {
            $fragment = '#'.$parts['fragment'];
        }

        return $path.$query.$fragment;
    }
} // End url
