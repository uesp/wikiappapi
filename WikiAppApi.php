<?php


use MediaWiki\MediaWikiServices;


class WikiAppApi extends ApiBase
{
	
	public function __construct($mainModule, $moduleName, $modulePrefix = '') 
	{
		parent::__construct($mainModule, $moduleName, $modulePrefix);
	}
	
	
	public function outputError($msg)
	{
		$apiResult = $this->getResult();
		
		$apiResult->addValue( null, "error", $msg );
	}
	
	
	public function execute()
	{
		//$pageFactory = MediaWikiServices::getInstance()->getWikiPageFactory();
		//$title = Title::newFromText("AppHomePage", 1);
		//$page = $pageFactory->newFromTitle($title);
		//$content = $page->getContent(RevisionRecord::RAW);
		//$text = ContentHandler::getContentText( $content );
		
		$params = $this->extractRequestParams();
		$apiResult = $this->getResult();
		
		//$this->requireOnlyOneParameter($params, "version");
		
		if ($params['version'] == null) return $this->outputError("Invalid request, manifest version required.");
		
		$version = intval($params['version']);
		if ($version <= 0) return $this->outputError("Invalid request, provided non-integer manifest version param.");
		
		//TODO: Check manifest version for valid value
		
			//TODO: fix/check for higher versions
		$title = Title::newFromText("UESPWiki:AppHomePage");
		$page = WikiPage::factory($title);
		$content = $page->getContent(Revision::RAW);
		$text = ContentHandler::getContentText( $content );
		
		if ($text == null) return $this->outputError("Page data not available.");
		
		$text = str_replace('\n', "\n", $text);
		$text = str_replace('\"', '"', $text);
		$isMatched = preg_match('#<syntaxhighlight .*?>(.*)</syntaxhighlight>#s', $text, $matches);
		
		$json = [];
		
		if ($isMatched)
		{
			$text = $matches[1];
			$text = preg_replace('~(" (?:\\\\. | [^"])*+ ") | // [^\v]*+ | /\* .*? \*/~xs', '$1', $text);
			
			//error_log("JSON: $text");
			
			$json = json_decode($text, true);
			if ($json == null) return $this->outputError("Invalid page JSON format."); 
		}
		
		//$r = [ 'stuff' => 1234, 'version' => $version, "url" => $title->getFullURL(), 'jsonLastError' => json_last_error_msg(), "text" => $text ];
		//$apiResult->addValue( null, "test", $r );
		$apiResult->addValue( null, "apphomepage", $json );
	}
	
	
	protected function getAllowedParams() {
		return [
				'version' => [
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_REQUIRED => false,
				],
		];
	}
	
	
};