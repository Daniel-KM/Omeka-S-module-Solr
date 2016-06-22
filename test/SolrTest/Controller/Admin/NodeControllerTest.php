<?php

namespace SolrTest\Controller\Admin;

require_once __DIR__ . '/../SolrControllerTestCase.php';

use SolrTest\Controller\SolrControllerTestCase;

class NodeControllerTest extends SolrControllerTestCase
{
    public function testBrowseAction()
    {
        $this->dispatch('/admin/solr');
        $this->assertResponseStatusCode(200);

        $this->assertXpathQueryContentRegex('//table//td[1]', '/default/');
    }

    public function testAddAction()
    {
        $this->dispatch('/admin/solr/node/add');
        $this->assertResponseStatusCode(200);

        $this->assertQuery('input[name="o:name"]');
        $this->assertQuery('input[name="o:settings[client][hostname]"]');
        $this->assertQuery('input[name="o:settings[client][port]"]');
        $this->assertQuery('input[name="o:settings[client][path]"]');
        $this->assertQuery('input[name="o:settings[resource_name_field]"]');

        $forms = $this->getServiceLocator()->get('FormElementManager');
        $form = $forms->get('Solr\Form\Admin\SolrNodeForm');
        $this->dispatch('/admin/solr/node/add', 'POST', [
            'o:name' => 'TestNode',
            'o:settings' => [
                'client' => [
                    'hostname' => 'example.com',
                    'port' => '8983',
                    'path' => 'solr/test_node',
                ],
                'resource_name_field' => 'resource_name_s',
            ],
            'csrf' => $form->get('csrf')->getValue(),
        ]);
        $this->assertRedirectTo('/admin/solr');
    }
}
