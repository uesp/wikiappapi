<?php


use MediaWiki\MediaWikiServices;


class WikiAppApi extends ApiBase
{
	public $REMOVE_JSON_COMMENTS = true;
	
	
	public function __construct($mainModule, $moduleName, $modulePrefix = '') 
	{
		parent::__construct($mainModule, $moduleName, $modulePrefix);
	}
	
	
	public function outputError($msg)
	{
		$apiResult = $this->getResult();
		
		$apiResult->addValue( null, "error", $msg );
	}
	
	
	public function getProjectNamespace()
	{
		global $wgMetaNamespace;
		global $wgContLang;
		
		return $wgContLang->getFormattedNsText( NS_PROJECT );
		//return $wgMetaNamespace;
		
		//TODO: Fix to work in higher MW versions? More portable version?
		//return MediaWikiServices::getInstance()->getContentLanguage()->getFormattedNsText( NS_PROJECT );
	}
	
	
	public function getPageText($textTitle)
	{
			//TODO: fix/check for higher versions
		$title = Title::newFromText($textTitle);
		$page = WikiPage::factory($title);
		$content = $page->getContent(Revision::RAW);
		$text = ContentHandler::getContentText($content);
		
		//$pageFactory = MediaWikiServices::getInstance()->getWikiPageFactory();
		//$title = Title::newFromText("AppHomePage", 1);
		//$page = $pageFactory->newFromTitle($title);
		//$content = $page->getContent(RevisionRecord::RAW);
		//$text = ContentHandler::getContentText( $content );
		
		return $text;
	}
	
	
	public function removeJsonComments($text)
	{
		return preg_replace('~(" (?:\\\\. | [^"])*+ ") | // [^\v]*+ | /\* .*? \*/~xs', '$1', $text);
	}
	
	
	public function getFeaturedImageContent($widget)
	{
		$currentPage = $widget['current_page'];
		$historyPage = $widget['history_page'];
		$content = [];
		
		$currentFI = $this->getPageText($currentPage);
		
		if ($currentFI)
		{
			$isMatched = preg_match('#\[\[(File:.*)\]\]#', $currentFI, $matches);
			
			if ($isMatched)
			{
				$line = $matches[1];
				$line = preg_replace('/[|].*$/', '', $line);
				$file = str_replace("File:", "", $line);
				$imageUrl = '';
				
				$file = wfFindFile($file);
				$imageUrl = "";
				if ($file) $imageUrl = $file->getFullUrl();
				
				$content[] = [
					'imageURL' => $imageUrl,
					'imagePageURL' => $line,
					'caption' => 'TODO',
				];
				
				return $content[0];
			}
		}
		
		return [];
		
		//$projectNS = $this->getProjectNamespace();
		//$text = $this->getPageText("$projectNS:Featured Images/Old FIs");
		$text = $this->getPageText($historyPage);
		
		if ($text == null) return $content;	//TODO: Error or default value?
		
		$isMatched = preg_match('#<gallery>(.*)</gallery>#s', $text, $matches);	//TODO: Different FI page formats?
		if (!$isMatched) return $content;
		
		$gallery = $matches[1];
		$lines = explode("\n", $gallery);
		
		foreach ($lines as $line)
		{
			$line = trim($line);
			if ($line == "") continue;
			
			$line = preg_replace('/[|].*$/', '', $line);
			$file = str_replace("File:", "", $line);
			$imageUrl = '';
			
				//TODO: Work in more recent MW versions
			//MediaWikiServices::getInstance()->getRepoGroup()->findFile() in 1.38+
			$file = wfFindFile($file);
			$imageUrl = "";
			if ($file) $imageUrl = $file->getFullUrl();
			
			$content[] = [
				'imageURL' => $imageUrl,
				'imagePageURL' => $line,
			];
		}
		
		return $content;
	}
	
	
	public function replaceJsonContent(&$json)
	{
		$homepage = &$json['homepage'];
		
		foreach ($homepage as $i => &$widget)
		{
			$type = $widget['type'];
			
			switch ($type)
			{
				case "card_featured_image":
					$widget['content'] = $this->getFeaturedImageContent($widget);
					break;
			}
		}
		
		return $text;
	}
	
	
	public function execute()
	{
		$params = $this->extractRequestParams();
		$apiResult = $this->getResult();
		
		if ($params['version'] == null) return $this->outputError("Invalid request, manifest version required.");
		
		$version = intval($params['version']);
		if ($version <= 0) return $this->outputError("Invalid request, provided non-integer manifest version param.");
		
		//TODO: Check manifest version for valid value
		
		$projectNS = $this->getProjectNamespace();
		$text = $this->getPageText("$projectNS:AppHomePage");
		if ($text == null) return $this->outputError("Page data not available.");
		
		$text = str_replace('\n', "\n", $text);
		$text = str_replace('\"', '"', $text);
		$isMatched = preg_match('#<syntaxhighlight .*?>(.*)</syntaxhighlight>#s', $text, $matches);
		
		if (!$isMatched) return $this->outputError("Invalid page format.");
		
		$text = $matches[1];
		if ($this->REMOVE_JSON_COMMENTS) $text = $this->removeJsonComments($text);
		
		$json = json_decode($text, true);
		if ($json == null) return $this->outputError("Invalid page JSON format.");
		
		$this->replaceJsonContent($json);
		
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