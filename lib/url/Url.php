<?php

namespace url;

/**
 * Support for url construction, mutation, and output depending on content type.
 * @link https://github.com/thesmart/php-atwood
 */
class Url {

	public static $DEFAULT_SCHEME = 'http';
	public static $DEFAULT_HOST = array('example', 'com');

	/**
	 * The url scheme
	 * @var string
	 */
	protected $scheme;

	/**
	 * all the domain parts that make up this Url
	 * @var array
	 */
	protected $host;

	/**
	 * The port number
	 * @var int
	 */
	protected $port = null;

	/**
	 * all the path parts that make up this Url
	 * @var array
	 */
	protected $path = array();

	/**
	 * Whether to use a trailing slash
	 * @var bool
	 */
	protected $trailingSlash	= true;

	/**
	 * Associative array of query key/value pairs
	 * @var array
	 */
	protected $query = array();

	/**
	 * The fragment identifier
	 * @var string|null
	 */
	protected $fragment = null;

	public function __construct($url = null) {
		$this->scheme	= Url::$DEFAULT_SCHEME;
		$this->host	= Url::$DEFAULT_HOST;

		if (is_string($url)) {
			$urlParts = parse_url($url);
			$this->setScheme(isset($urlParts['scheme']) ? $urlParts['scheme'] : self::$DEFAULT_SCHEME);
			$this->setHost(isset($urlParts['host']) ? $urlParts['host'] : self::$DEFAULT_HOST);
			$this->setPort(isset($urlParts['port']) ? $urlParts['port'] : null);
			$this->setPath(isset($urlParts['path']) ? $urlParts['path'] : '');
			$this->setQuery(isset($urlParts['query']) ? $urlParts['query'] : null);
			$this->setFragment(isset($urlParts['fragment']) ? $urlParts['fragment'] : null);
		}
	}

	/**
	 * Enforce deep-clone
	 * @return Url
	 */
	public function __clone() {
		return new Url($this->__toString());
	}

	/**
	 * The the scheme used in this Url
	 * e.g. 'http', 'ftp', 'etc'
	 *
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}

	/**
	 * get or set the url scheme (aka: protocol)
	 * e.g. 'http', 'ftp', 'etc'
	 *
	 * @param string $scheme     The scheme to set
	 * @return Url	$this
	 */
	public function setScheme($scheme) {
		$this->scheme  = str_replace('://', '', $scheme);
		if ($this->scheme == 'https') {
			$this->setPort(443);
		} else if ($this->scheme == 'http') {
			$this->setPort(80);
		}

		return $this;
	}

	/**
	 * Is this url an HTTPS url?
	 * @return bool
	 */
	public function isHttps() {
		return $this->scheme === 'https';
	}

	/**
	 * Set or get the domain, as an array.
	 * e.g. sub.example.com would be array('sub', 'example', 'com')
	 *
	 * @param array|string $domain     A domain or set of (sub)domains
	 * @return Url	$this
	 */
	public function setHost($domain) {
		if (is_array($domain)) {
			$this->host  = $domain;
		} else if (is_string($domain)) {
			$this->host  = explode('.', trim($domain, '.'));
		}

		return $this;
	}

	/**
	 * Get this Url's Host
	 * NOTE: URL-safe
	 *
	 * @param int $level Optional. Specify which domain level to return. -1 will return TLD.
	 * @return string|null
	 */
	public function getHost($level = null) {
		if (is_null($level)) {
			$hostStr = array();
			foreach ($this->host as $piece) {
				$hostStr[]	= rawurlencode($piece);
			}
			return implode('.', $hostStr);
		} else if ($level >= count($this->host)) {
			// overflow
			return null;
		} else if ($level < 0) {
			$level = $level + count($this->host);
			if ($level < 0) {
				// underflow
				return null;
			}
			return rawurlencode($this->host[$level]);
		} else {
			return rawurlencode($this->host[$level]);
		}
	}

	/**
	 * Get the top-level-domain.
	 * e.g. "com" or "net" or "org", etc.
	 * NOTE: URL-safe
	 *
	 * @return string
	 */
	public function getTld() {
		return $this->getHost(-1);
	}

	/**
	 * Set port value
	 *
	 * @param int|null $port		A value between 0 and 65536 inclusive
	 * @return Url	$this
	 */
	public function setPort($port) {
		if (is_null($port)) {
			$this->port	= null;
		} else {
			$this->port = intval($port);
			if ($this->port <= 0 || $this->port > 65536) {
				$this->port = null;
			}
		}

		return $this;
	}

	/**
	 * Get the port value.
	 * @return int|null
	 */
	public function getPort() {
		if (is_null($this->port)) {
			if ($this->scheme === 'http') {
				return 80;
			} else if ($this->scheme === 'https') {
				return 443;
			}
		}

		return $this->port;
	}

	/**
	 * Set the path
	 * e.g. '/api/User/view' or array('api', 'User', 'view')
	 * e.g. '/api/User/views/' or array('api', 'User', 'views/')
	 *
	 * @param array|string $path       The path to set.
	 * @return Url	$this
	 */
	public function setPath($path) {
		if (empty($path)) {
			$this->trailingSlash = false;
			$this->path = array();
			return $this;
		}

		// do we have a trailing slash?
		$this->trailingSlash = mb_substr($path, -1, 1) === '/';

		// convert to array
		$this->path = self::stringToPath($path);

		return $this;
	}

	/**
	 * Get the path
	 * NOTE: URL-safe
	 *
	 * @return string    The path
	 */
	public function getPath() {
		return self::pathToString($this->path, $this->trailingSlash);
	}

	/**
	 * Set the query
	 * e.g. the part of the url after the '?' but before the '#' or end-of-string
	 *
	 * @param array|string|null $query  The query to set.
	 * @return Url	$this
	 */
	public function setQuery($query) {
		if (is_string($query)) {
			$this->query	= self::strToQuery($query);
		} else if (is_array($query)) {
			$this->query	= $query;
		} else if (is_null($query)) {
			$this->query	= array();
		}

		ksort($this->query);

		return $this;
	}

	/**
	 * Get the query portion of this url
	 * NOTE: NOT URL-safe, URL-dangerous
	 *
	 * @return array
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * Get the query portion of this url
	 * NOTE: URL-safe
	 *
	 * @return string
	 */
	public function getQueryStr() {
		return self::queryToStr($this->query, true);
	}

	/**
	 * Does this url have a queyr string?
	 * @return bool
	 */
	public function hasQuery() {
		return !empty($this->query);
	}

	/**
	 * Set the fragment
	 * e.g. the part of the url after the '#'
	 *
	 * @param array|string|null $fragment  The fragment to set. If an array, will create a key-value fragment.
	 * @return Url	$this
	 */
	public function setFragment($fragment) {
		if (is_array($fragment)) {
			$this->fragment = self::queryToStr($fragment, true);
		} else if (is_null($fragment)) {
			$this->fragment = null;
		} else {
			$this->fragment = (string)$fragment;
		}

		return $this;
	}

	/**
	 * Get the fragment
	 * NOTE: URL-safe, if used as a fragment
	 *
	 * e.g. the part of the url after the '#'
	 * @return string
	 */
	public function getFragment() {
		return $this->fragment ? $this->fragment : '';
	}

	/**
	 * Assume the fragment is a query string, and return it as an associative array
	 * e.g. the part of the url after the '#'
	 * NOTE: URL-safe
	 *
	 * @return array
	 */
	public function getFragmentQuery() {
		return $this->fragment ? self::strToQuery($this->fragment, true) : array();
	}

	/**
	 * Does this url have a fragment?
	 * @return bool
	 */
	public function hasFragment() {
		return !is_null($this->fragment);
	}

	/**
	 * Get the absolute string representation of the Url object.
	 * e.g. 'https://www.example.com/?foo=bar#foobar'
	 * 
	 * @return string
	 */
	public function __toString() {
		$url = array(
			'scheme' => $this->scheme,
			'schemepart' => '://',
			'host' => $this->getHost()
		);

		if ($this->port) {
			$url['port']	= ':' . $this->getPort();
		}

		$url['path']	= $this->getPath();

		if ($this->hasQuery()) {
			$url['query']	= '?' . $this->getQueryStr();
		}

		if ($this->hasFragment()) {
			$url['fragment']	= '#' . $this->getFragment();
		}

		return implode('', $url);
	}

	/**
	 * Turns associative array into a query string
	 * @param array $query
	 * @param bool $sort		Set true to sort the query string by key
	 * @return string
	 */
	public static function queryToStr(array $query, $sort = false) {
		$queryStr = array();
		foreach ($query as $key => $val) {
			$key	= rawurlencode($key);
			$val	= rawurlencode($val);
			$queryStr[$key] = $key . '=' . $val;
		} unset($val);

		if ($sort) {
			ksort($queryStr);
		}

		return implode('&', $queryStr);
	}

	/**
	 * Turns a query string into an associative array
	 * @param string $queryStr
	 * @param bool $sort		Set true to sort the query string by key
	 * @return array
	 */
	public static function strToQuery($queryStr, $sort = false) {
		$queryStr = explode('&', $queryStr);

		$query = array();
		foreach ($queryStr as $kvSet) {
			$kvSet = explode('=', $kvSet);

			$key = rawurldecode($kvSet[0]);
			if (isset($kvSet[1])) {
				$query[$key] = rawurldecode($kvSet[1]);
			} else {
				$query[$key] = null;
			}
		}

		if ($sort) {
			ksort($query);
		}

		return $query;
	}

	/**
	 * Turn a path array into a string
	 * @param array $path
	 * @param bool $useTrailingSlash		Optional. Set false to remove trailing slash from result
	 * @return string
	 * @static
	 */
	public static function pathToString(array $path, $useTrailingSlash = true) {
		$pieces		= array('');
		foreach ($path as $piece) {
			$pieces[]	= rawurlencode($piece);
		}

		$path		= implode('/', $pieces);
		if ($useTrailingSlash) {
			$path	.= '/';
		}
		return  $path;
	}

	/**
	 * Turn a path string into an array
	 * @param string $path
	 * @return array
	 * @static
	 */
	public static function stringToPath($path) {
		$path = explode('/', $path);
		$tmpPath = array();
		foreach ($path as $piece) {
			if ($piece === '.' || $piece === '') {
				// ignore
				continue;
			} else if ($piece === '..') {
				if (count($tmpPath)) {
					// sub-folder
					array_pop($tmpPath);
				} else {
					// ignore
					continue;
				}
			} else {
				$tmpPath[] = rawurldecode($piece);
			}
		}
		return $tmpPath;
	}
}