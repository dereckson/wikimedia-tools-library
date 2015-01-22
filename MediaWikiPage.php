<?php

/**
 * Class MediaWikiPage
 *
 * Represents a MediaWiki page
 */
class MediaWikiPage {
	///
	/// Core properties and constructor
	///

	/**
	 * The project the page is stored (e.g. enwiki)
	 *
	 * @var string
	 */
	private $project;

	/**
	 * The page's title
	 *
	 * @var string
	 */
	private $title;

	/**
	 * The page's namespace
	 *
	 * @var int
	 */
	private $namespace;

	/**
	 * Initializes a new instance of the MediaWikiPage class
	 *
	 * @param string $project The page's project (e.g. enwiki)
	 * @param string $title The page's title
	 * @param int $namespace The page's namespace, as a numeric identifier [facultative]
	 */
	public function __construct ($project, $title, $namespace = 0) {
		$this->project = $project;
		$this->title = $title;
		$this->namespace = $namespace;
	}

	///
	/// Methods using only core properties
	///

	/**
	 * The page identifier
	 *
	 * @return int|null
	 */
	function getId () {
		$title = static::getNormalizedTitleForPage($this->title);

		$sql = "SELECT page_id FROM page WHERE page_title = '$title' AND page_namespace = '$this->namespace'";
		$row = ReplicationDatabaseFactory::get($this->project . 'wiki')
			->query($sql)
			->fetch_array();

		if ($row) {
			return $row['page_id'];
		}
		return null;
	}

	/**
	 * Gets normalized title
	 *
	 * @return string the normalized title
	 */
	function getNormalizedTitle () {
		return static::getNormalizedTitleForPage($this->title);
	}

	/**
	 * Gets display title
	 *
	 * @return string the display title
	 */
	function getDisplayTitle () {
		return static::getDisplayTitleForPage($this->title);
	}

	/**
	 * Gets full display title
	 *
	 * @return string the full display title
	 */
	function getFullDisplayTitle () {
		$title = "";
		if ($this->namespace != 0) {
			$namespaces = MediaWikiProject::getWikimediaProject($this->project)->getNamespaces();
			dprint_r($this->namespace);
			dprint_r($namespaces);
			die;
		}
		$title .= $this->getDisplayTitle();
		return $title;
	}

	/**
	 * Resolves a redirect
	 *
	 * @return int The page id of the redirect target
	 */
	public function resolveRedirect () {
		return self::resolveRedirectFromPageId($this->project, $this->getId());
	}

	/**
	 * Resolves a redirect
	 *
	 * @param string $project The project the page is stored (e.g. enwiki)
	 * @param int $pageId The page id of the redirect source
	 * @return int The page id of the redirect target
	 * @throws InvalidArgumentException if the page id syntax isn't valid
	 */
	public static function resolveRedirectFromPageId ($project, $pageId) {
		//Ensures $page_id is numeric
		if (!preg_match('/^[0-9][0-9]*$/', $pageId)) {
			throw new InvalidArgumentException("$pageId isn't a valid page id (an integer is expected).");
		}

		$sql = "SELECT rd_title FROM redirect WHERE rd_from = $pageId";
		$row = ReplicationDatabaseFactory::get($project)
			->query($sql)
			->fetch_array();
		return $row['rd_title'];
	}

	///
	/// Static helper methods
	///

	/**
	 * Gets the normalized title of a page
	 *
	 * @param string $title The page title
	 * @return string The normalized page title
	 */
	public static function getNormalizedTitleForPage ($title) {
		return str_replace(' ', '_', $title);
	}

	/**
	 * Gets the display title of a page
	 *
	 * @param string $title The page title
	 * @return string The page title to display
	 */
	public static function getDisplayTitleForPage ($title) {
		return str_replace('_', ' ', $title);
	}

	/**
	 * Determines if an identifier is a valid namespace identifier for a page
	 *
	 * @param int $namespaceId The namespace identifier
	 * @return bool true if the id is a valid namespace for a page; otherwise, false.
	 */
	public static function isValidNamespaceIdentifier ($namespaceId) {
		//Must be a positive integer or zero (we don't allow to use negative namespaces for pages).
		return is_numeric($namespaceId) && is_int((int)$namespaceId) && $namespaceId >= 0;
	}
}
