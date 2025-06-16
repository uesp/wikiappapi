<?php


class WikiAppContentApi extends ApiBase
{
	
	public function __construct($mainModule, $moduleName, $modulePrefix = '') 
	{
		parent::__construct($mainModule, $moduleName, $modulePrefix);
	}
	
	
	public function execute()
	{
		$this->makeParseCall();
		$this->makeQueryCall();
		$this->makeMobileViewCall();
	}
	
	
	public function makeParseCall()
	{
		$params = $this->extractRequestParams();
		$apiResult = $this->getResult();
		
		$page = $params['page'];
		$pageId = $params['pageid'];
		$revId = $params['revid'];
		$redirect = $params['redirect'];
		$disabletoc = $params['disabletoc'];
		$mobileFormat = $params['mobileformat'];
		
		$reqParams = [];
		$reqParams['action'] = "parse";
		$reqParams['prop'] = "text|langlinks|sections|revid";
		if ($page) $reqParams['page'] = $page;
		if ($pageId) $reqParams['pageid'] = $pageId;
		if ($revId) $reqParams['revid'] = $revId;
		if ($redirect) $reqParams['redirect'] = $redirect;
		if ($disabletoc) $reqParams['disabletoc'] = $disabletoc;
		if ($mobileFormat) $reqParams['mobileformat'] = $mobileFormat;
		$req = new FauxRequest( $reqParams );
		
		$api = new ApiMain( $req );
		$api->execute();
		
		$data = $api->getResult()->getResultData();
		$apiResult->addValue( null, "parse", $data['parse'] );
	}
	
	
	public function makeMobileViewCall()
	{
		$params = $this->extractRequestParams();
		$apiResult = $this->getResult();
		
		$page = $params['page'];
		$pageId = $params['pageid'];
		$revId = $params['revid'];
		$redirect = $params['redirect'];
		$mobileFormat = $params['mobileformat'];
		
		$reqParams = [];
		$reqParams['action'] = "mobileview";
		$reqParams['sections'] = "all";
		if ($page) $reqParams['page'] = $page;
		if ($pageId) $reqParams['pageid'] = $pageId;
		if ($revId) $reqParams['revid'] = $revId;
		if ($redirect) $reqParams['redirect'] = $redirect;
		if ($mobileFormat) $reqParams['mobileformat'] = $mobileFormat;
		$reqParams['prop'] = "text|normalizedtitle|lastmodified|lastmodifiedby|protection|editable|languagecount|hasvariants";
		$req = new FauxRequest( $reqParams );
		
		$api = new ApiMain( $req );
		$api->execute();
		
		$data = $api->getResult()->getResultData();
		$apiResult->addValue( null, "mobileview", $data['mobileview'] );
	}
	
	
	public function makeQueryCall()
	{
		$params = $this->extractRequestParams();
		$apiResult = $this->getResult();
		
		$page = $params['page'];
		$pageId = $params['pageid'];
		$revId = $params['revid'];
		$redirect = $params['redirect'];
		
		$reqParams = [];
		$reqParams['action'] = "query";
		$reqParams['prop'] = "categories|images|info|pageimages";
		if ($page) $reqParams['titles'] = $page;
		if ($pageId) $reqParams['pageids'] = $pageId;
		if ($revId) $reqParams['revids'] = $revId;
		$reqParams['cllimit'] = "max";
		$reqParams['imlimit'] = "max";
		$reqParams['inprop'] = "url";
		$reqParams['intestactions'] = "read";
		$reqParams['piprop'] = "thumbnail|name|original";
		if ($redirect) $reqParams['redirect'] = $redirect;
		$req = new FauxRequest( $reqParams );
		
		$api = new ApiMain( $req );
		$api->execute();
		
		$data = $api->getResult()->getResultData();
		$apiResult->addValue( null, "query", $data['query'] );
	}
	
	
	protected function getAllowedParams() {
		return [
				'version' => [
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_REQUIRED => false,
				],
				'page' => [
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_REQUIRED => false,
				],
				'pageid' => [
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_REQUIRED => false,
				],
				'revid' => [
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_REQUIRED => false,
				],
				'redirect' => [
					ApiBase::PARAM_TYPE => 'boolean',
					ApiBase::PARAM_REQUIRED => false,
				],
				'disabletoc' => [
					ApiBase::PARAM_TYPE => 'boolean',
					ApiBase::PARAM_REQUIRED => false,
				],
				'mobileformat' => [
					ApiBase::PARAM_TYPE => 'boolean',
					ApiBase::PARAM_REQUIRED => false,
				],
		];
	}
};