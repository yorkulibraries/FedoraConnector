<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  fedora-connector
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class FedoraConnectorServer extends Omeka_Record_AbstractRecord
{


    /**
     * The name of the server [string].
     */
    public $name;

    /**
     * The server URL [string].
     */
    public $url;


    /**
     * Retrieve the server version.
     *
     * @return string The version.
     */
    public function getVersion()
    {

        // Query for version.
        $version = Zend_Registry::get('gateway')->query(
            "{$this->url}/describe?xml=true",
            "//*[local-name() = 'repositoryVersion']"
        );

        // Extract node value.
        return $version ? $version->item(0)->nodeValue : false;

    }


    /**
     * Retrieve the server service (get or objects) for url construction.
     *
     * @return string The version.
     */
    public function getService()
    {

        if (preg_match('/^2\./', $this->getVersion())) {
            $service = 'get';
        } else {
            $service = 'objects';
        }

        return $service;

    }


    /**
     * Test to see if server is online.
     *
     * @return boolean True if online.
     */
    public function isOnline()
    {
        return !$this->getVersion() ? false : true;
    }


    /**
     * Retrieve datastream nodes.
     *
     * @param $pid The pid to hit.
     * @return array The nodes.
     */
    public function getDatastreamNodes($pid)
    {

        // Construct url.
        $url = "{$this->url}/objects/$pid/datastreams?format=xml";

        // Query for nodes.
        $nodes = Zend_Registry::get('gateway')->query($url,
            "//*[local-name() = 'datastream']"
        );

        return $nodes;

    }


    /**
     * Retrieve the mimeType for a given pid and dsid.
     *
     * @param string $pid The pid.
     * @param string $dsid The dsid.
     * @return string The mimeType.
     */
    public function getMimeType($pid, $dsid)
    {

        // Query for mime type.
        $stream = Zend_Registry::get('gateway')->query(
            "{$this->url}/objects/$pid/datastreams?format=xml",
            "//*[local-name() = 'datastream'][@dsid='" . $dsid . "']"
        );

        return $stream->item(0)->getAttribute('mimeType');

    }


}
