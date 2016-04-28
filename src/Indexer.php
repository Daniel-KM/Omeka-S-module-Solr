<?php

/*
 * Copyright BibLibre, 2016
 *
 * This software is governed by the CeCILL license under French law and abiding
 * by the rules of distribution of free software.  You can use, modify and/ or
 * redistribute the software under the terms of the CeCILL license as circulated
 * by CEA, CNRS and INRIA at the following URL "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and rights to copy, modify
 * and redistribute granted by the license, users are provided only with a
 * limited warranty and the software's author, the holder of the economic
 * rights, and the successive licensors have only limited liability.
 *
 * In this respect, the user's attention is drawn to the risks associated with
 * loading, using, modifying and/or developing or reproducing the software by
 * the user in light of its specific status of free software, that may mean that
 * it is complicated to manipulate, and that also therefore means that it is
 * reserved for developers and experienced professionals having in-depth
 * computer knowledge. Users are therefore encouraged to load and test the
 * software's suitability as regards their requirements in conditions enabling
 * the security of their systems and/or data to be ensured and, more generally,
 * to use and operate it in the same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
 */

namespace Solr;

use SolrClient;
use SolrInputDocument;
use Omeka\Api\Representation\ItemRepresentation;
use Search\Indexer\AbstractIndexer;

class Indexer extends AbstractIndexer
{
    protected $client;

    public function clearIndex()
    {
        $client = $this->getClient();
        $client->deleteByQuery('*:*');
        $client->commit();
    }

    public function indexItem(ItemRepresentation $item)
    {
        $client = $this->getClient();

        $document = new SolrInputDocument;
        $document->addField('id', $item->id());
        foreach ($item->values() as $term => $v) {
            $fieldName = preg_replace('/[^a-zA-Z_]/', '_', $term) . '_t';
            foreach ($v['values'] as $value) {
                $type = $value->type();
                if ($type == 'literal' || $type == 'uri') {
                    $document->addField($fieldName, $value);
                }
            }
        }
        $this->getLogger()->info('Indexing item ' . $item->id());

        $client->addDocument($document);
        $client->commit();
    }

    protected function getClient()
    {
        if (!isset($this->client)) {
            $this->client = new SolrClient([
                'hostname' => $this->getSetting('hostname'),
                'port' => $this->getSetting('port'),
                'path' => $this->getSetting('path'),
            ]);
        }

        return $this->client;
    }
}
