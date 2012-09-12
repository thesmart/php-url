<?php

namespace test;

require_once __DIR__ . "/../vendor/autoload.php";

use url\Url;

class UrlTest extends \PHPUnit_Framework_TestCase {

	public function testConstructor() {
		$url = new Url();
		$this->assertEquals('http://example.com/', (string)$url);
		$this->assertFalse($url->isHttps());

		$url = new Url('http://example.com/');
		$this->assertEquals('http://example.com/', (string)$url);

		$url = new Url('http://example.com');
		$this->assertEquals('http://example.com', (string)$url);

		$url = new Url('http://test.com');
		$this->assertEquals('http://test.com', (string)$url);

		$url = new Url('https://test.com');
		$this->assertEquals('https://test.com', (string)$url);
		$this->assertTrue($url->isHttps());
		$this->assertEquals('https', $url->getScheme());
		$this->assertEquals('test.com', $url->getHost());
		$this->assertEquals('com', $url->getTld());
		$this->assertEquals(443, $url->getPort());
		$this->assertEquals('', $url->getPath());
		$this->assertEquals(array(), $url->getQuery());
		$this->assertEquals('', $url->getQueryStr());
		$this->assertFalse($url->hasFragment());
		$this->assertEquals('', $url->getFragment());
		$this->assertEquals(array(), $url->getFragmentQuery());

		$url = new Url('ftp://www.google.com:8080/foobar/?b=2&a=1#easyman');
		$this->assertEquals('ftp://www.google.com:8080/foobar/?a=1&b=2#easyman', (string)$url);
		$this->assertFalse($url->isHttps());
		$this->assertEquals('ftp', $url->getScheme());
		$this->assertEquals('www.google.com', $url->getHost());
		$this->assertEquals('com', $url->getTld());
		$this->assertEquals(8080, $url->getPort());
		$this->assertEquals('/foobar/', $url->getPath());
		$this->assertEquals(array('a'=>1,'b'=>2), $url->getQuery());
		$this->assertEquals('a=1&b=2', $url->getQueryStr());
		$this->assertTrue($url->hasFragment());
		$this->assertEquals('easyman', $url->getFragment());
		$this->assertEquals(array('easyman' => null), $url->getFragmentQuery());
	}

	public function testSetters() {
		$url = new Url('ftp://www.google.com:8080/foobar?b=2&a=1#easyman');
		$this->assertEquals('ftp://www.google.com:8080/foobar?a=1&b=2#easyman', (string)$url);
		$this->assertFalse($url->isHttps());
		$this->assertEquals('ftp', $url->getScheme());
		$this->assertEquals('www.google.com', $url->getHost());
		$this->assertEquals('com', $url->getTld());
		$this->assertEquals(8080, $url->getPort());
		$this->assertEquals('/foobar', $url->getPath());
		$this->assertEquals(array('a'=>1,'b'=>2), $url->getQuery());
		$this->assertEquals('a=1&b=2', $url->getQueryStr());
		$this->assertTrue($url->hasFragment());
		$this->assertEquals('easyman', $url->getFragment());
		$this->assertEquals(array('easyman' => null), $url->getFragmentQuery());

		// modify
		$url->setScheme('https://')->setHost('reddit.com')->setPort(2001)->setPath('')->setQuery(array())->setFragment(null);
		$this->assertEquals('https://reddit.com:2001', (string)$url);
		$url->setPort(443);
		$this->assertEquals('https://reddit.com:443', (string)$url);
		$url->setPort(null);
		$this->assertEquals('https://reddit.com', (string)$url);
		$url->setQuery(array('x'=>1,'y'=>2,'a'=>3));
		$this->assertEquals('https://reddit.com?a=3&x=1&y=2', (string)$url);
	}
}