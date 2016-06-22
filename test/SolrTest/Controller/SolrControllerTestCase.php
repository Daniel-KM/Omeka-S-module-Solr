<?php

namespace SolrTest\Controller;

use Omeka\Test\AbstractHttpControllerTestCase;

abstract class SolrControllerTestCase extends AbstractHttpControllerTestCase
{
    protected $searchIndex;
    protected $searchPage;

    public function setUp()
    {
        parent::setUp();

        $this->loginAsAdmin();

        $response = $this->api()->create('search_indexes', [
            'o:name' => 'TestIndex',
            'o:adapter' => 'solr',
            'o:settings' => [
                'resources' => [
                    'items',
                    'item_sets',
                ],
            ],
        ]);
        $searchIndex = $response->getContent();
        $response = $this->api()->create('search_pages', [
            'o:name' => 'TestPage',
            'o:path' => 'test/search',
            'o:index_id' => $searchIndex->id(),
            'o:form' => 'basic',
            'o:settings' => [
                'facets' => [],
                'sort_fields' => [],
            ],
        ]);
        $searchPage = $response->getContent();

        $this->searchIndex = $searchIndex;
        $this->searchPage = $searchPage;
    }

    public function tearDown()
    {
        $this->api()->delete('search_pages', $this->searchPage->id());
        $this->api()->delete('search_indexes', $this->searchIndex->id());
    }

    protected function loginAsAdmin()
    {
        $application = $this->getApplication();
        $serviceLocator = $application->getServiceManager();
        $auth = $serviceLocator->get('Omeka\AuthenticationService');
        $adapter = $auth->getAdapter();
        $adapter->setIdentity('admin@example.com');
        $adapter->setCredential('root');
        $auth->authenticate();
    }

    protected function getServiceLocator()
    {
        return $this->getApplication()->getServiceManager();
    }

    protected function api()
    {
        return $this->getServiceLocator()->get('Omeka\ApiManager');
    }

    protected function resetApplication()
    {
        $this->application = null;
    }
}
