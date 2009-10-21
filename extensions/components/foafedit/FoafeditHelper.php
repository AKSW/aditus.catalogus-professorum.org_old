<?php


/**
 * Helper class for the FOAF Editor component.
 * Checks whether the current resource is an instance of foaf:Person
 * and registers the FOAF Editor component if so.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_foafedit
 * @author Norman Heino <norman.heino@gmail.com>
 * @version $Id: FoafeditHelper.php 4090 2009-08-19 22:10:54Z christian.wuerker $
 */
class FoafeditHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        $owApp = OntoWiki_Application::getInstance();
        
        if ($owApp->selectedModel) {
            $store    = $owApp->erfurt->getStore();
            $resource = (string) $owApp->selectedResource;

            $query = new Erfurt_Sparql_SimpleQuery();

            // build SPARQL query for getting class (rdf:type) of current resource
            $query->setProloguePart('SELECT DISTINCT ?t')
                  ->setWherePart('WHERE {<' . $resource . '> a ?t.}');

            // query the store
            if ($result = $owApp->selectedModel->sparqlQuery($query)) {
                $row = current($result);
                $class = $row['t'];

                // get all super classes of the class
                $super = $store->getTransitiveClosure(
                    (string) $owApp->selectedModel, 
                    EF_RDFS_SUBCLASSOF, 
                    $class, 
                    false);

                // merge direct type
                $types = array_merge(array($class), array_keys($super));

                if (in_array($this->_privateConfig->person, $types)) {
                    // we have a foaf:Person
                    // register new tab
                    OntoWiki_Navigation::register('foafedit', array(
                        'controller' => 'foafedit', 
                        'action'     => 'person', 
                        'name'       => 'FOAF Editor', 
                        'priority'   => -1, 
                        'active'     => false));
                }
            }
        }
    }
}

